<?php
declare(strict_types=1);

defined('APP_BOOTSTRAPPED') || exit;

const VENDOR_PAYOUT_FEE = 5000;

function payout_text(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function payout_has_wallet_payment(array $order): bool
{
    return (bool)dbone(
        "SELECT debit.id FROM wallet_transactions debit
         WHERE debit.user_id=? AND debit.reference_id=? AND debit.reference_type='orders'
           AND debit.type='order_payment' AND debit.direction='debit' AND debit.amount=?
           AND NOT EXISTS (SELECT 1 FROM wallet_transactions refund
               WHERE refund.user_id=debit.user_id AND refund.reference_id=debit.reference_id
                 AND refund.reference_type='orders' AND refund.type='order_refund')",
        [$order['user_id'], $order['id'], $order['total_amount']]
    );
}

function payout_record_completed_order(array $order): void
{
    $gross = (int)$order['total_amount'];
    $saleFee = intdiv($gross * 5 + 50, 100);
    execsql(
        "INSERT IGNORE INTO vendor_earnings
         (order_id,vendor_id,gross_amount,platform_fee,net_amount,status)
         VALUES (?,?,?,?,?,'held')",
        [$order['id'], $order['vendor_id'], $gross, $saleFee, $gross - $saleFee]
    );
}

function payout_available_orders(int $vendorId): array
{
    return dball(
        "SELECT e.order_id, e.net_amount - COALESCE((
            SELECT SUM(l.amount) FROM vendor_payout_lines l
            JOIN vendor_payouts p ON p.id=l.payout_id
            WHERE l.order_id=e.order_id AND p.status IN ('pending','processing','paid')
         ),0) AS available
         FROM vendor_earnings e JOIN orders o ON o.id=e.order_id
         JOIN vendor_order_settlements s ON s.order_id=o.id AND s.amount=o.total_amount
         WHERE e.vendor_id=? AND o.vendor_id=e.vendor_id AND e.status='eligible' AND o.order_status='completed'
         AND (
          o.payment_method='cod'
          OR (o.payment_method='wallet' AND EXISTS (
               SELECT 1 FROM wallet_transactions w
               WHERE w.user_id=o.user_id AND w.reference_id=o.id AND w.reference_type='orders'
                 AND w.type='order_payment' AND w.direction='debit' AND w.amount=o.total_amount
          ) AND NOT EXISTS (
               SELECT 1 FROM wallet_transactions r
               WHERE r.user_id=o.user_id AND r.reference_id=o.id AND r.reference_type='orders'
                 AND r.type='order_refund'
          ))
         )
         HAVING available > 0 ORDER BY e.eligible_at,e.order_id",
        [$vendorId]
    );
}

function payout_submit(array $vendor): void
{
    global $pdo;
    $vendorId = (int)$vendor['id'];
    $amount = positive_int($_POST['amount'] ?? '', 99999999999999);
    $key = payout_text('request_key');
    $bank = payout_text('bank_name');
    $account = payout_text('bank_account');
    $holder = payout_text('bank_holder');
    if ($amount <= VENDOR_PAYOUT_FEE || !preg_match('/\A[a-f0-9]{32}\z/', $key)) {
        throw new RuntimeException('Số tiền yêu cầu phải lớn hơn 5.000 đ.');
    }
    if ($bank === '' || strlen($bank) > 100 || strlen($holder) < 2 || strlen($holder) > 120
        || !preg_match('/\A[0-9]{4,30}\z/', $account)) {
        throw new RuntimeException('Hãy điền ngân hàng, số tài khoản và tên chủ tài khoản hợp lệ.');
    }

    $pdo->beginTransaction();
    try {
        $shop = dbone('SELECT status FROM vendors WHERE id=? FOR UPDATE', [$vendorId]);
        if (!$shop || $shop['status'] !== 'approved') {
            throw new RuntimeException('Gian hàng cần được duyệt.');
        }

        if (dbone('SELECT id FROM vendor_payouts WHERE vendor_id=? AND request_key=?', [$vendorId, $key])) {
            $pdo->commit();
            flash('Yêu cầu này đã được ghi nhận.');
            go('vendor_payouts');
        }

        $eligible = payout_available_orders($vendorId);
        $available = array_sum(array_map(static fn(array $row): int => (int)$row['available'], $eligible));
        if ($amount > $available) {
            throw new RuntimeException('Số tiền yêu cầu vượt doanh thu có thể rút.');
        }

        execsql(
            'INSERT INTO vendor_payouts
             (vendor_id,request_key,amount,fee,transfer_amount,bank_name,bank_account,bank_holder)
             VALUES(?,?,?,?,?,?,?,?)',
            [$vendorId, $key, $amount, VENDOR_PAYOUT_FEE, $amount - VENDOR_PAYOUT_FEE, $bank, $account, $holder]
        );
        $payoutId = (int)$pdo->lastInsertId();
        $remaining = $amount;
        foreach ($eligible as $row) {
            $part = min($remaining, (int)$row['available']);
            execsql('INSERT INTO vendor_payout_lines(payout_id,order_id,amount) VALUES(?,?,?)',
                [$payoutId, $row['order_id'], $part]);
            $remaining -= $part;
            if ($remaining === 0) {
                break;
            }
        }

        $pdo->commit();
        flash('Đã gửi yêu cầu rút tiền. Shop nhận ' . money($amount - VENDOR_PAYOUT_FEE) . ' sau phí.');
        go('vendor_payouts');
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function payout_admin(string $action, int $adminId): void
{
    global $pdo;
    $id = positive_int($_POST['id'] ?? 0, PHP_INT_MAX);
    $reference = payout_text('bank_reference');
    if (strlen($reference) > 120) {
        throw new RuntimeException('Mã tham chiếu tối đa 120 ký tự.');
    }

    $owner = $action === 'admin_payout_settle' ? null : dbone('SELECT vendor_id FROM vendor_payouts WHERE id=?', [$id]);
    $pdo->beginTransaction();
    try {
        if ($action === 'admin_payout_settle') {
            $order = dbone("SELECT * FROM orders WHERE id=? AND payment_method IN ('cod','wallet') AND order_status='completed' FOR UPDATE", [$id]);
            if (!$order || $reference === '') {
                throw new RuntimeException('Cần đơn đã hoàn tất và mã đối soát tiền thật.');
            }
            if (!preg_match('/\A[0-9]+(?:\.00)?\z/', (string)$order['total_amount'])) {
                throw new RuntimeException('Tổng đơn phải là số nguyên đồng để đối soát.');
            }
            if ($order['payment_method'] === 'wallet' && !payout_has_wallet_payment($order)) {
                throw new RuntimeException('Đơn chưa có giao dịch trừ ví hợp lệ.');
            }
            if (!dbone('SELECT order_id FROM vendor_order_settlements WHERE order_id=?', [$id])) {
                execsql('INSERT INTO vendor_order_settlements (order_id,amount,bank_reference,admin_id) VALUES (?,?,?,?)',
                    [$id, $order['total_amount'], $reference, $adminId]);
                payout_record_completed_order($order);
                execsql("UPDATE vendor_earnings SET status='eligible',eligible_at=NOW()
                         WHERE order_id=? AND status IN ('held','eligible')", [$id]);
            }
        } else {
            if (!$owner) {
                throw new RuntimeException('Không tìm thấy yêu cầu rút tiền.');
            }
            dbone('SELECT id FROM vendors WHERE id=? FOR UPDATE', [$owner['vendor_id']]);
            $payout = dbone('SELECT * FROM vendor_payouts WHERE id=? FOR UPDATE', [$id]);
            $assigned = $payout['status'] === 'processing' && (int)$payout['handled_by'] === $adminId;

            if ($action === 'admin_payout_claim' && $payout['status'] === 'pending') {
                execsql("UPDATE vendor_payouts SET status='processing',handled_by=? WHERE id=?", [$adminId, $id]);
            } elseif ($action === 'admin_payout_paid' && $assigned && $reference !== '') {
                execsql("INSERT INTO withdrawal_bank_receipts(bank_reference,source_kind,source_id) VALUES(?,'vendor',?)",
                    [$reference, $id]);
                execsql("UPDATE vendor_payouts SET status='paid',bank_reference=?,resolved_at=NOW() WHERE id=?", [$reference, $id]);
                execsql(
                    "UPDATE vendor_earnings e JOIN vendor_payout_lines current_line ON current_line.order_id=e.order_id
                     SET e.status='paid',e.paid_at=NOW(),e.payout_reference=?
                     WHERE current_line.payout_id=? AND e.status='eligible'
                       AND e.net_amount=(
                         SELECT SUM(l.amount) FROM vendor_payout_lines l JOIN vendor_payouts p ON p.id=l.payout_id
                         WHERE l.order_id=e.order_id AND p.status='paid'
                       )",
                    [$reference, $id]
                );
            } elseif ($action === 'admin_payout_reject' && ($assigned || $payout['status'] === 'pending')) {
                $reason = payout_text('reason');
                if ($reason === '' || strlen($reason) > 255) {
                    throw new RuntimeException('Cần nhập lý do từ chối.');
                }
                execsql("UPDATE vendor_payouts SET status='rejected',handled_by=?,rejection_reason=?,resolved_at=NOW() WHERE id=?",
                    [$adminId, $reason, $id]);
            } else {
                throw new RuntimeException('Trạng thái yêu cầu đã đổi hoặc cần bổ sung mã chuyển khoản.');
            }
        }

        $pdo->commit();
        flash('Đã lưu kết quả đối soát.');
        go('admin_payouts');
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

<?php
declare(strict_types=1);

defined('APP_BOOTSTRAPPED') || exit;

/** Theo dõi phần số dư có chứng từ SePay trong sổ ví, theo thứ tự ghi nhận. */
function wallet_withdrawable(int $userId, int $balance): int
{
    $rows = dball(
        "SELECT w.*, t.id AS sepay_topup
         FROM wallet_transactions w LEFT JOIN wallet_topups t
           ON w.type='topup' AND w.reference_type='wallet_topups'
           AND w.reference_id=t.id AND w.user_id=t.user_id AND w.amount=t.amount
           AND t.status='paid' AND t.bank_reference REGEXP '^SEPAY:[1-9][0-9]*$'
         WHERE w.user_id=? ORDER BY w.id",
        [$userId]
    );

    $cash = 0;
    $cashCharges = [];
    foreach ($rows as $row) {
        $amount = max(0, (int)$row['amount']);
        $key = $row['reference_type'] . ':' . $row['reference_id'];
        if ($row['direction'] === 'debit') {
            $used = min($cash, $amount);
            $cash -= $used;
            if (in_array($row['type'], ['order_payment','wallet_withdrawal'], true)) {
                $cashCharges[$key] = $used;
            }
        } elseif ($row['sepay_topup'] !== null) {
            $cash += $amount;
        } elseif (in_array($row['type'], ['order_refund','wallet_withdrawal_return'], true)) {
            $restored = min($amount, $cashCharges[$key] ?? 0);
            $cash += $restored;
            unset($cashCharges[$key]);
        }
        $cash = min($cash, max(0, (int)$row['balance_after']));
    }

    return min($cash, max(0, $balance));
}

function wallet_withdraw_request(int $userId): void
{
    global $pdo;
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
        $wallet = dbone('SELECT balance FROM wallet_accounts WHERE user_id=? FOR UPDATE', [$userId]);
        if (dbone('SELECT id FROM wallet_withdrawals WHERE user_id=? AND request_key=?', [$userId, $key])) {
            $pdo->commit();
            flash('Yêu cầu này đã được ghi nhận.');
            go('wallet_withdrawals');
        }

        $balance = (int)($wallet['balance'] ?? 0);
        if ($amount > wallet_withdrawable($userId, $balance)) {
            throw new RuntimeException('Số tiền yêu cầu vượt phần số dư có nguồn nạp SePay đã xác minh.');
        }
        execsql(
            'INSERT INTO wallet_withdrawals(user_id,request_key,amount,fee,transfer_amount,bank_name,bank_account,bank_holder)
             VALUES(?,?,?,?,?,?,?,?)',
            [$userId, $key, $amount, VENDOR_PAYOUT_FEE, $amount - VENDOR_PAYOUT_FEE, $bank, $account, $holder]
        );
        $id = (int)$pdo->lastInsertId();
        execsql('UPDATE wallet_accounts SET balance=balance-? WHERE user_id=?', [$amount, $userId]);
        execsql(
            "INSERT INTO wallet_transactions(user_id,direction,amount,balance_after,type,reference_type,reference_id,description)
             VALUES(?,'debit',?,?,'wallet_withdrawal','wallet_withdrawals',?,?)",
            [$userId, $amount, $balance - $amount, $id, 'Giữ tiền cho yêu cầu rút ví #' . $id]
        );

        $pdo->commit();
        flash('Đã gửi yêu cầu rút ví. Thực nhận ' . money($amount - VENDOR_PAYOUT_FEE) . ' sau phí.');
        go('wallet_withdrawals');
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $error;
    }
}

function wallet_withdraw_admin(string $action, int $adminId): void
{
    global $pdo;
    $id = positive_int($_POST['id'] ?? '', PHP_INT_MAX);
    $owner = dbone('SELECT user_id FROM wallet_withdrawals WHERE id=?', [$id]);
    if (!$owner) {
        throw new RuntimeException('Không tìm thấy yêu cầu rút ví.');
    }

    $reference = payout_text('bank_reference');
    $reason = payout_text('reason');
    $pdo->beginTransaction();
    try {
        $userId = (int)$owner['user_id'];
        $wallet = dbone('SELECT balance FROM wallet_accounts WHERE user_id=? FOR UPDATE', [$userId]);
        $row = dbone('SELECT * FROM wallet_withdrawals WHERE id=? FOR UPDATE', [$id]);
        $hold = dbone(
            "SELECT id FROM wallet_transactions WHERE user_id=? AND type='wallet_withdrawal'
             AND reference_type='wallet_withdrawals' AND reference_id=? AND direction='debit' AND amount=?",
            [$userId, $id, $row['amount']]
        );
        if (!$hold) {
            throw new RuntimeException('Chưa tìm thấy khoản tiền giữ cho yêu cầu này.');
        }
        $assigned = $row['status'] === 'processing' && (int)$row['handled_by'] === $adminId;

        if ($action === 'admin_wallet_withdraw_claim' && $row['status'] === 'pending') {
            execsql("UPDATE wallet_withdrawals SET status='processing',handled_by=? WHERE id=?", [$adminId, $id]);
        } elseif ($action === 'admin_wallet_withdraw_paid' && $assigned && $reference !== '' && strlen($reference) <= 120) {
            execsql("INSERT INTO withdrawal_bank_receipts(bank_reference,source_kind,source_id) VALUES(?,'wallet',?)",
                [$reference, $id]);
            execsql("UPDATE wallet_withdrawals SET status='paid',bank_reference=?,resolved_at=NOW() WHERE id=?", [$reference, $id]);
        } elseif ($action === 'admin_wallet_withdraw_reject' && ($assigned || $row['status'] === 'pending')
            && $reason !== '' && strlen($reason) <= 255) {
            if (!$wallet) {
                throw new RuntimeException('Không tìm thấy ví để hoàn lại tiền.');
            }
            $amount = (int)$row['amount'];
            execsql('UPDATE wallet_accounts SET balance=balance+? WHERE user_id=?', [$amount, $userId]);
            execsql(
                "INSERT INTO wallet_transactions(user_id,direction,amount,balance_after,type,reference_type,reference_id,description)
                 VALUES (?,'credit',?,?,'wallet_withdrawal_return','wallet_withdrawals',?,?)",
                [$userId, $amount, (int)$wallet['balance'] + $amount, $id, 'Hoàn lại yêu cầu rút ví #' . $id]
            );
            execsql("UPDATE wallet_withdrawals SET status='rejected',handled_by=?,rejection_reason=?,resolved_at=NOW() WHERE id=?",
                [$adminId, $reason, $id]);
        } else {
            throw new RuntimeException('Trạng thái yêu cầu đã đổi hoặc còn thiếu mã chuyển khoản hay lý do từ chối.');
        }

        $pdo->commit();
        flash('Đã cập nhật yêu cầu rút ví.');
        go('admin_wallet_withdrawals');
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        throw $error;
    }
}

<?php
declare(strict_types=1);

defined('APP_BOOTSTRAPPED') || exit;

function order_can_request_cancellation(array $order): bool
{
    return in_array($order['order_status'], ['printing', 'shipping'], true)
        && in_array($order['payment_method'], ['cod', 'wallet'], true);
}

function order_cancellation_reason(string $field): string
{
    $value = $_POST[$field] ?? '';
    $value = is_string($value) ? trim($value) : '';
    if (!preg_match('/\A.{1,255}\z/us', $value)) {
        throw new RuntimeException('Hãy điền lý do từ 1 đến 255 ký tự.');
    }
    return $value;
}

/** Khóa dòng orders đã được giữ trước khi gọi hàm này. */
function cancel_locked_order(array $order): void
{
    $orderId = (int)$order['id'];
    $userId = (int)$order['user_id'];
    $payment = dbone('SELECT status FROM order_payments WHERE order_id=?', [$orderId]);
    if ($payment && $payment['status'] === 'confirmed' && $order['payment_method'] !== 'wallet') {
        throw new RuntimeException('Đơn đã thanh toán. Liên hệ Admin để xử lý.');
    }

    execsql("UPDATE orders SET order_status='cancelled' WHERE id=?", [$orderId]);
    $items = order_items($orderId);
    usort($items, static fn (array $a, array $b): int => (int)$a['product_id'] <=> (int)$b['product_id']);
    foreach ($items as $item) {
        if ($item['product_id']) {
            execsql('UPDATE products SET stock=stock+? WHERE id=?', [$item['quantity'], $item['product_id']]);
        }
    }

    if ($order['payment_method'] === 'wallet') {
        $charge = dbone(
            "SELECT amount FROM wallet_transactions WHERE user_id=? AND direction='debit'
             AND type='order_payment' AND reference_type='orders' AND reference_id=?",
            [$userId, $orderId]
        );
        if (!$charge || checkout_dong($charge['amount']) !== checkout_dong($order['total_amount'])) {
            throw new RuntimeException('Không tìm thấy giao dịch ví của đơn này để hoàn tiền.');
        }
        $refund = checkout_dong($charge['amount']);
        if (dbone(
            "SELECT id FROM wallet_transactions WHERE user_id=? AND direction='credit'
             AND type='order_refund' AND reference_type='orders' AND reference_id=?",
            [$userId, $orderId]
        )) {
            throw new RuntimeException('Đơn hàng đã có giao dịch hoàn tiền.');
        }
        execsql(
            'INSERT INTO wallet_accounts(user_id,balance) VALUES(?,?) ON DUPLICATE KEY UPDATE balance=balance+?',
            [$userId, $refund, $refund]
        );
        $account = dbone('SELECT balance FROM wallet_accounts WHERE user_id=?', [$userId]);
        execsql(
            "INSERT INTO wallet_transactions
             (user_id,direction,amount,balance_after,type,reference_type,reference_id,description)
             VALUES (?,'credit',?,?,'order_refund','orders',?,?)",
            [$userId, $refund, $account['balance'], $orderId, 'Hoàn tiền đơn ' . $order['order_code']]
        );
    }
}

function order_cancellation_action(string $action): void
{
    global $pdo;
    $requesting = $action === 'order_cancel_request';
    $actor = $requesting ? need_role('client', 'vendor') : need_role('admin', 'vendor');
    $shopId = !$requesting && $actor['role'] === 'vendor' ? (int)approved_vendor()['id'] : null;
    $back = $requesting ? 'my_orders' : ($shopId === null ? 'admin_orders' : 'vendor_orders');
    $orderId = positive_int($_POST['order_id'] ?? 0, 2147483647);

    $pdo->beginTransaction();
    try {
        $order = dbone('SELECT * FROM orders WHERE id=? FOR UPDATE', [$orderId]);
        if (!$order
            || ($requesting && (int)$order['user_id'] !== (int)$actor['id'])
            || ($shopId !== null && ((int)$order['vendor_id'] !== $shopId || (int)$order['user_id'] === (int)$actor['id']))) {
            throw new RuntimeException('Bạn không có quyền thao tác với đơn hàng này.');
        }

        if ($requesting) {
            $token = $_POST['request_token'] ?? '';
            if (!is_string($token) || !preg_match('/\A[a-f0-9]{32}\z/', $token)) {
                throw new RuntimeException('Hãy tải lại trang đơn hàng rồi gửi yêu cầu.');
            }
            $existing = dbone('SELECT id FROM order_cancel_requests WHERE order_id=? AND request_token=?',
                [$orderId, $token]);
            if ($existing) {
                $message = 'Yêu cầu hủy của bạn đã được ghi nhận.';
            } else {
                if (!order_can_request_cancellation($order)) {
                    throw new RuntimeException('Chỉ có thể gửi yêu cầu hủy cho đơn COD hoặc đơn ví đang in, đang giao.');
                }
                if (dbone("SELECT id FROM order_cancel_requests WHERE order_id=? AND status='pending'", [$orderId])) {
                    throw new RuntimeException('Đơn này đã có yêu cầu hủy chờ duyệt.');
                }
                execsql('INSERT INTO order_cancel_requests(order_id,request_token,reason) VALUES(?,?,?)',
                    [$orderId, $token, order_cancellation_reason('cancel_reason')]);
                $message = 'Đã gửi yêu cầu hủy đơn.';
            }
        } else {
            $requestId = positive_int($_POST['request_id'] ?? 0, PHP_INT_MAX);
            $request = dbone('SELECT id,status FROM order_cancel_requests WHERE id=? AND order_id=? FOR UPDATE',
                [$requestId, $orderId]);
            if (!$request || $request['status'] !== 'pending') {
                throw new RuntimeException('Yêu cầu hủy đã được xử lý hoặc không tồn tại.');
            }

            $approve = $action === 'order_cancel_approve';
            if ($approve) {
                if (!order_can_request_cancellation($order)) {
                    throw new RuntimeException('Đơn đã đổi trạng thái. Hãy kiểm tra lại trước khi xử lý yêu cầu hủy.');
                }
                if ($order['order_status'] === 'shipping' && ($_POST['shipment_stopped'] ?? '') !== '1') {
                    throw new RuntimeException('Hãy xác nhận kiện hàng đã dừng giao hoặc được nhận lại trước khi duyệt.');
                }
                if (dbone('SELECT order_id FROM vendor_order_settlements WHERE order_id=?', [$orderId])
                    || ($order['payment_method'] === 'cod'
                        && dbone('SELECT order_id FROM cod_collections WHERE order_id=?', [$orderId]))) {
                    throw new RuntimeException('Đơn đã đối soát hoặc thu COD. Cần kiểm tra giao dịch trước khi hủy.');
                }
                cancel_locked_order($order);
                $note = $order['order_status'] === 'shipping' ? 'Đã xác nhận tình trạng kiện hàng và tiền thu hộ.' : null;
                $message = $order['payment_method'] === 'wallet'
                    ? 'Đã hủy đơn và hoàn tiền về ví người mua.'
                    : 'Đã hủy đơn COD và hoàn lại tồn kho.';
            } else {
                $note = order_cancellation_reason('reject_reason');
                $message = 'Đã từ chối yêu cầu hủy.';
            }
            execsql(
                'UPDATE order_cancel_requests SET status=?,reviewed_by=?,review_note=?,reviewed_at=NOW() WHERE id=?',
                [$approve ? 'approved' : 'rejected', $actor['id'], $note, $requestId]
            );
        }

        $pdo->commit();
        flash($message);
        go($back);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

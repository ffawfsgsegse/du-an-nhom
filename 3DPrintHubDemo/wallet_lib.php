<?php
/** Sổ số dư dành cho DEMO; KHÔNG phải dịch vụ ví điện tử được cấp phép.
 * Các cập nhật tài chính cần chạy trong một PDO transaction từ caller.
 */
function wallet_topup_enabled(): bool {
    return payos_enabled() && getenv('THREEDPRINT_STORED_VALUE_ENABLED') === '1';
}
function wallet_demo_enabled(): bool {
    $host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));
    return in_array($host, ['localhost','127.0.0.1','[::1]'], true)
        && in_array((string)($_SERVER['REMOTE_ADDR'] ?? ''), ['127.0.0.1','::1'], true);
}
function wallet_account(int $userId): array {
    if ($userId < 1) throw new RuntimeException('Tài khoản không hợp lệ');
    execute('INSERT IGNORE INTO wallet_accounts(user_id,balance) VALUES (?,0)', [$userId]);
    $row=dbone('SELECT * FROM wallet_accounts WHERE user_id=?', [$userId]);
    if (!$row) throw new RuntimeException('Không thể tạo tài khoản số dư');
    return $row;
}
function wallet_lock(int $userId): array {
    global $pdo;
    if (!$pdo->inTransaction()) throw new LogicException('Thiếu giao dịch dữ liệu');
    execute('INSERT IGNORE INTO wallet_accounts(user_id,balance) VALUES (?,0)', [$userId]);
    $row=dbone('SELECT * FROM wallet_accounts WHERE user_id=? FOR UPDATE', [$userId]);
    if (!$row) throw new RuntimeException('Không thể khóa số dư');
    return $row;
}
function wallet_post_locked(int $userId, int $amount, string $direction, string $type, string $refType, int $refId, string $description): int {
    if ($amount < 1 || $amount > 1000000000 || !in_array($direction,['credit','debit'],true)) throw new RuntimeException('Số tiền không hợp lệ');
    if ($refId < 1) throw new RuntimeException('Tham chiếu giao dịch không hợp lệ');
    $wallet=wallet_lock($userId);
    $current=(int)$wallet['balance'];
    $next=$direction==='credit' ? $current+$amount : $current-$amount;
    if ($next<0) throw new RuntimeException('Số dư không đủ. Vui lòng chọn COD hoặc nạp thêm tiền.');
    // Unique reference ensures one credit/debit per business event.
    execute('INSERT INTO wallet_transactions(user_id,direction,amount,balance_after,type,reference_type,reference_id,description) VALUES(?,?,?,?,?,?,?,?)',[
        $userId,$direction,$amount,$next,$type,$refType,$refId,$description
    ]);
    execute('UPDATE wallet_accounts SET balance=? WHERE user_id=?',[$next,$userId]);
    return $next;
}
/** Trong transaction đang mở; caller đã xác minh HMAC webhook payOS. */
function wallet_process_verified_topup(array $data): bool {
    global $pdo;
    if (!$pdo->inTransaction()) throw new LogicException('Thiếu transaction');
    $code=(int)($data['orderCode']??0);
    $tx=dbone('SELECT * FROM wallet_topups WHERE payos_order_code=? FOR UPDATE',[$code]);
    if (!$tx) return false;
    if ($tx['status']==='paid') return true;  // webhook lặp
    if ($tx['status']!=='pending') throw new RuntimeException('Nạp tiền đã hết hiệu lực');
    $amount=(int)($data['amount']??0);
    $link=(string)($data['paymentLinkId']??'');
    $reference=trim((string)($data['reference']??''));
    if ($amount<1 || $amount!==(int)$tx['amount'] || $link==='' || $link!==(string)$tx['payment_link_id'] || $reference==='') {
        throw new RuntimeException('Thông tin thanh toán nạp tiền không khớp');
    }
    execute('UPDATE wallet_topups SET status="paid",bank_reference=?,paid_at=NOW() WHERE id=? AND status="pending"',[$reference,$tx['id']]);
    wallet_post_locked((int)$tx['user_id'],$amount,'credit','topup','wallet_topups',(int)$tx['id'],'Nạp số dư qua payOS #'.$tx['id']);
    return true;
}
function wallet_vendor_earnings(int $orderId,int $vendorId,int $gross): void {
    // Phí sàn demo = 0%, chưa thực hiện chuyển tiền tự động cho người bán.
    execute('INSERT INTO vendor_earnings(order_id,vendor_id,gross_amount,platform_fee,net_amount,status) VALUES(?,?,?,0,?,"held")',[
        $orderId,$vendorId,$gross,$gross
    ]);
}

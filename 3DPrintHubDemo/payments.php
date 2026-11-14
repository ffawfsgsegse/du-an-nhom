<?php
/** Helpers for the bank transfer (manual reconciliation) checkout. */
function bank_bin($bankName) {
    $name = strtolower(trim((string)$bankName));
    if (preg_match('/^97[0-9]{4}$/', $name)) return $name;
    $name = preg_replace('/[^a-z0-9]/', '', $name);
    $bins = [
        'vietcombank'=>'970436', 'vcb'=>'970436',
        'techcombank'=>'970407', 'tcb'=>'970407',
        'mbbank'=>'970422','mb'=>'970422','mbb'=>'970422',
        'vietinbank'=>'970415','icb'=>'970415',
        'bidv'=>'970418', 'agribank'=>'970405','vba'=>'970405',
        'acb'=>'970416','vpbank'=>'970432',
        'tpbank'=>'970423','sacombank'=>'970403',
        'hdbank'=>'970437','shb'=>'970443',
        'ocb'=>'970448','vib'=>'970441','msb'=>'970426',
        'eximbank'=>'970431','seabank'=>'970440',
    ];
    return $bins[$name] ?? null;
}
function vietqr_image($bankName, $bankAccount, $amount, $reference, $accountHolder='') {
    $bin = bank_bin($bankName);
    $account = preg_replace('/\s+/', '', (string)$bankAccount);
    if (!$bin || !preg_match('/^[0-9]{4,25}$/', $account)) return null;
    // VietQR transfer notes are ASCII letters and digits for banking compatibility.
    $note = preg_replace('/[^A-Za-z0-9]/', '', (string)$reference);
    $args = ['amount' => (int)round((float)$amount), 'addInfo' => $note];
    if (trim((string)$accountHolder)!=='') $args['accountName'] = trim((string)$accountHolder);
    return 'https://img.vietqr.io/image/'.$bin.'-'.$account.'-compact2.png?'.http_build_query($args, '', '&', PHP_QUERY_RFC3986);
}
function payment_label($status) {
    return [
        'unpaid'=>'Chưa chuyển khoản',
        'submitted'=>'Chờ xác nhận',
        'confirmed'=>'Đã xác nhận tiền',
        'rejected'=>'Cần kiểm tra lại',
    ][$status] ?? 'Chưa ghi nhận';
}
function payment_badge($status) {
    return in_array($status, ['confirmed','submitted','rejected','unpaid'], true) ? $status : 'unpaid';
}

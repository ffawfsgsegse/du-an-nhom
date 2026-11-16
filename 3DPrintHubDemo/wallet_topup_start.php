<?php
/** Chỉ mở khi môi trường đã được phép vận hành tiền nạp trước và đăng ký payOS. */
require __DIR__.'/config.php';
require __DIR__.'/payos_lib.php';
require __DIR__.'/wallet_lib.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method Not Allowed');}
require_login();verify();
if (!wallet_topup_enabled()) {http_response_code(403);exit('Chức năng nạp tiền thật chưa được bật. Bạn cần cấu hình payOS và tuân thủ pháp lý trước.');}
$raw=(string)($_POST['amount']??'');
if(!ctype_digit($raw)){http_response_code(400);exit('Số tiền không hợp lệ');}
$amount=(int)$raw;
if($amount<10000 || $amount>5000000){http_response_code(400);exit('Nạp từ 10.000đ đến 5.000.000đ/lần');}
$cfg=payos_secrets();
try {
  $pdo->beginTransaction();
  // Unique numeric payOS order code distinct from existing commerce order code range.
  execute('INSERT INTO wallet_topups(user_id,amount,payos_order_code) VALUES (?, ?, ?)',[user()['id'],$amount,0]);
  $id=(int)$pdo->lastInsertId();
  $code=3000000000+$id;
  execute('UPDATE wallet_topups SET payos_order_code=? WHERE id=?',[$code,$id]);
  $pdo->commit();
  $base=rtrim((string)$cfg['site_url'],'/');
  $res=payos_create_payment($cfg,$code,$amount,'NAP'.strtoupper(base_convert((string)$id,10,36)),$base.'/wallet_topup_return.php?id='.$id,$base.'/wallet_topup_return.php?id='.$id.'&cancelled=1');
  $linkId=(string)($res['paymentLinkId']??'');
  $checkout=(string)($res['checkoutUrl']??'');
  if($linkId===''||$checkout==='')throw new RuntimeException('payOS chưa trả về thông tin hợp lệ');
  execute('UPDATE wallet_topups SET payment_link_id=?, checkout_url=? WHERE id=? AND status="pending"',[$linkId,$checkout,$id]);
  header('Location: '.$checkout,true,303);exit;
} catch(Throwable $e){
 if($pdo->inTransaction())$pdo->rollBack();
 if(isset($id))execute('UPDATE wallet_topups SET status="failed" WHERE id=? AND status="pending"',[$id]);
 error_log('3DPrintHub wallet topup: '.$e->getMessage());
 http_response_code(502);
 echo '<!doctype html><meta charset="utf-8"><h2>Không thể tạo yêu cầu nạp tiền</h2><p>Hãy kiểm tra cấu hình payOS hoặc thử lại sau.</p><a href="index.php?page=wallet">Quay lại trang số dư</a>';
}

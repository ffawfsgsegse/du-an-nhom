<?php
require __DIR__.'/config.php';
require __DIR__.'/payos_lib.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed');}
require_login();verify();
$cfg=payos_secrets();if(!$cfg){http_response_code(503);exit('Chưa cấu hình payOS. Xem HUONG_DAN_PAYOS.md');}
$orderId=filter_input(INPUT_POST,'order_id',FILTER_VALIDATE_INT);
if(!$orderId){http_response_code(400);exit('Thiếu mã đơn hàng');}
try {
 $pdo->beginTransaction();
 $order=dbone('SELECT o.* FROM orders o JOIN order_payments op ON op.order_id=o.id WHERE o.id=? AND o.user_id=? AND o.payment_method="banking" AND op.payment_provider="payos" FOR UPDATE',[$orderId,user()['id']]);
 if(!$order || $order['order_status']==='cancelled')throw new RuntimeException('Đơn hàng không hợp lệ hoặc đã hủy');
 $paid=dbone('SELECT status FROM order_payments WHERE order_id=?',[$orderId]);
 if($paid && $paid['status']==='confirmed'){
  $pdo->commit();go('payment',['id'=>$orderId]);
 }
 $existing=dbone('SELECT * FROM payos_transactions WHERE order_id=? FOR UPDATE',[$orderId]);
 if($existing){
    $pdo->commit();
    if($existing['status']==='paid'){go('payment',['id'=>$orderId]);}
    if($existing['checkout_url'] && filter_var($existing['checkout_url'],FILTER_VALIDATE_URL)){
       header('Location: '.$existing['checkout_url'],true,303);exit;
    }
    throw new RuntimeException('Đơn đang chờ tạo liên kết. Vui lòng liên hệ quản trị nếu tiếp tục gặp lỗi.');
 }
 $amount=(int)round((float)$order['total_amount']);
 if($amount<2000 || $amount>1000000000 || abs($amount-(float)$order['total_amount'])>0.001) throw new RuntimeException('Số tiền không hợp lệ cho thanh toán trực tuyến');
 $payosCode=1000000000+$orderId; // Unique numeric code for THIS merchant payOS channel.
 $description='3DP'.strtoupper(base_convert((string)$orderId,10,36));
 if(strlen($description)>9)throw new RuntimeException('Mã mô tả vượt giới hạn');
 $site=rtrim((string)$cfg['site_url'],'/');
 $returnUrl=$site.'/payos_return.php?order_id='.$orderId;
 $cancelUrl=$returnUrl.'&cancelled=1';
 $result=payos_create_payment($cfg,$payosCode,$amount,$description,$returnUrl,$cancelUrl);
 execute('INSERT INTO payos_transactions(order_id,payos_order_code,payment_link_id,checkout_url,amount,status) VALUES(?,?,?,?,?,"created")',[
   $orderId,$payosCode,(string)($result['paymentLinkId']??''),(string)$result['checkoutUrl'],$amount
 ]);
 $pdo->commit();
 header('Location: '.$result['checkoutUrl'],true,303);exit;
} catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('payOS start: '.$e->getMessage());http_response_code(500);echo '<!doctype html><meta charset="utf-8"><h2>Chưa thể bắt đầu thanh toán</h2><p>'.htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8').'</p><p><a href="index.php?page=orders">Quay lại đơn hàng</a></p>';}

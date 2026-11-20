<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED')||exit;
function checkout_action(string $action): void {
 global $pdo;
 $u=need_role('client','vendor');
 if($action==='place_order'){
  $name=trim((string)($_POST['receiver_name']??''));$phone=trim((string)($_POST['receiver_phone']??''));$address=trim((string)($_POST['address']??''));$option=(string)($_POST['payment_option']??'cod');$method=$option==='cod'?'cod':'banking';
  if(strlen($name)<2||strlen($name)>100||!preg_match('/^[+0-9 .-]{8,20}$/',$phone)||strlen($address)<10||strlen($address)>255)throw new RuntimeException('Hãy nhập đúng tên, số điện thoại và địa chỉ nhận hàng.');
  if(!in_array($option,['cod','demo_bank','demo_momo'],true))throw new RuntimeException('Phương thức thanh toán không hợp lệ.');
  $cart=$_SESSION['cart']??[];if(!$cart)throw new RuntimeException('Giỏ hàng đang trống.');
  ksort($cart,SORT_NUMERIC);$pdo->beginTransaction();
  try{
   $groups=[];
   foreach($cart as $pid=>$qty){
    $qty=positive_int($qty,100);
    $p=dbone('SELECT p.id,p.vendor_id,p.name,p.price,p.stock,p.status,p.deleted_at,v.status vendor_status,u.status user_status FROM products p JOIN vendors v ON v.id=p.vendor_id JOIN users u ON u.id=v.user_id WHERE p.id=? FOR UPDATE',[(int)$pid]);
    if(!$p||$p['status']!=='active'||$p['deleted_at']!==null||$p['vendor_status']!=='approved'||!(int)$p['user_status']||$qty>(int)$p['stock'])throw new RuntimeException('Một sản phẩm đã hết hàng. Hãy kiểm tra lại giỏ.');
    $groups[$p['vendor_id']][]=['p'=>$p,'qty'=>$qty];
   }
   $orders=[];
   foreach($groups as $vendor=>$items){
    $total=0;foreach($items as $row)$total+=(float)$row['p']['price']*$row['qty'];
    $code='3D'.date('ymd').strtoupper(bin2hex(random_bytes(5)));
    execsql('INSERT INTO orders(order_code,user_id,vendor_id,receiver_name,receiver_phone,shipping_address,total_amount,payment_method,order_status) VALUES(?,?,?,?,?,?,?, ?, "pending")',[$code,$u['id'],$vendor,$name,$phone,$address,$total,$method]);
    $oid=(int)$pdo->lastInsertId();$orders[]=$oid;
    foreach($items as $row){
     execsql('INSERT INTO order_items(order_id,product_id,quantity,unit_price) VALUES(?,?,?,?)',[$oid,$row['p']['id'],$row['qty'],$row['p']['price']]);
     execsql('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?',[$row['qty'],$row['p']['id'],$row['qty']]);
    }
    if($method==='banking'){
     execsql('INSERT INTO order_payments(order_id,status,payment_provider,bank_name,bank_account,bank_account_holder) VALUES(?,"unpaid",?,"DEMO","DEMO","KHÔNG CHUYỂN TIỀN")',[$oid,$option]);
     execsql('INSERT INTO demo_payments(order_id,provider,status,expires_at) VALUES(?, ?, "pending",DATE_ADD(NOW(),INTERVAL 15 MINUTE))',[$oid,$option==='demo_momo'?'momo':'bank_qr']);
    }
   }
   $pdo->commit();unset($_SESSION['cart']);
   flash(count($orders).' đơn hàng đã được tạo thành công.'.($method==='banking'?' Vào lịch sử đơn để mở trang MoMo/QR DEMO.':''));go('my_orders');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
 }
 if($action==='demo_confirm'){
  if(!in_array(strtolower(explode(':',$_SERVER['HTTP_HOST']??'')[0]),['localhost','127.0.0.1'],true))throw new RuntimeException('QR DEMO chỉ dùng khi chạy localhost.');
  $oid=positive_int($_POST['order_id']??0);$pdo->beginTransaction();
  try{
   $o=dbone('SELECT * FROM orders WHERE id=? AND user_id=? AND payment_method="banking" FOR UPDATE',[$oid,$u['id']]);
   $d=dbone('SELECT * FROM demo_payments WHERE order_id=? FOR UPDATE',[$oid]);
   if(!$o||!$d||$o['order_status']==='cancelled')throw new RuntimeException('Không thể xác nhận đơn này.');
   if($d['status']==='pending'){
    if(strtotime($d['expires_at'])<time())throw new RuntimeException('Mã DEMO hết hạn. Không có giao dịch thực tế.');
    execsql('UPDATE demo_payments SET status="paid",confirmed_at=NOW() WHERE id=? AND status="pending"',[$d['id']]);
    execsql('UPDATE order_payments SET status="confirmed" WHERE order_id=? AND payment_provider IN ("demo_bank","demo_momo")',[$oid]);
   }
   $pdo->commit();flash('Đã ghi nhận thanh toán DEMO (không có chuyển tiền thật).');go('my_orders');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
 }
 if($action==='cancel_order'){
  $oid=positive_int($_POST['order_id']??0);$pdo->beginTransaction();
  try{
   $o=dbone('SELECT * FROM orders WHERE id=? AND user_id=? FOR UPDATE',[$oid,$u['id']]);
   if(!$o||$o['order_status']!=='pending')throw new RuntimeException('Chỉ hủy được đơn đang chờ xác nhận.');
   $pay=dbone('SELECT status FROM order_payments WHERE order_id=?',[$oid]);
   if($pay && $pay['status']==='confirmed')throw new RuntimeException('Đơn đã thanh toán DEMO. Liên hệ Admin để xử lý.');
   execsql('UPDATE orders SET order_status="cancelled" WHERE id=?',[$oid]);
   foreach(order_items($oid) as $row)if($row['product_id'])execsql('UPDATE products SET stock=stock+? WHERE id=?',[$row['quantity'],$row['product_id']]);
   $pdo->commit();flash('Đã hủy đơn và hoàn lại số lượng tồn kho.');go('my_orders');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
 }
}

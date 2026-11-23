<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED')||exit;
function vendor_action(string $action): void {
 global $pdo;
 $vendor=approved_vendor();
 if($action==='vendor_payout_request'){payout_submit($vendor);return;}
 if($action==='vendor_material_add'){
  $name=trim((string)($_POST['name']??''));$price=safe_price($_POST['price_per_cm3']??0);
  if(strlen($name)<2||strlen($name)>100)throw new RuntimeException('Tên vật liệu không hợp lệ.');
  execsql('INSERT INTO materials(vendor_id,name,price_per_cm3,status) VALUES(?,?,?,1)',[$vendor['id'],$name,$price]);flash('Đã thêm vật liệu.');go('vendor_products');
 }
 if($action==='vendor_order_status'){
  $oid=positive_int($_POST['order_id']??0);$next=(string)($_POST['next_status']??'');
  $map=['pending'=>'printing','printing'=>'shipping','shipping'=>'completed'];
  $pdo->beginTransaction();try{
   $o=dbone('SELECT * FROM orders WHERE id=? AND vendor_id=? FOR UPDATE',[$oid,$vendor['id']]);
   if(!$o||!isset($map[$o['order_status']])||$map[$o['order_status']]!==$next)throw new RuntimeException('Không thể chuyển trạng thái đơn theo yêu cầu.');
   if($o['payment_method']==='banking'){
    $pay=dbone('SELECT status FROM order_payments WHERE order_id=?',[$oid]);if(!$pay||$pay['status']!=='confirmed')throw new RuntimeException('Đơn QR DEMO chưa được xác nhận.');
   }
   execsql('UPDATE orders SET order_status=?,completed_at=IF(?="completed",NOW(),completed_at) WHERE id=?',[$next,$next,$oid]);
   if($next==='completed'){
    if($o['payment_method']==='cod')execsql('INSERT IGNORE INTO cod_collections(order_id,collected_by,note) VALUES(?,?,?)',[$oid,user()['id'],'Xưởng xác nhận đã giao. Chờ đối soát COD.']);
    payout_record_completed_order($o);
   }
   $pdo->commit();flash('Đã chuyển đơn sang '.order_status_label($next).'.');go('vendor_orders');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
 }
}

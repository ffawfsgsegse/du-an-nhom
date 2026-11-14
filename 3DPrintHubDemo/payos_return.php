<?php
// Display only. URL parameters alone NEVER confirm successful payments.
require __DIR__.'/config.php';
$id=(int)($_GET['order_id']??0);
if(!user()){header('Location: index.php?page=login');exit;}
$o=dbone('SELECT o.id,o.order_code,o.total_amount,op.status FROM orders o JOIN order_payments op ON op.order_id=o.id WHERE o.id=? AND o.user_id=?',[$id,user()['id']]);
if(!$o){http_response_code(404);exit('Không tìm thấy đơn hàng');}
header('Location: index.php?page=payment&id='.$id,true,303);exit;

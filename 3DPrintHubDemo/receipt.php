<?php
/** Secure payment receipt viewer: never expose raw upload URLs. */
require __DIR__.'/config.php';
if(!user()) { http_response_code(401); exit('Vui lòng đăng nhập.'); }
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if(!$id) { http_response_code(400); exit('ID không hợp lệ.'); }
$order=dbone('SELECT o.id,o.user_id,o.vendor_id,op.receipt_mime,op.receipt_data,v.user_id AS vendor_user_id FROM orders o JOIN order_payments op ON op.order_id=o.id JOIN vendors v ON v.id=o.vendor_id WHERE o.id=?',[$id]);
$u=user();
if(!$order || ($u['role']!=='admin' && (int)$order['user_id']!==(int)$u['id'] && (int)$order['vendor_user_id']!==(int)$u['id'])) { http_response_code(404);exit('Không tìm thấy biên lai.'); }
if(!$order['receipt_mime']||$order['receipt_data']===null||!in_array($order['receipt_mime'],['image/jpeg','image/png','image/webp'],true)) { http_response_code(404);exit('Chưa có biên lai.'); }
header('Content-Type: '.$order['receipt_mime']);
header('Content-Disposition: inline; filename="receipt-'.$id.'"');
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; sandbox");
header('Content-Length: '.strlen($order['receipt_data']));
echo $order['receipt_data'];

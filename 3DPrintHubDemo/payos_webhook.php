<?php
/** Public HTTPS endpoint registered in payOS dashboard. NO session/CSRF needed.
 * DO NOT mark paid from returnUrl or client-side buttons.
 */
require __DIR__.'/config.php';
require __DIR__.'/payos_lib.php';
require __DIR__.'/wallet_lib.php';
header('Content-Type: application/json; charset=utf-8');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['code'=>'01','desc'=>'method']);exit;}
$cfg=payos_secrets();
if(!$cfg){http_response_code(503);echo json_encode(['code'=>'01','desc'=>'not configured']);exit;}
$raw=file_get_contents('php://input');
if(!is_string($raw)||strlen($raw)>65536){http_response_code(400);echo json_encode(['code'=>'01','desc'=>'size']);exit;}
$payload=json_decode($raw,true);
if(!is_array($payload)||!payos_verify_webhook($payload,$cfg['checksum_key'])){
 http_response_code(400);echo json_encode(['code'=>'01','desc'=>'signature']);exit;
}
$data=$payload['data'];
// payOS may send a signed sample webhook when confirming a channel; ignore unknown orders.
if(($payload['success']??false)!==true || ($payload['code']??'')!=='00' || ($data['code']??'')!=='00'){
 echo json_encode(['code'=>'00','desc'=>'success']);exit;
}
$code=(int)($data['orderCode']??0);
$amount=(int)($data['amount']??0);
$paymentLinkId=(string)($data['paymentLinkId']??'');
$currency=(string)($data['currency']??'VND');
if($code<1 || $amount<1 || $currency!=='VND'){
 http_response_code(422);echo json_encode(['code'=>'01','desc'=>'invalid transaction']);exit;
}
try{
 $pdo->beginTransaction();
 if($code >= 3000000000){
   wallet_process_verified_topup($data);
   $pdo->commit();echo json_encode(['code'=>'00','desc'=>'success']);exit;
 }
 $tx=dbone('SELECT t.*,o.total_amount,o.order_status,op.status AS payment_status,op.payment_provider FROM payos_transactions t JOIN orders o ON o.id=t.order_id JOIN order_payments op ON op.order_id=t.order_id WHERE t.payos_order_code=? FOR UPDATE',[$code]);
 if(!$tx){$pdo->commit();echo json_encode(['code'=>'00','desc'=>'success']);exit;}
 if($tx['status']==='paid' && $tx['payment_status']==='confirmed'){
   $pdo->commit();echo json_encode(['code'=>'00','desc'=>'success']);exit;
 }
 if($tx['payment_provider']!=='payos'||$tx['order_status']==='cancelled' || $amount!==(int)$tx['amount'] || $amount!==(int)round((float)$tx['total_amount']) || !hash_equals((string)$tx['payment_link_id'],$paymentLinkId)){
   error_log('payOS webhook mismatch for code '.$code);
   $pdo->rollBack();http_response_code(422);echo json_encode(['code'=>'01','desc'=>'mismatch']);exit;
 }
 execute('UPDATE payos_transactions SET status="paid",bank_reference=?,paid_at=NOW() WHERE order_id=? AND status!="paid"',[(string)($data['reference']??''),$tx['order_id']]);
 execute('UPDATE order_payments SET status="confirmed",reviewed_at=NOW(),review_note="Đã xác minh tự động qua payOS webhook" WHERE order_id=? AND payment_provider="payos"',[$tx['order_id']]);
 $pdo->commit();
 echo json_encode(['code'=>'00','desc'=>'success']);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('payOS webhook: '.$e->getMessage());http_response_code(500);echo json_encode(['code'=>'01','desc'=>'server error']);}

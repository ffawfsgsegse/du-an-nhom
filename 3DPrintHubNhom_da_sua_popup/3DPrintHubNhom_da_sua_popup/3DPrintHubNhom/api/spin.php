<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function reply($code,$data){http_response_code($code);echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST')reply(405,['success'=>false,'message'=>'Chỉ hỗ trợ POST']);
$userId=(int)($_SESSION['uid']??0);
if(!$userId)reply(401,['success'=>false,'message'=>'Vui lòng đăng nhập']);
$lock='wheel_spin_'.$userId.'_'.date('Y-m-d');
try {
 $q=$pdo->prepare('SELECT GET_LOCK(?,5)');$q->execute([$lock]);
 if((int)$q->fetchColumn()!==1)reply(429,['success'=>false,'message'=>'Vui lòng thử lại']);
 try {
  $pdo->beginTransaction();
  $q=$pdo->prepare('SELECT COUNT(*) FROM user_rewards WHERE user_id=? AND created_at>=CURDATE() AND created_at<CURDATE()+INTERVAL 1 DAY');$q->execute([$userId]);
  if((int)$q->fetchColumn()>0){$pdo->rollBack();reply(409,['success'=>false,'message'=>'Hôm nay bạn đã quay rồi']);}
  $rows=$pdo->query('SELECT id,name,discount_percent FROM promotions WHERE status=1 AND quantity>0 AND (start_date IS NULL OR start_date<=CURDATE()) AND (end_date IS NULL OR end_date>=CURDATE()) ORDER BY id FOR UPDATE')->fetchAll(PDO::FETCH_ASSOC);
  if(!$rows){$pdo->rollBack();reply(404,['success'=>false,'message'=>'Chưa có phần thưởng khả dụng']);}
  $prize=$rows[random_int(0,count($rows)-1)];
  $q=$pdo->prepare('UPDATE promotions SET quantity=quantity-1 WHERE id=? AND quantity>0');$q->execute([$prize['id']]);
  $q=$pdo->prepare('INSERT INTO user_rewards(user_id,promotion_id,created_at) VALUES(?,?,NOW())');$q->execute([$userId,$prize['id']]);
  $pdo->commit();reply(200,['success'=>true,'id'=>(int)$prize['id'],'name'=>$prize['name'],'discount_percent'=>(int)$prize['discount_percent']]);
 } finally { $q=$pdo->prepare('SELECT RELEASE_LOCK(?)');$q->execute([$lock]); }
} catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Wheel spin: '.$e->getMessage());reply(500,['success'=>false,'message'=>'Lỗi vòng quay hoặc thiếu bảng dữ liệu, kiểm tra hướng dẫn SQL']);}

<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED')||exit;
function auth_action(string $action): void {
 global $pdo;
 if($action==='logout'){
  $_SESSION=[]; session_regenerate_id(true); flash('Đã đăng xuất.');go('home');
 }
 if($action==='register'){
  if(user())go('home');
  $name=trim((string)($_POST['name']??''));$email=strtolower(trim((string)($_POST['email']??'')));$pw=(string)($_POST['password']??'');$confirm=(string)($_POST['confirm_password']??'');
  if(strlen($name)<2||strlen($name)>100)throw new RuntimeException('Họ tên cần 2–100 ký tự.');
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>120)throw new RuntimeException('Email không hợp lệ.');
  if(strlen($pw)<8||$pw!==$confirm)throw new RuntimeException('Mật khẩu từ 8 ký tự và xác nhận phải trùng nhau.');
  if(dbone('SELECT id FROM users WHERE email=?',[$email]))throw new RuntimeException('Email đã tồn tại.');
  execsql('INSERT INTO users(full_name,email,password,role,status) VALUES(?,?,?,"client",1)',[$name,$email,password_hash($pw,PASSWORD_DEFAULT)]);
  flash('Đăng ký thành công. Hãy đăng nhập.');go('login');
 }
 if($action==='login'){
  if(user())go('home');
  $email=strtolower(trim((string)($_POST['email']??'')));$pw=(string)($_POST['password']??'');
  $attempts=array_values(array_filter($_SESSION['login_attempts']??[],static fn($t)=>$t>time()-300));
  if(count($attempts)>=10)throw new RuntimeException('Quá nhiều lần đăng nhập sai. Hãy thử lại sau 5 phút.');
  $u=dbone('SELECT * FROM users WHERE email=?',[$email]);
  if(!$u||!(int)$u['status']||!password_verify($pw,$u['password'])){ $attempts[]=time();$_SESSION['login_attempts']=$attempts;throw new RuntimeException('Email hoặc mật khẩu không chính xác.'); }
  session_regenerate_id(true);unset($_SESSION['login_attempts']);$_SESSION['uid']=(int)$u['id'];
  flash('Đăng nhập thành công, chào '.$u['full_name'].'!');
  go($u['role']==='admin'?'admin_dashboard':($u['role']==='vendor'?'vendor_dashboard':'home'));
 }
 if($action==='apply_vendor'){
  $u=need_role('client');
  if(vendor_of_user())throw new RuntimeException('Bạn đã có hồ sơ đăng ký gian hàng.');
  $name=trim((string)($_POST['shop_name']??''));$address=trim((string)($_POST['address']??''));$capacity=trim((string)($_POST['capacity']??''));
  if(strlen($name)<3||strlen($name)>150||strlen($address)<5||strlen($address)>255)throw new RuntimeException('Tên gian hàng hoặc địa chỉ không hợp lệ.');
  execsql('INSERT INTO vendors(user_id,shop_name,address,printer_capacity,status) VALUES(?,?,?, ?, "pending")',[$u['id'],$name,$address,$capacity]);
  flash('Đã gửi hồ sơ. Admin sẽ duyệt trước khi bạn đăng hàng.');go('apply');
 }
}

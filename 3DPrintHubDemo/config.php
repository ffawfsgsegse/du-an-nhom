<?php
const DB_HOST='127.0.0.1'; const DB_NAME='3dprinthub_db'; const DB_USER='root'; const DB_PASS='';
try { $pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); }
catch(PDOException $e){http_response_code(500);exit('Không thể kết nối MySQL. Hãy import database.sql trong Laragon và kiểm tra config.php.');}
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function money($v){return number_format((float)$v,0,',','.').' ₫';}
function url($page='home',$extra=[]){return 'index.php?'.http_build_query(array_merge(['page'=>$page],$extra));}
function go($page='home',$extra=[]){header('Location: '.url($page,$extra));exit;}
function user(){return $_SESSION['user']??null;}
function require_login(){if(!user())go('login');}
function require_role($role){require_login();if(user()['role']!==$role){http_response_code(403);exit('403 - Không có quyền truy cập');}}
function token(){if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function verify(){if(!hash_equals(token(),$_POST['csrf']??'')){http_response_code(419);exit('Phiên không hợp lệ');}}
function flash($s,$type='success'){$_SESSION['flash']=[$s,$type];}
function dball($sql,$params=[]){global $pdo;$q=$pdo->prepare($sql);$q->execute($params);return $q->fetchAll();}
function dbone($sql,$params=[]){global $pdo;$q=$pdo->prepare($sql);$q->execute($params);return $q->fetch();}
function execute($sql,$params=[]){global $pdo;$q=$pdo->prepare($sql);return $q->execute($params);}
function vendor(){return dbone('SELECT * FROM vendors WHERE user_id=?',[user()['id']]);}
function upload_image(){if(empty($_FILES['image']['name']))return null;$f=$_FILES['image'];if($f['error']!==UPLOAD_ERR_OK||$f['size']>4*1024*1024)throw new Exception('Ảnh tối đa 4MB');$mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;if(!$ext)throw new Exception('Chỉ nhận JPG, PNG, WEBP');$name=bin2hex(random_bytes(16)).'.'.$ext;if(!move_uploaded_file($f['tmp_name'],__DIR__.'/uploads/'.$name))throw new Exception('Không lưu được ảnh');return 'uploads/'.$name;}
function product_image($p){$path=$p['image_path']??null;return $path&&is_file(__DIR__.'/'.$path)?$path:null;}

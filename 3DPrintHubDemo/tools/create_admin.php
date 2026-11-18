<?php
declare(strict_types=1);
/* Chạy trong Terminal: php tools/create_admin.php (KHÔNG chạy từ trình duyệt). */
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Chỉ chạy từ dòng lệnh.');}
require dirname(__DIR__).'/config.php';
function ask(string $question): string {echo $question;return trim((string)fgets(STDIN));}
$email=strtolower(ask("Email Admin mới: "));
$name=ask("Họ tên: ");
$password=ask("Mật khẩu (sẽ hiển thị khi nhập, dùng mật khẩu riêng): ");
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($name)<2||strlen($password)<10)exit("Email, tên hoặc mật khẩu không hợp lệ (ít nhất 10 ký tự).\n");
$s=$pdo->prepare('SELECT id FROM users WHERE email=?');$s->execute([$email]);
if($s->fetch())exit("Email đã tồn tại. Không thay đổi vai trò của tài khoản cũ.\n");
$s=$pdo->prepare('INSERT INTO users(full_name,email,password,role,status) VALUES(?,?,?,"admin",1)');
$s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
echo "Đã tạo Admin #".$pdo->lastInsertId().". Hãy đăng nhập qua website.\n";

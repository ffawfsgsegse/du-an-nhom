<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;

function h(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $page='home', array $params=[]): string {
 // Các link Admin đi vào file admin.php riêng, giữ nguyên dữ liệu POST và phân quyền cũ.
 if (str_starts_with($page, 'admin_')) {
  return 'admin.php?'.http_build_query(array_merge(['section'=>substr($page, 6)], $params));
 }
 return 'index.php?'.http_build_query(array_merge(['page'=>$page],$params));
}
function go(string $page='home', array $params=[]): never { header('Location: '.url($page,$params),true,303); exit; }
function money(float|int|string $n): string { return number_format((float)$n,0,',','.').'đ'; }
function dball(string $sql,array $args=[]): array { global $pdo; $s=$pdo->prepare($sql);$s->execute($args);return $s->fetchAll(); }
function dbone(string $sql,array $args=[]): ?array { global $pdo; $s=$pdo->prepare($sql);$s->execute($args);return $s->fetch()?:null; }
function execsql(string $sql,array $args=[]): int { global $pdo; $s=$pdo->prepare($sql);$s->execute($args);return $s->rowCount(); }
function csrf_token(): string { return $_SESSION['csrf']??=bin2hex(random_bytes(32)); }
function csrf_field(): void { echo '<input type="hidden" name="csrf" value="'.h(csrf_token()).'">'; }
function check_csrf(): void { if(!hash_equals(csrf_token(),(string)($_POST['csrf']??'')))throw new RuntimeException('Phiên xác thực không hợp lệ. Hãy tải lại trang.'); }
function flash(string $msg,string $type='success'): void { $_SESSION['flash']=['message'=>$msg,'type'=>$type]; }
function positive_int(mixed $v,int $max=1000000): int { $n=filter_var($v,FILTER_VALIDATE_INT);if($n===false||$n<1||$n>$max)throw new RuntimeException('Giá trị số lượng không hợp lệ.');return (int)$n; }
function safe_price(mixed $v): int { $n=filter_var($v,FILTER_VALIDATE_INT);if($n===false||$n<1000||$n>1000000000)throw new RuntimeException('Giá phải từ 1.000đ đến 1 tỷ đồng.');return (int)$n; }
function cart_count(): int { return array_sum(array_map('intval',$_SESSION['cart']??[])); }
function order_status_label(string $s): string { return ['pending'=>'Chờ xác nhận','printing'=>'Đang sản xuất','shipping'=>'Đang giao hàng','completed'=>'Hoàn tất','cancelled'=>'Đã hủy'][$s]??$s; }
function upload_image(string $name='image'): ?string {
 if(empty($_FILES[$name])||($_FILES[$name]['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
 $f=$_FILES[$name];if($f['error']!==UPLOAD_ERR_OK||$f['size']<1||$f['size']>3*1024*1024)throw new RuntimeException('Ảnh phải nhỏ hơn 3MB.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
 $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;
 if(!$ext||!getimagesize($f['tmp_name']))throw new RuntimeException('Ảnh phải là JPG/PNG/WEBP hợp lệ.');
 $path='uploads/products/'.bin2hex(random_bytes(16)).'.'.$ext;
 if(!move_uploaded_file($f['tmp_name'],__DIR__.'/../'.$path))throw new RuntimeException('Không lưu được hình ảnh. Kiểm tra quyền ghi thư mục uploads/products.');
 return $path;
}
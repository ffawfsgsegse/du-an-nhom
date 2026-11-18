<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED')||exit;
function admin_action(string $action): void {
 global $pdo;
 need_role('admin');
 if($action==='admin_vendor_review'){
  $id=positive_int($_POST['id']??0);$status=(string)($_POST['status']??'');if(!in_array($status,['approved','rejected'],true))throw new RuntimeException('Trạng thái duyệt không hợp lệ.');
  $pdo->beginTransaction();try{
   $v=dbone('SELECT * FROM vendors WHERE id=? FOR UPDATE',[$id]);if(!$v)throw new RuntimeException('Không tìm thấy gian hàng.');
   execsql('UPDATE vendors SET status=? WHERE id=?',[$status,$id]);
   if($status==='approved')execsql('UPDATE users SET role="vendor" WHERE id=? AND role="client"',[$v['user_id']]);
   $pdo->commit();flash('Đã cập nhật trạng thái gian hàng.');go('admin_vendors');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
 }
 if($action==='admin_user_toggle'){
  $id=positive_int($_POST['id']??0);$target=dbone('SELECT id,role,status FROM users WHERE id=?',[$id]);
  if(!$target||$target['role']==='admin'||$id===(int)user()['id'])throw new RuntimeException('Không thể thay đổi tài khoản Admin.');
  execsql('UPDATE users SET status=1-status WHERE id=?',[$id]);flash('Đã cập nhật tài khoản.');go('admin_users');
 }
 if($action==='admin_category_save'){
  $id=(int)($_POST['id']??0);$name=trim((string)($_POST['name']??''));$slug=strtolower(trim((string)($_POST['slug']??'')));$description=trim((string)($_POST['description']??''));
  if(strlen($name)<3||strlen($name)>120||!preg_match('/^[a-z0-9-]{3,150}$/',$slug))throw new RuntimeException('Tên danh mục hoặc slug không hợp lệ (slug: chữ thường, số, gạch nối).');
  if($id>0){execsql('UPDATE categories SET name=?,slug=?,description=? WHERE id=?',[$name,$slug,$description,$id]);}
  else execsql('INSERT INTO categories(name,slug,description,status) VALUES(?,?,?,1)',[$name,$slug,$description]);
  flash('Đã lưu danh mục.');go('admin_categories');
 }
 if($action==='admin_category_toggle'){
  $id=positive_int($_POST['id']??0);execsql('UPDATE categories SET status=1-status WHERE id=?',[$id]);flash('Đã cập nhật hiển thị danh mục.');go('admin_categories');
 }
}

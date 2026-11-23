<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED')||exit;
function product_action(string $action): void {
 global $pdo;
 $admin=str_starts_with($action,'admin_');
 if($admin)need_role('admin');else $seller=approved_vendor();
 $id=(int)($_POST['id']??0);
 if(in_array($action,['vendor_product_delete','admin_product_delete'],true)){
  $p=dbone('SELECT * FROM products WHERE id=? AND deleted_at IS NULL',[$id]);
  if(!$p||(!$admin && (int)$p['vendor_id']!==(int)$seller['id']))throw new RuntimeException('Không tìm thấy sản phẩm có quyền quản lý.');
  execsql('UPDATE products SET deleted_at=NOW(),status="hidden" WHERE id=?',[$id]);flash('Đã xóa mềm sản phẩm. Dữ liệu đơn hàng được giữ lại.');go($admin?'admin_products':'vendor_products');
 }
 if(in_array($action,['admin_product_approve','admin_product_hide','vendor_product_hide'],true)){
  $p=dbone('SELECT * FROM products WHERE id=? AND deleted_at IS NULL',[$id]);
  if(!$p||(!$admin&&(int)$p['vendor_id']!==(int)$seller['id']))throw new RuntimeException('Không tìm thấy sản phẩm có quyền xử lý.');
  execsql('UPDATE products SET status=? WHERE id=?',[$action==='admin_product_approve'?'active':'hidden',$id]);flash('Đã cập nhật trạng thái sản phẩm.');go($admin?'admin_products':'vendor_products');
 }
 if(in_array($action,['vendor_product_save','admin_product_save'],true)){
  $vendor=$admin?positive_int($_POST['vendor_id']??0): (int)$seller['id'];
  $name=trim((string)($_POST['name']??''));$price=safe_price($_POST['price']??0);$stock=(int)($_POST['stock']??-1);$category=positive_int($_POST['category_id']??0);$material=positive_int($_POST['material_id']??0);$dimensions=trim((string)($_POST['dimensions']??''));
  if(strlen($name)<3||strlen($name)>200||$stock<0||$stock>100000||strlen($dimensions)>100)throw new RuntimeException('Tên, tồn kho hoặc kích thước không hợp lệ.');
  if(!dbone('SELECT id FROM vendors WHERE id=? AND status="approved"',[$vendor]))throw new RuntimeException('Gian hàng chưa được duyệt.');
  if(!dbone('SELECT id FROM categories WHERE id=? AND status=1',[$category]))throw new RuntimeException('Danh mục không hợp lệ.');
  if(!dbone('SELECT id FROM materials WHERE id=? AND vendor_id=? AND status=1',[$material,$vendor]))throw new RuntimeException('Vật liệu không thuộc gian hàng đã chọn.');
  if($id){$old=dbone('SELECT * FROM products WHERE id=? AND deleted_at IS NULL',[$id]);if(!$old||(!$admin&&(int)$old['vendor_id']!==(int)$vendor))throw new RuntimeException('Không được sửa sản phẩm của gian hàng khác.');}
  $image=upload_image();
  $pdo->beginTransaction();
  try{
   if($id){execsql('UPDATE products SET vendor_id=?,category_id=?,material_id=?,name=?,price=?,stock=?,dimensions=?,status=? WHERE id=?',[$vendor,$category,$material,$name,$price,$stock,$dimensions,$admin?'active':'pending',$id]);}
   else{execsql('INSERT INTO products(vendor_id,category_id,material_id,name,price,stock,dimensions,status) VALUES(?,?,?,?,?,?,?,?)',[$vendor,$category,$material,$name,$price,$stock,$dimensions,$admin?'active':'pending']);$id=(int)$pdo->lastInsertId();}
   if($image){execsql('INSERT INTO product_images(product_id,image_path) VALUES(?,?) ON DUPLICATE KEY UPDATE image_path=VALUES(image_path)',[$id,$image]);}
   $pdo->commit();flash($admin?'Đã lưu sản phẩm.':'Đã gửi sản phẩm chờ Admin duyệt.');go($admin?'admin_products':'vendor_products');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();if($image && is_file(__DIR__.'/../../'.$image))unlink(__DIR__.'/../../'.$image);throw $e;}
 }
 throw new RuntimeException('Thao tác sản phẩm không hợp lệ.');
}

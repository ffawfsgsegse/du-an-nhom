<?php
defined('APP_BOOTSTRAPPED')||exit;
$adminMode=true;$id=(int)($_GET['id']??0);$product=null;
if($id>0){$product=dbone('SELECT p.*,pi.image_path FROM products p LEFT JOIN product_images pi ON pi.product_id=p.id WHERE p.id=? AND p.deleted_at IS NULL',[$id]);if(!$product)http_response_code(404);}
$vendors=dball('SELECT * FROM vendors WHERE status="approved" ORDER BY shop_name');$materials=all_vendor_materials();$cats=categories();
?>
<div class="admin-page-head"><div><div class="admin-kicker">BIÊN TẬP SẢN PHẨM</div><h1><?=$id?'Chỉnh sửa':'Thêm mới'?> sản phẩm</h1><p>Thêm sản phẩm thuộc một gian hàng đã duyệt, chọn danh mục và vật liệu tương ứng.</p></div><a class="admin-btn admin-btn-outline" href="<?=h(url('admin_products'))?>">← Quay lại danh sách</a></div>
<section class="admin-panel admin-product-edit-panel">
<?php if($id && !$product):?><div class="admin-empty-text">Sản phẩm không tồn tại hoặc đã bị xóa.</div><?php elseif(!$vendors):?><div class="admin-empty-text">Chưa có gian hàng đã duyệt. Hãy duyệt gian hàng trước khi thêm sản phẩm.</div><?php else:?>
<?php require __DIR__.'/../partials/product_form.php';?>
<?php endif;?></section>

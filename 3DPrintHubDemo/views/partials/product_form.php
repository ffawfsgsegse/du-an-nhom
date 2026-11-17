<?php defined('APP_BOOTSTRAPPED')||exit;
$editMode=isset($product)&&$product;
$actionName=$adminMode?'admin_product_save':'vendor_product_save';
$pageName=$adminMode?'admin_product_edit':'vendor_product_edit';
$returnPage=$adminMode?'admin_products':'vendor_products';
?>
<form method="post" enctype="multipart/form-data" action="<?=h(url($pageName,$editMode?['id'=>$product['id']]:[]))?>" class="stack product-editor-form"><?php csrf_field();?><input type="hidden" name="action" value="<?=h($actionName)?>"><input type="hidden" name="id" value="<?=$editMode?(int)$product['id']:0?>">
<?php if($adminMode):?><label>Chọn gian hàng (đã duyệt)<select name="vendor_id" id="vendorSelect" required><option value="">-- Chọn shop --</option><?php foreach($vendors as $v):?><option value="<?=$v['id']?>" <?=($editMode&&(int)$product['vendor_id']===(int)$v['id'])?'selected':''?>><?=h($v['shop_name'])?></option><?php endforeach;?></select></label><?php endif;?>
<div class="market-form-row"><label>Tên sản phẩm<input name="name" required maxlength="200" minlength="3" value="<?=h($product['name']??'')?>" placeholder="Ví dụ: Cánh tay Robot 4 bậc"></label><label>Giá bán (VNĐ)<input type="number" name="price" min="1000" max="1000000000" step="1000" value="<?=h($product['price']??'')?>" required></label></div>
<div class="market-form-row"><label>Danh mục<select name="category_id" required><option value="">-- Chọn danh mục --</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=($editMode&&(int)$product['category_id']===(int)$c['id'])?'selected':''?>><?=h($c['name'])?></option><?php endforeach;?></select></label><label>Vật liệu<select name="material_id" id="materialSelect" required><option value="">-- Chọn vật liệu --</option><?php foreach($materials as $m):?><option data-vendor="<?=$m['vendor_id']?>" value="<?=$m['id']?>" <?=($editMode&&(int)$product['material_id']===(int)$m['id'])?'selected':''?>><?=h($m['name'])?><?= $adminMode?' · '.h($m['shop_name']):'' ?></option><?php endforeach;?></select></label></div>
<div class="market-form-row"><label>Số lượng tồn kho<input type="number" name="stock" min="0" max="100000" required value="<?=h($product['stock']??0)?>"></label><label>Kích thước / Quy cách<input name="dimensions" maxlength="100" value="<?=h($product['dimensions']??'')?>" placeholder="180 × 120 × 210 mm"></label></div>
<label>Ảnh sản phẩm JPG/PNG/WEBP (tối đa 3MB)<input type="file" name="image" accept="image/png,image/jpeg,image/webp"></label>
<?php if($editMode && !empty($product['image_path'])):?><div class="editor-preview"><img src="<?=h($product['image_path'])?>" alt="Ảnh hiện tại"><span>Ảnh đang sử dụng. Chọn ảnh mới để thay.</span></div><?php endif;?>
<div class="dashboard-actions"><button class="btn btn-primary btn-lg">LƯU SẢN PHẨM →</button><a class="btn btn-outline" href="<?=h(url($returnPage))?>">Hủy</a></div>
</form>

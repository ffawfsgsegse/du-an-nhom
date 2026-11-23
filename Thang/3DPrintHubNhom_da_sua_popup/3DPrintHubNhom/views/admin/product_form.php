<?php defined('APP_BOOTSTRAPPED')||exit;$adminMode=true;$id=(int)($_GET['id']??0);$product=null;
if($id){$product=dbone('SELECT p.*,pi.image_path FROM products p LEFT JOIN product_images pi ON pi.product_id=p.id WHERE p.id=? AND p.deleted_at IS NULL',[$id]);if(!$product)http_response_code(404);}
$vendors=dball('SELECT * FROM vendors WHERE status="approved" ORDER BY shop_name');$materials=all_vendor_materials();$cats=categories(); ?>
<section class="container section page-top"><div class="dash-layout"><?php require __DIR__.'/../partials/side_nav.php';?><div class="dash-main"><div class="eyebrow">ADMIN PRODUCT EDITOR</div><h1 class="page-title"><?=$id?'Sửa':'Thêm'?> <em>sản phẩm</em></h1>
<div class="panel"><?php if($id&&!$product):?><h2>Sản phẩm không tồn tại.</h2><?php elseif(!$vendors):?><p>Chưa có gian hàng được duyệt. Hãy duyệt shop trước.</p><?php else:?><?php require __DIR__.'/../partials/product_form.php';?><?php endif;?></div></div></div></section>

<?php
defined('APP_BOOTSTRAPPED')||exit;
$products = all_products_admin();
$shops = []; $cats = [];
foreach($products as $p){$shops[$p['shop_name']]=$p['shop_name'];$cats[$p['category_name']]=$p['category_name'];}
natcasesort($shops);natcasesort($cats);
?>
<div class="admin-page-head"><div><div class="admin-kicker">QUẢN LÝ SÀN IN 3D</div><h1>Quản lý sản phẩm</h1><p>Duyệt, chỉnh sửa, ẩn và quản lý sản phẩm của mọi gian hàng.</p></div><div class="admin-head-actions"><a href="<?=h(url('admin_product_edit'))?>" class="admin-btn admin-btn-primary"><?=admin_icon('plus')?> Thêm sản phẩm</a></div></div>
<section class="admin-panel"><div class="admin-section-title"><div><h2>Danh sách sản phẩm</h2><p>Danh sách lấy từ MySQL, các bộ lọc hoạt động đồng thời.</p></div><span class="admin-soft-badge"><?=count($products)?> sản phẩm gần nhất</span></div>
<?php $filterTable='admin-products-table';$filterPlaceholder='Tìm tên, mã, gian hàng, danh mục, vật liệu...';$filterFields=[
 ['key'=>'status','label'=>'Trạng thái','options'=>['pending'=>'Chờ duyệt','active'=>'Đang bán','hidden'=>'Đã ẩn']],
 ['key'=>'shop','label'=>'Gian hàng','options'=>$shops],
 ['key'=>'category','label'=>'Danh mục','options'=>$cats],
];require __DIR__.'/_filters.php';?>
<div class="admin-table-scroll"><table class="admin-table" id="admin-products-table"><thead><tr><th>Sản phẩm</th><th>Danh mục / Shop</th><th>Giá bán</th><th>Tồn kho</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach($products as $p):?><tr data-admin-row data-status="<?=h($p['status'])?>" data-shop="<?=h($p['shop_name'])?>" data-category="<?=h($p['category_name'])?>" data-search="<?=h(implode(' ',[$p['id'],$p['name'],$p['shop_name'],$p['category_name'],$p['material_name'],$p['price'],$p['status']]))?>">
<td><div class="admin-product-cell"><span class="admin-product-thumb"><?php if($p['image_path']):?><img loading="lazy" src="<?=h($p['image_path'])?>" alt=""><?php else:?>⬡<?php endif;?></span><span><strong><?=h($p['name'])?></strong><small>#<?=h($p['id'])?></small></span></div></td><td><?=h($p['category_name'])?><small class="admin-subtext"><?=h($p['shop_name'])?></small></td><td><strong><?=money($p['price'])?></strong></td><td><?=h($p['stock'])?></td><td><span class="admin-badge status-<?=h($p['status'])?>"><?=h(admin_product_status($p['status']))?></span></td><td><div class="admin-table-actions"><a class="admin-table-btn" href="<?=h(url('admin_product_edit',['id'=>$p['id']]))?>">Sửa</a>
<?php if($p['status']!=='active'):?><form method="post" action="<?=h(url('admin_products'))?>"><?php csrf_field();?><input type="hidden" name="action" value="admin_product_approve"><input type="hidden" name="id" value="<?=h($p['id'])?>"><button class="admin-table-btn success">Duyệt</button></form><?php endif;?>
<?php if($p['status']!=='hidden'):?><form method="post" action="<?=h(url('admin_products'))?>"><?php csrf_field();?><input type="hidden" name="action" value="admin_product_hide"><input type="hidden" name="id" value="<?=h($p['id'])?>"><button class="admin-table-btn">Ẩn</button></form><?php endif;?>
<form method="post" action="<?=h(url('admin_products'))?>" data-confirm="Xóa mềm sản phẩm này? Đơn hàng cũ vẫn được giữ."><?php csrf_field();?><input type="hidden" name="action" value="admin_product_delete"><input type="hidden" name="id" value="<?=h($p['id'])?>"><button class="admin-table-btn danger">Xóa</button></form></div></td></tr><?php endforeach;?>
</tbody></table><div class="admin-filter-empty" data-empty-for="admin-products-table" hidden>Không tìm thấy sản phẩm phù hợp với bộ lọc.</div><?php if(!$products):?><div class="admin-empty-text">Chưa có sản phẩm nào.</div><?php endif;?></div></section>

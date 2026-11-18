<?php defined('APP_BOOTSTRAPPED')||exit;
$adminMode=str_starts_with($page,'admin_');
$links=$adminMode?[
 ['admin_dashboard','◈','Tổng quan'],['admin_products','▣','Quản lý sản phẩm'],['admin_categories','◧','Danh mục'],['admin_vendors','♜','Gian hàng'],['admin_users','♙','Người dùng'],['admin_orders','▤','Đơn hàng'],['admin_analytics','▥','Thống kê doanh thu']
]:[['vendor_dashboard','◈','Tổng quan'],['vendor_products','▣','Sản phẩm / vật liệu'],['vendor_orders','▤','Đơn hàng']];
?>
<aside class="panel dash-sidebar"><div class="dash-side-header">✦ <?= $adminMode?'ADMIN CONTROL':'KÊNH NGƯỜI BÁN' ?></div>
<?php foreach($links as [$key,$icon,$label]):?><a class="dash-side-link <?=($page===$key||($page===$key.'_edit'))?'is-active':''?>" href="<?=h(url($key))?>"><span><?=h($icon)?></span><?=h($label)?></a><?php endforeach;?>
<div class="dash-side-foot">3DPrintHub · Quản trị theo vai trò</div></aside>

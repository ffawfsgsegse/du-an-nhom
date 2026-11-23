<?php
/** Giao diện Admin riêng, có thể chỉnh menu và topbar tại đây. */
defined('APP_BOOTSTRAPPED') || exit;
$menu = [
 ['admin_dashboard','layout-dashboard','Tổng quan'],
 ['admin_products','package','Sản phẩm'],
 ['admin_categories','layers','Danh mục'],
 ['admin_vendors','store','Gian hàng'],
 ['admin_users','users','Người dùng'],
 ['admin_orders','shopping-bag','Đơn hàng'],
 ['admin_analytics','chart-no-axes-combined','Thống kê'],
];
$adminLabels = [
 'admin_dashboard'=>'Tổng quan','admin_products'=>'Sản phẩm','admin_product_edit'=>'Chỉnh sửa sản phẩm',
 'admin_categories'=>'Danh mục','admin_vendors'=>'Gian hàng','admin_users'=>'Người dùng',
 'admin_orders'=>'Đơn hàng','admin_analytics'=>'Thống kê',
 'admin_payouts'=>'Chi trả gian hàng','admin_wallet_withdrawals'=>'Rút ví khách hàng',
];
$adminTitle = $adminLabels[$page] ?? 'Quản trị';
$pendingVendorCount = (int)(dbone('SELECT COUNT(*) n FROM vendors WHERE status="pending"')['n'] ?? 0);
$pendingProductCount = (int)(dbone('SELECT COUNT(*) n FROM products WHERE status="pending" AND deleted_at IS NULL')['n'] ?? 0);
$pendingCount = $pendingVendorCount + $pendingProductCount;
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#061d2c">
<title><?=h($adminTitle)?> · Quản trị 3DPrintHub</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="assets/css/admin_portal.css">
<link rel="stylesheet" href="assets/css/admin_sales_insights.css">
</head>
<body class="admin-portal">
<div class="admin-app" id="admin-app">
 <div class="admin-mobile-overlay" id="admin-mobile-overlay"></div>
 <aside class="admin-sidebar" id="admin-sidebar" aria-label="Menu quản trị">
  <a class="admin-brand" href="<?=h(url('admin_dashboard'))?>" aria-label="3DPrintHub - trang quản trị">
   <span class="admin-brand-mark">⬡</span><span class="admin-brand-text">3D<span>PRINT</span>HUB<small>TRUNG TÂM QUẢN TRỊ</small></span>
  </a>
  <div class="admin-sidebar-label">KHÔNG GIAN LÀM VIỆC</div>
  <nav class="admin-menu" aria-label="Chức năng quản trị">
   <?php foreach ($menu as [$itemPage,$icon,$label]):
    $active = $page===$itemPage || ($itemPage==='admin_products' && $page==='admin_product_edit');
   ?>
    <a class="admin-menu-link <?=$active?'is-active':''?>" href="<?=h(url($itemPage))?>" <?=$active?'aria-current="page"':''?>>
     <span class="admin-nav-icon" aria-hidden="true"><?=admin_icon($icon)?></span>
     <span><?=h($label)?></span>
     <?php $badgeCount = $itemPage==='admin_vendors'?$pendingVendorCount:($itemPage==='admin_products'?$pendingProductCount:0); if($badgeCount>0):?><span class="admin-nav-count"><?=h($badgeCount)?></span><?php endif;?>
    </a>
   <?php endforeach;?>
  </nav>
  <div class="admin-sidebar-label admin-sidebar-label-secondary">VẬN HÀNH</div>
  <nav class="admin-menu" aria-label="Công cụ vận hành">
   <a class="admin-menu-link <?=$page==='admin_payouts'?'is-active':''?>" href="<?=h(url('admin_payouts'))?>"><span class="admin-nav-icon"><?=admin_icon('wallet')?></span><span>Chi trả gian hàng</span></a>
   <a class="admin-menu-link <?=$page==='admin_wallet_withdrawals'?'is-active':''?>" href="<?=h(url('admin_wallet_withdrawals'))?>"><span class="admin-nav-icon"><?=admin_icon('credit-card')?></span><span>Rút ví khách hàng</span></a>
   <a class="admin-menu-link" href="<?=h(url('home'))?>"><span class="admin-nav-icon"><?=admin_icon('external-link')?></span><span>Về trang bán hàng</span></a>
  </nav>
  <div class="admin-sidebar-bottom"><span class="admin-live-dot"></span><div><strong>3DPrintHub đang hoạt động</strong><small>Dữ liệu kết nối MySQL</small></div></div>
 </aside>
 <div class="admin-workspace">
  <header class="admin-topbar">
   <div class="admin-top-left">
    <button type="button" class="admin-icon-button admin-mobile-toggle" id="admin-menu-toggle" aria-label="Mở menu" aria-expanded="false"><?=admin_icon('menu')?></button>
    <div class="admin-breadcrumb"><a href="<?=h(url('admin_dashboard'))?>">Quản trị</a><span>/</span><strong><?=h($adminTitle)?></strong></div>
   </div>
   <div class="admin-top-actions">
    <form class="admin-global-search" action="admin.php" method="get" role="search">
      <input type="hidden" name="section" value="products">
      <?=admin_icon('search')?><input name="q" aria-label="Tìm nhanh sản phẩm" placeholder="Tìm nhanh sản phẩm..." maxlength="120">
      <kbd>/</kbd>
    </form>
    <a class="admin-top-quick" href="<?=h(url('admin_vendors'))?>" aria-label="Xem yêu cầu chờ duyệt" title="Chờ duyệt"><?=admin_icon('bell')?><?php if($pendingCount>0):?><i aria-hidden="true"></i><?php endif;?></a>
    <div class="admin-profile"><span class="admin-avatar"><?=h(admin_initial(user()['full_name'] ?? 'A'))?></span><span class="admin-profile-text"><strong><?=h(user()['full_name'])?></strong><small>Quản trị viên</small></span></div>
    <form method="post" action="<?=h(url('home'))?>" class="admin-logout"><?php csrf_field();?><input type="hidden" name="action" value="logout"><button type="submit" class="admin-icon-button" title="Đăng xuất" aria-label="Đăng xuất"><?=admin_icon('log-out')?></button></form>
   </div>
  </header>
  <?php if($flash):?><div class="admin-message <?=($flash['type']??'')==='error'?'admin-message-error':''?>" role="status"><?=h($flash['message'])?></div><?php endif;?>
  <?php if($errors):?><div class="admin-message admin-message-error" role="alert"><?=h(implode(' · ',$errors))?></div><?php endif;?>
  <main class="admin-content" id="main-content">
<?php
/* SVG icon helper được giữ trong giao diện Admin, không dùng thư viện/CDN. */

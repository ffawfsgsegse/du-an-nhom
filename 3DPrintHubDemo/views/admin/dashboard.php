<?php
defined('APP_BOOTSTRAPPED') || exit;
$stats = admin_overview();
$series = revenue_series('month');
$recent = dball('SELECT o.order_code,o.total_amount,o.order_status,o.created_at,u.full_name customer_name,v.shop_name FROM orders o JOIN users u ON u.id=o.user_id JOIN vendors v ON v.id=o.vendor_id ORDER BY o.id DESC LIMIT 7');
$orderStatuses = dball('SELECT order_status,COUNT(*) n FROM orders GROUP BY order_status');
$pendingProducts = dball('SELECT p.id,p.name,v.shop_name FROM products p JOIN vendors v ON v.id=p.vendor_id WHERE p.status="pending" AND p.deleted_at IS NULL ORDER BY p.id DESC LIMIT 3');
$pendingVendors = dball('SELECT v.id,v.shop_name,u.full_name FROM vendors v JOIN users u ON u.id=v.user_id WHERE v.status="pending" ORDER BY v.id DESC LIMIT 3');
$hour = (int)date('G');$greeting = $hour < 11 ? 'Chào buổi sáng' : ($hour < 18 ? 'Chào buổi chiều' : 'Chào buổi tối');
?>
<div class="admin-page-head">
 <div><div class="admin-kicker"><span class="admin-kicker-dot"></span> BẢNG ĐIỀU KHIỂN HỆ THỐNG</div><h1><?=$greeting?>, <?=h(user()['full_name'])?> <span class="admin-waving">✦</span></h1><p>Đây là tình hình hoạt động mới nhất của sàn 3DPrintHub.</p></div>
 <div class="admin-head-actions"><span class="admin-timestamp" id="admin-last-updated">Dữ liệu từ MySQL</span><button class="admin-btn admin-btn-outline" type="button" id="admin-refresh-now"><?=admin_icon('refresh-cw')?> Làm mới</button></div>
</div>
<div class="admin-metrics">
 <a class="admin-metric" href="<?=h(url('admin_users'))?>"><span class="admin-metric-icon violet"><?=admin_icon('users')?></span><span class="admin-metric-caption">Người dùng</span><strong data-stat="users"><?=h($stats['users'])?></strong><span class="admin-metric-help">Tài khoản trên sàn <?=admin_icon('arrow-up-right')?></span></a>
 <a class="admin-metric" href="<?=h(url('admin_vendors'))?>"><span class="admin-metric-icon cyan"><?=admin_icon('store')?></span><span class="admin-metric-caption">Gian hàng</span><strong data-stat="shops"><?=h($stats['shops'])?></strong><span class="admin-metric-help">Hồ sơ đã đăng ký <?=admin_icon('arrow-up-right')?></span></a>
 <a class="admin-metric" href="<?=h(url('admin_products'))?>"><span class="admin-metric-icon pink"><?=admin_icon('package')?></span><span class="admin-metric-caption">Sản phẩm</span><strong data-stat="products"><?=h($stats['products'])?></strong><span class="admin-metric-help">Chưa xóa mềm <?=admin_icon('arrow-up-right')?></span></a>
 <a class="admin-metric" href="<?=h(url('admin_orders'))?>"><span class="admin-metric-icon lime"><?=admin_icon('shopping-bag')?></span><span class="admin-metric-caption">Đơn hàng</span><strong data-stat="orders"><?=h($stats['orders'])?></strong><span class="admin-metric-help">Mọi trạng thái <?=admin_icon('arrow-up-right')?></span></a>
</div>
<div class="admin-dashboard-duo">
 <section class="admin-panel admin-revenue-hero">
  <div class="admin-panel-heading"><div><span class="admin-mini-label">TỔNG DOANH THU</span><h2 data-stat="revenue"><?=money($stats['revenue'])?></h2><p>Chỉ tính các đơn đã hoàn tất, không tính đơn hủy.</p></div><span class="admin-revenue-icon"><?=admin_icon('chart-no-axes-combined')?></span></div>
  <div class="admin-mini-stat-row"><div><span>Shop chờ duyệt</span><strong data-stat="pending_shops"><?=h($stats['pending_shops'])?></strong></div><div><span>Sản phẩm chờ duyệt</span><strong data-stat="pending_products"><?=h($stats['pending_products'])?></strong></div><a href="<?=h(url('admin_analytics'))?>">Xem phân tích <?=admin_icon('arrow-right')?></a></div>
 </section>
 <section class="admin-panel admin-review-panel"><div class="admin-section-title"><div><span class="admin-mini-label">KIỂM DUYỆT</span><h2>Việc cần xử lý</h2></div><span class="admin-review-count" data-stat="pending_total"><?=h($stats['pending_products']+$stats['pending_shops'])?> đang chờ</span></div>
  <a class="admin-review-link" href="<?=h(url('admin_vendors'))?>"><span class="admin-review-avatar">⌂</span><span><strong>Đơn đăng ký gian hàng</strong><small>Xem hồ sơ người bán mới</small></span><b data-stat="pending_shops"><?=h($stats['pending_shops'])?></b></a>
  <a class="admin-review-link" href="<?=h(url('admin_products'))?>"><span class="admin-review-avatar">⬡</span><span><strong>Sản phẩm chờ kiểm duyệt</strong><small>Duyệt sản phẩm trước khi mở bán</small></span><b data-stat="pending_products"><?=h($stats['pending_products'])?></b></a>
 </section>
</div>
<div class="admin-chart-grid">
 <section class="admin-panel"><div class="admin-section-title"><div><span class="admin-mini-label">PHÂN TÍCH DOANH THU</span><h2>Doanh thu theo tháng</h2><p>Giá trị thực từ đơn hàng hoàn tất</p></div><a class="admin-section-more" href="<?=h(url('admin_analytics'))?>">Xem chi tiết <?=admin_icon('arrow-up-right')?></a></div>
  <?php if (!$series):?><div class="admin-chart-empty">Chưa có đơn hoàn tất để hiển thị biểu đồ.</div><?php endif; ?>
  <div class="admin-chart-container"><canvas id="salesChart" role="img" aria-label="Biểu đồ doanh thu theo tháng"></canvas></div>
  <script type="application/json" id="admin-sales-data"><?=json_encode($series,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE)?></script>
 </section>
 <section class="admin-panel"><div class="admin-section-title"><div><span class="admin-mini-label">TRẠNG THÁI ĐƠN</span><h2>Phân bổ đơn hàng</h2><p>Tổng số đơn theo tiến độ</p></div></div>
  <div class="admin-donut-row"><canvas id="ordersDonut" aria-label="Tỷ lệ trạng thái đơn hàng" role="img" width="205" height="205"></canvas><div id="ordersDonutLegend" class="admin-donut-legend"></div></div>
  <script type="application/json" id="admin-status-data"><?=json_encode($orderStatuses,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE)?></script>
 </section>
</div>
<?php $salesInsights = admin_sales_insights(); require __DIR__.'/_sales_insights.php'; ?>
<div class="admin-chart-grid admin-last-grid">
 <section class="admin-panel"><div class="admin-section-title"><div><span class="admin-mini-label">HOẠT ĐỘNG GẦN ĐÂY</span><h2>Đơn hàng mới nhất</h2></div><a class="admin-section-more" href="<?=h(url('admin_orders'))?>">Tất cả đơn <?=admin_icon('arrow-right')?></a></div>
 <div class="admin-table-scroll"><table class="admin-table"><thead><tr><th>Đơn</th><th>Gian hàng</th><th>Số tiền</th><th>Trạng thái</th></tr></thead><tbody id="dashboard-orders-body">
 <?php foreach($recent as $o):?><tr><td><strong><?=h($o['order_code'])?></strong><span class="admin-subtext"><?=h(date('d/m/Y',strtotime($o['created_at'])))?></span></td><td><?=h($o['shop_name'])?></td><td><?=money($o['total_amount'])?></td><td><span class="admin-badge status-<?=h($o['order_status'])?>"><?=h(order_status_label($o['order_status']))?></span></td></tr><?php endforeach;?>
 <?php if(!$recent):?><tr><td colspan="4" class="admin-empty-row">Chưa có đơn hàng.</td></tr><?php endif;?>
 </tbody></table></div>
 </section>
 <section class="admin-panel"><div class="admin-section-title"><div><span class="admin-mini-label">HÀNG ĐỢI DUYỆT</span><h2>Yêu cầu gần đây</h2></div><a class="admin-section-more" href="<?=h(url('admin_vendors'))?>">Xem tất cả <?=admin_icon('arrow-right')?></a></div>
 <?php foreach($pendingVendors as $v):?><a class="admin-queue-item" href="<?=h(url('admin_vendors'))?>"><span class="admin-queue-flag">⌂</span><div><strong><?=h($v['shop_name'])?></strong><small>Gian hàng · <?=h($v['full_name'])?></small></div><span class="admin-badge status-pending">Chờ duyệt</span></a><?php endforeach;?>
 <?php foreach($pendingProducts as $p):?><a class="admin-queue-item" href="<?=h(url('admin_product_edit',['id'=>$p['id']]))?>"><span class="admin-queue-flag">⬡</span><div><strong><?=h($p['name'])?></strong><small>Sản phẩm · <?=h($p['shop_name'])?></small></div><span class="admin-badge status-pending">Chờ duyệt</span></a><?php endforeach;?>
 <?php if(!$pendingVendors && !$pendingProducts):?><p class="admin-empty-text">Tất cả yêu cầu đã được xử lý.</p><?php endif;?>
 </section>
</div>

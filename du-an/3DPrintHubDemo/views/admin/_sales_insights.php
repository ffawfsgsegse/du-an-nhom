<?php
/** Thống kê bán hàng 3DPrintHub - sửa giao diện 4 bảng tại đây. */
defined('APP_BOOTSTRAPPED') || exit;
$insights = $salesInsights ?? admin_sales_insights();
$locationData = $insights['locations'];
$insightLists = [
    ['top_products', '01', 'Sản phẩm bán nhiều nhất', 'Top 5 theo số lượng bán', $insights['top_products'], 'products', 'Chưa có sản phẩm đang mở bán.'],
    ['bottom_products', '02', 'Sản phẩm bán ít nhất', 'Bao gồm sản phẩm bán 0 chiếc', $insights['bottom_products'], 'products', 'Chưa có sản phẩm đang mở bán.'],
    ['top_locations', '03', 'Địa điểm mua nhiều nhất', 'Top 5 tỉnh/thành theo số đơn', $locationData['top'], 'locations', 'Chưa có tỉnh/thành xác định được.'],
    ['bottom_locations', '04', 'Địa điểm mua ít nhất', 'Chỉ tính nơi đã có đơn hoàn tất', $locationData['bottom'], 'locations', 'Chưa có tỉnh/thành xác định được.'],
];
?>
<section class="admin-sales-section" aria-label="Phân tích sản phẩm và khu vực mua hàng">
  <div class="admin-section-title admin-insights-heading">
    <div>
      <span class="admin-mini-label">THỐNG KÊ BÁN HÀNG</span>
      <h2>Sản phẩm &amp; địa điểm mua hàng</h2>
      <p>Xếp hạng theo đơn đã hoàn tất · dữ liệu trực tiếp từ MySQL</p>
    </div>
    <?php if ($page === 'admin_dashboard'): ?>
      <a href="<?=h(url('admin_analytics'))?>" class="admin-section-more">Báo cáo chi tiết <?=admin_icon('arrow-up-right')?></a>
    <?php endif; ?>
  </div>
  <div class="admin-insights-grid">
    <?php foreach ($insightLists as [$key, $number, $heading, $subtitle, $items, $kind, $emptyText]): ?>
    <article class="admin-panel admin-insight-card <?=str_starts_with($key, 'bottom_') ? 'is-low' : 'is-high'?>">
      <header class="admin-insight-card-head">
        <span class="admin-insight-badge <?=h($kind)?>" aria-hidden="true"><?=h($number)?></span>
        <div><h3><?=h($heading)?></h3><p><?=h($subtitle)?></p></div>
      </header>
      <div class="admin-insight-list" data-insight-list="<?=h($key)?>" data-insight-kind="<?=h($kind)?>" aria-live="polite">
        <?php foreach ($items as $i => $item): ?>
          <div class="admin-insight-item">
            <span class="admin-insight-position"><?=($i + 1)?></span>
            <div class="admin-insight-info"><strong><?=h($item['name'])?></strong>
              <small><?=h($kind === 'products' ? $item['shop_name'] : money($item['revenue']) . ' doanh thu')?></small>
            </div>
            <span class="admin-insight-value"><b><?=h(number_format((int)($kind === 'products' ? $item['units_sold'] : $item['orders_count']), 0, ',', '.'))?></b><small><?=h($kind === 'products' ? 'chiếc' : 'đơn')?></small></span>
          </div>
        <?php endforeach; ?>
        <?php if (!$items): ?><p class="admin-insight-empty"><?=h($emptyText)?></p><?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <p class="admin-insights-note">
    <strong>Cách tính:</strong> Sản phẩm đang mở bán được xếp theo tổng số chiếc trong đơn hoàn tất; hàng chưa bán được tính 0 chiếc.
    Địa điểm dựa vào tỉnh/thành nhận diện từ địa chỉ giao hàng; không tính các nơi chưa có đơn.
    <span data-location-unknown>
    <?php if ((int)$locationData['unknown_orders'] > 0): ?>
      Có <?=h(number_format((int)$locationData['unknown_orders'],0,',','.'))?> đơn hoàn tất chưa xác định được tỉnh/thành, không đưa vào bảng xếp hạng.
    <?php endif; ?>
    </span>
  </p>
</section>
<?php unset($insightLists, $locationData, $insights, $salesInsights); ?>

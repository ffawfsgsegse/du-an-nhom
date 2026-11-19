<?php
defined('APP_BOOTSTRAPPED') || exit;
$v = approved_vendor();
$summary = dbone(
    'SELECT 
        COALESCE(SUM(gross_amount), 0) AS gross_total,
        COALESCE(SUM(platform_fee), 0) AS fee_total,
        COALESCE(SUM(CASE WHEN status = "eligible" THEN net_amount ELSE 0 END), 0) AS eligible_total,
        COALESCE(SUM(CASE WHEN status = "paid" THEN net_amount ELSE 0 END), 0) AS paid_total
     FROM vendor_earnings WHERE vendor_id = ?',
    [$v['id']]
);
$rows = dball(
    'SELECT ve.*, o.order_code, o.created_at AS order_date
     FROM vendor_earnings ve
     JOIN orders o ON o.id = ve.order_id
     WHERE ve.vendor_id = ?
     ORDER BY ve.order_id DESC',
    [$v['id']]
);
?>
<section class="container section page-top">
    <div class="dash-layout">
        <?php require __DIR__ . '/../partials/side_nav.php'; ?>
        <div class="dash-main">
            <div class="eyebrow">REVENUE &amp; PAYOUTS</div>
            <h1 class="page-title">Doanh thu <em>&amp; đối soát</em></h1>
            <p class="muted">Theo dõi doanh thu từ các đơn hàng hoàn tất, phí dịch vụ sàn (5%) và lịch sử chuyển khoản về ngân hàng.</p>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-bottom:20px">
                <div class="panel" style="margin:0;padding:18px 20px">
                    <div class="muted" style="font-size:13px;margin-bottom:6px">Tổng doanh thu gộp</div>
                    <div style="font-size:24px;font-weight:800"><?= money($summary['gross_total']) ?></div>
                </div>
                <div class="panel" style="margin:0;padding:18px 20px">
                    <div class="muted" style="font-size:13px;margin-bottom:6px">Phí nền tảng (5%)</div>
                    <div style="font-size:24px;font-weight:800;color:#94a3b8"><?= money($summary['fee_total']) ?></div>
                </div>
                <div class="panel" style="margin:0;padding:18px 20px;border-color:rgba(139,92,246,0.45);background:linear-gradient(145deg,rgba(139,92,246,0.12),rgba(18,22,38,0.9))">
                    <div class="muted" style="font-size:13px;margin-bottom:6px">Khả dụng chờ rút</div>
                    <div style="font-size:24px;font-weight:800;color:#a78bfa"><?= money($summary['eligible_total']) ?></div>
                </div>
                <div class="panel" style="margin:0;padding:18px 20px">
                    <div class="muted" style="font-size:13px;margin-bottom:6px">Đã quyết toán</div>
                    <div style="font-size:24px;font-weight:800;color:#34d399"><?= money($summary['paid_total']) ?></div>
                </div>
            </div>

            <div class="panel" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:20px">
                <div>
                    <div class="eyebrow" style="margin:0 0 4px">TÀI KHOẢN NHẬN TIỀN MẶC ĐỊNH</div>
                    <?php if (!empty($v['bank_name'])): ?>
                        <strong><?= h($v['bank_name']) ?> · <?= h($v['bank_account']) ?></strong>
                        <div class="muted" style="font-size:13px">Chủ tài khoản: <?= h($v['bank_account_holder'] ?? '') ?></div>
                    <?php else: ?>
                        <span class="muted">Chưa cấu hình tài khoản ngân hàng. <a href="<?= h(url('vendor_profile')) ?>">Cập nhật ngay →</a></span>
                    <?php endif; ?>
                </div>
                <form method="post" action="<?= h(url('vendor_earnings')) ?>" style="margin:0">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="vendor_payout_request">
                    <button class="btn btn-primary" <?= (float)$summary['eligible_total'] <= 0 ? 'disabled' : '' ?>>
                        Yêu cầu rút <?= money($summary['eligible_total']) ?> →
                    </button>
                </form>
            </div>

            <div class="panel">
                <h2>Chi tiết quyết toán theo đơn hàng (<?= count($rows) ?>)</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Giá trị đơn</th>
                                <th>Phí sàn (5%)</th>
                                <th>Thực nhận</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><strong><?= h($r['order_code']) ?></strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($r['order_date'])) ?></td>
                                    <td><?= money($r['gross_amount']) ?></td>
                                    <td class="muted">-<?= money($r['platform_fee']) ?></td>
                                    <td><strong><?= money($r['net_amount']) ?></strong></td>
                                    <td>
                                        <span class="status-pill">
                                            <?= h(['eligible' => 'Khả dụng', 'paid' => 'Đã chuyển khoản', 'pending' => 'Chờ xử lý'][$r['status']] ?? $r['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!$rows): ?>
                    <p class="muted">Chưa có dữ liệu doanh thu. Các đơn hàng chuyển sang trạng thái Hoàn tất (completed) sẽ tự động ghi nhận tại đây.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
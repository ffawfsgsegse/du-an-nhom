<?php
defined('APP_BOOTSTRAPPED') || exit;
$admin = need_role('admin');
$pendingOrders = dball(
    "SELECT o.id,o.order_code,o.payment_method,o.total_amount,v.shop_name
     FROM orders o JOIN vendors v ON v.id=o.vendor_id
     LEFT JOIN vendor_order_settlements s ON s.order_id=o.id
     LEFT JOIN vendor_earnings e ON e.order_id=o.id
     WHERE o.order_status='completed' AND o.payment_method IN ('cod','wallet')
       AND s.order_id IS NULL AND (e.status IS NULL OR e.status IN ('held','eligible'))
     ORDER BY o.id DESC LIMIT 100"
);
$payouts = dball(
    "SELECT p.*,v.shop_name FROM vendor_payouts p JOIN vendors v ON v.id=p.vendor_id
     ORDER BY FIELD(p.status,'processing','pending','paid','rejected'),p.id DESC LIMIT 100"
);
$statuses = ['pending'=>'Chờ xử lý','processing'=>'Đang xử lý','paid'=>'Đã chi trả','rejected'=>'Từ chối'];
?>
<section class="container section page-top">
    <a href="<?= h(url('admin_dashboard')) ?>">Về quản trị</a>
    <a href="<?= h(url('admin_wallet_withdrawals')) ?>">Chi trả rút ví khách hàng</a>
    <h1 class="page-title">Đối soát và chi trả cho shop</h1>
    <div class="panel">
        <h2>Đơn chờ đối soát</h2>
        <p>Đối chiếu khoản COD đã về hoặc nguồn tiền thật của giao dịch ví trước khi xác nhận.</p>
        <?php if (!$pendingOrders): ?><p>Chưa có đơn chờ đối soát.</p><?php endif; ?>
        <?php foreach ($pendingOrders as $order): ?>
            <form class="stack" method="post" action="<?= h(url('admin_payouts')) ?>">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="admin_payout_settle">
                <input type="hidden" name="id" value="<?= h($order['id']) ?>">
                <p><strong><?= h($order['order_code']) ?></strong> · <?= h($order['shop_name']) ?>
                    · <?= $order['payment_method'] === 'cod' ? 'COD' : 'Ví' ?>
                    · <?= h(money($order['total_amount'])) ?></p>
                <label>Mã đối soát <input name="bank_reference" required maxlength="120"></label>
                <button class="btn btn-outline">Xác nhận đã thu tiền</button>
            </form>
        <?php endforeach; ?>
    </div>

    <div class="panel">
        <h2>Yêu cầu rút tiền</h2>
        <?php if (!$payouts): ?><p>Chưa có yêu cầu.</p><?php endif; ?>
        <?php foreach ($payouts as $row):
            $assigned = $row['status'] === 'processing' && (int)$row['handled_by'] === (int)$admin['id'];
        ?>
            <article class="stack">
                <p><strong>#<?= h($row['id']) ?> · <?= h($row['shop_name']) ?></strong> · <?= h($statuses[$row['status']]) ?></p>
                <p>Yêu cầu <?= h(money($row['amount'])) ?> · Phí <?= h(money($row['fee'])) ?>
                    · Chuyển <?= h(money($row['transfer_amount'])) ?></p>
                <?php if ($row['status'] === 'pending'): ?>
                    <form method="post" action="<?= h(url('admin_payouts')) ?>">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= h($row['id']) ?>">
                        <button class="btn btn-outline" name="action" value="admin_payout_claim">Nhận xử lý</button>
                    </form>
                <?php endif; ?>
                <?php if ($assigned): ?>
                    <p><?= h($row['bank_name']) ?> · <?= h($row['bank_account']) ?> · <?= h($row['bank_holder']) ?></p>
                    <form class="stack" method="post" action="<?= h(url('admin_payouts')) ?>">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= h($row['id']) ?>">
                        <label>Mã giao dịch chuyển khoản <input name="bank_reference" required maxlength="120"></label>
                        <button class="btn btn-primary" name="action" value="admin_payout_paid">Xác nhận đã chuyển tiền</button>
                    </form>
                <?php endif; ?>
                <?php if ($row['status'] === 'pending' || $assigned): ?>
                    <form class="stack" method="post" action="<?= h(url('admin_payouts')) ?>">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= h($row['id']) ?>">
                        <label>Lý do từ chối <input name="reason" required maxlength="255"></label>
                        <button class="btn btn-outline" name="action" value="admin_payout_reject">Từ chối và trả doanh thu khả dụng</button>
                    </form>
                <?php endif; ?>
                <?php if ($row['bank_reference'] !== null): ?><p>Mã đã chuyển <?= h($row['bank_reference']) ?></p><?php endif; ?>
                <?php if ($row['rejection_reason'] !== null): ?><p><?= h($row['rejection_reason']) ?></p><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

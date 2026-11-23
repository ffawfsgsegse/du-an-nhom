<?php
defined('APP_BOOTSTRAPPED') || exit;

(static function (string $page): void {
    $buyer = $page === 'my_orders';
    $actor = need_login();
    if ($buyer) {
        $filter = "o.user_id=? AND (r.id IS NOT NULL OR
                    (o.order_status IN ('printing','shipping') AND o.payment_method IN ('cod','wallet')))";
        $params = [$actor['id']];
    } elseif ($page === 'vendor_orders') {
        $shop = vendor_of_user();
        if (!$shop || $shop['status'] !== 'approved') {
            return;
        }
        $filter = 'o.vendor_id=? AND r.id IS NOT NULL';
        $params = [$shop['id']];
    } else {
        $filter = 'r.id IS NOT NULL';
        $params = [];
    }

    $rows = dball(
        "SELECT o.id AS order_id,o.user_id,o.order_code,o.order_status,o.payment_method,o.total_amount,
                r.id AS request_id,r.status AS request_status,r.reason,r.review_note
         FROM orders o
         LEFT JOIN order_cancel_requests r ON r.id=(
             SELECT MAX(latest.id) FROM order_cancel_requests latest WHERE latest.order_id=o.id
         )
         WHERE $filter ORDER BY COALESCE(r.status='pending',0) DESC,o.id DESC",
        $params
    );
    if (!$rows) {
        return;
    }
    $labels = ['pending'=>'Chờ duyệt hủy','approved'=>'Đã duyệt hủy','rejected'=>'Đã từ chối hủy'];
    ?>
    <section class="container section" id="order-cancellations">
        <div class="panel stack">
            <h2>Yêu cầu hủy đơn</h2>
            <?php foreach ($rows as $row):
                $canCancel = order_can_request_cancellation($row);
                $pending = $row['request_status'] === 'pending';
                $canReview = !$buyer && $pending
                    && ($actor['role'] === 'admin' || (int)$row['user_id'] !== (int)$actor['id']);
            ?>
                <article class="stack">
                    <p><strong><?= h($row['order_code']) ?></strong>
                        · <?= h(order_status_label($row['order_status'])) ?>
                        <?php if ($row['request_status']): ?>
                            · <?= h($labels[$row['request_status']]) ?>
                        <?php endif; ?>
                    </p>
                    <?php if ($row['reason']): ?><p>Lý do hủy · <?= h($row['reason']) ?></p><?php endif; ?>
                    <?php if ($row['review_note']): ?><p>Phản hồi · <?= h($row['review_note']) ?></p><?php endif; ?>

                    <?php if ($buyer && $canCancel && $row['payment_method'] === 'wallet'): ?>
                        <p>Hoàn <?= h(money($row['total_amount'])) ?> về ví nếu yêu cầu được duyệt.</p>
                    <?php endif; ?>
                    <?php if ($buyer && $canCancel && !$pending): ?>
                        <form method="post" action="<?= h(url($page)) ?>#order-cancellations" class="stack">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="order_id" value="<?= h($row['order_id']) ?>">
                            <input type="hidden" name="request_token" value="<?= h(bin2hex(random_bytes(16))) ?>">
                            <label>Lý do muốn hủy <textarea name="cancel_reason" required maxlength="255" rows="2"></textarea></label>
                            <button class="btn btn-outline" name="action" value="order_cancel_request">Gửi yêu cầu hủy</button>
                        </form>
                    <?php elseif ($canReview): ?>
                        <?php if ($canCancel): ?>
                            <form method="post" action="<?= h(url($page)) ?>#order-cancellations" class="stack">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="order_id" value="<?= h($row['order_id']) ?>">
                                <input type="hidden" name="request_id" value="<?= h($row['request_id']) ?>">
                                <?php if ($row['order_status'] === 'shipping'): ?>
                                    <label>
                                        <input type="checkbox" name="shipment_stopped" value="1" required>
                                        Đã dừng giao hoặc nhận lại kiện hàng<?= $row['payment_method'] === 'cod' ? ' và chưa thu tiền COD' : '' ?>.
                                    </label>
                                <?php endif; ?>
                                <button class="btn btn-primary" name="action" value="order_cancel_approve">
                                    <?= $row['payment_method'] === 'wallet'
                                        ? 'Duyệt hủy và hoàn ' . h(money($row['total_amount'])) . ' về ví'
                                        : 'Duyệt hủy đơn COD' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= h(url($page)) ?>#order-cancellations" class="stack">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="order_id" value="<?= h($row['order_id']) ?>">
                            <input type="hidden" name="request_id" value="<?= h($row['request_id']) ?>">
                            <label>Lý do từ chối <input name="reject_reason" maxlength="255" required></label>
                            <button class="btn btn-outline" name="action" value="order_cancel_reject">Từ chối yêu cầu</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
})($page);

<?php defined('APP_BOOTSTRAPPED') || exit;
$p = public_product((int)($_GET['id'] ?? 0));
if (!$p) {
    http_response_code(404);
} else {
    $reviews = dball('SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.product_id = ? ORDER BY r.id DESC', [$p['id']]);
    $totalReviews = count($reviews);
    $avgRating = $totalReviews ? round(array_sum(array_column($reviews, 'rating')) / $totalReviews, 1) : 0;
}
?>
<section class="container section page-top">
    <?php if (!$p): ?>
        <h2>Không tìm thấy sản phẩm đang bán.</h2>
        <a class="btn btn-outline" href="<?= h(url('catalog')) ?>">Trở về Khám phá</a>
    <?php else: ?>
        <a class="back" href="<?= h(url('catalog')) ?>">← Quay lại tất cả sản phẩm</a>
        <div class="detail">
            <div class="detail-visual">
                <?php if ($p['image_path']): ?><img src="<?= h($p['image_path']) ?>" alt="<?= h($p['name']) ?>"><?php else: ?><span class="shape">⬡</span><?php endif; ?>
            </div>
            <div class="detail-info">
                <div class="eyebrow"><?= h($p['category_name']) ?> · <?= h($p['shop_name']) ?></div>
                <h1><?= h($p['name']) ?></h1>
                <p class="muted">Sản phẩm được cung cấp bởi <?= h($p['shop_name']) ?> · <?= h($p['shop_address']) ?></p>
                <div class="big-price"><?= money($p['price']) ?></div>
                <div class="static-tags">
                    <span>Vật liệu: <?= h($p['material_name']) ?></span>
                    <span><?= h($p['dimensions'] ?: 'Kích thước liên hệ shop') ?></span>
                    <span>Còn <?= h($p['stock']) ?> sản phẩm</span>
                    <?php if ($totalReviews): ?><span>★ <?= $avgRating ?>/5 (<?= $totalReviews ?> đánh giá)</span><?php endif; ?>
                </div>
                <?php if ((int)$p['stock'] > 0): ?>
                    <form method="post" action="<?= h(url('product', ['id' => $p['id']])) ?>" class="stack buy-form">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="cart_add">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <label>Số lượng<input type="number" name="quantity" min="1" max="<?= $p['stock'] ?>" value="1" required></label>
                        <button class="btn btn-primary btn-lg">🛒 Thêm vào giỏ hàng</button>
                    </form>
                <?php else: ?>
                    <p class="notice notice-info">Sản phẩm đã hết hàng.</p>
                <?php endif; ?>
                <p class="muted">Thanh toán khi nhận hàng (COD) hoặc QR ngân hàng DEMO. Không có giao dịch tiền thật.</p>
            </div>
        </div>

        <div class="section">
            <div class="section-head">
                <div>
                    <div class="eyebrow">CUSTOMER REVIEWS</div>
                    <h2>Đánh giá <em>sản phẩm</em> (<?= $totalReviews ?>)</h2>
                </div>
                <?php if ($totalReviews): ?>
                    <span class="status-pill">★ <?= $avgRating ?> / 5.0</span>
                <?php endif; ?>
            </div>

            <div class="checkout-grid">
                <div class="panel">
                    <h2>Viết đánh giá của bạn</h2>
                    <?php if (user()): ?>
                        <form method="post" action="<?= h(url('product', ['id' => $p['id']])) ?>" class="stack">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="review_add">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <label>Số sao đánh giá
                                <select name="rating">
                                    <option value="5" selected>★★★★★ (5/5 - Tuyệt vời)</option>
                                    <option value="4">★★★★☆ (4/5 - Hài lòng)</option>
                                    <option value="3">★★★☆☆ (3/5 - Bình thường)</option>
                                    <option value="2">★★☆☆☆ (2/5 - Không hài lòng)</option>
                                    <option value="1">★☆☆☆☆ (1/5 - Tệ)</option>
                                </select>
                            </label>
                            <label>Nhận xét chi tiết
                                <textarea name="comment" required minlength="5" maxlength="1000" rows="3" placeholder="Chia sẻ cảm nhận về độ mịn lớp in, vật liệu, đóng gói..."></textarea>
                            </label>
                            <button class="btn btn-primary">Gửi đánh giá →</button>
                        </form>
                    <?php else: ?>
                        <p class="muted">Vui lòng đăng nhập để gửi nhận xét cho sản phẩm này.</p>
                        <a class="btn btn-primary" href="<?= h(url('login', ['redirect' => 'product', 'id' => $p['id']])) ?>">Đăng nhập ngay</a>
                    <?php endif; ?>
                </div>

                <div>
                    <?php if (!$reviews): ?>
                        <div class="panel empty-state">Chưa có đánh giá nào cho sản phẩm này.</div>
                    <?php else: ?>
                        <?php foreach ($reviews as $r): ?>
                            <div class="panel order-panel" style="margin-top:0">
                                <div class="order-head">
                                    <div>
                                        <b><?= h($r['full_name']) ?></b>
                                        <small><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></small>
                                    </div>
                                    <span class="status-pill" style="color:#ffd166"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></span>
                                </div>
                                <p style="margin:14px 0 0;color:#d6def0;line-height:1.6"><?= nl2br(h($r['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>
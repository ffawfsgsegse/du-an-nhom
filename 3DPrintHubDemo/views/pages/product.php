<?php defined('APP_BOOTSTRAPPED') || exit;
$pid = (int)($_GET['id'] ?? 0);
$p = public_product($pid);
$isPreview = false;

if (!$p && $pid > 0 && user()) {
    $raw = dbone(
        'SELECT p.*, c.name AS category_name, m.name AS material_name, v.shop_name, v.address AS shop_address, v.user_id AS vendor_user_id, pi.image_path
         FROM products p
         JOIN categories c ON c.id = p.category_id
         JOIN materials m ON m.id = p.material_id
         JOIN vendors v ON v.id = p.vendor_id
         LEFT JOIN product_images pi ON pi.product_id = p.id
         WHERE p.id = ? AND p.deleted_at IS NULL',
        [$pid]
    );
    $u = user();
    if ($raw && ((int)$raw['vendor_user_id'] === (int)$u['id'] || $u['role'] === 'admin')) {
        $p = $raw;
        $isPreview = true;
    }
}

if (!$p) {
    http_response_code(404);
} else {
    $variants = dball('SELECT * FROM product_variants WHERE product_id = ? ORDER BY price ASC, id ASC', [$p['id']]);
    $reviews = dball('SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.product_id = ? ORDER BY r.id DESC', [$p['id']]);
    $totalReviews = count($reviews);
    $avgRating = $totalReviews ? round(array_sum(array_column($reviews, 'rating')) / $totalReviews, 1) : 0;

    $materialsList = array_values(array_unique(array_column($variants, 'material_name')));
    $colorsList = array_values(array_unique(array_column($variants, 'color')));
    $sizesList = array_values(array_unique(array_column($variants, 'size_label')));
    $defaultVariant = $variants[0] ?? null;
}
?>
<section class="container section page-top">
    <?php if (!$p): ?>
        <h2>Không tìm thấy sản phẩm đang bán.</h2>
        <a class="btn btn-outline" href="<?= h(url('catalog')) ?>">Trở về Khám phá</a>
    <?php else: ?>
        <?php if ($isPreview): ?>
            <div class="notice notice-info" style="margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                <span>👁️ <b>Chế độ xem trước (Preview):</b> Sản phẩm đang ở trạng thái <b><?= h($p['status']) ?></b> — khách hàng chưa nhìn thấy cho đến khi được duyệt.</span>
                <div style="display:flex;gap:8px">
                    <a class="btn btn-outline btn-sm" href="<?= h(url('vendor_product_edit', ['id' => $p['id']])) ?>">✎ Tiếp tục chỉnh sửa</a>
                    <a class="btn btn-ghost btn-sm" href="<?= h(url('vendor_products')) ?>">Danh sách sản phẩm</a>
                </div>
            </div>
        <?php endif; ?>

        <a class="back" href="<?= h(url($isPreview ? 'vendor_products' : 'catalog')) ?>">← <?= $isPreview ? 'Quay lại quản lý sản phẩm' : 'Quay lại tất cả sản phẩm' ?></a>
        <div class="detail">
            <div class="detail-visual">
                <?php if ($p['image_path']): ?><img src="<?= h($p['image_path']) ?>" alt="<?= h($p['name']) ?>"><?php else: ?><span class="shape">⬡</span><?php endif; ?>
            </div>
            <div class="detail-info">
                <div class="eyebrow"><?= h($p['category_name']) ?> · <?= h($p['shop_name']) ?></div>
                <h1><?= h($p['name']) ?></h1>
                <p class="muted">Sản phẩm được cung cấp bởi <?= h($p['shop_name']) ?> · <?= h($p['shop_address']) ?></p>

                <div class="big-price" id="variantPriceDisplay"><?= money($defaultVariant ? $defaultVariant['price'] : $p['price']) ?></div>

                <div class="static-tags">
                    <span>Vật liệu gốc: <?= h($p['material_name']) ?></span>
                    <span><?= h($p['dimensions'] ?: 'Kích thước tiêu chuẩn') ?></span>
                    <span>Đóng gói: <?= (int)($p['weight_gram'] ?? 200) ?>g (<?= (float)($p['pkg_length'] ?? 15) ?>×<?= (float)($p['pkg_width'] ?? 15) ?>×<?= (float)($p['pkg_height'] ?? 10) ?> cm)</span>
                    <span id="variantStockBadge">Còn <?= h((string)($defaultVariant ? $defaultVariant['stock'] : $p['stock'])) ?> sản phẩm</span>
                    <?php if ($totalReviews): ?><span>★ <?= $avgRating ?>/5 (<?= $totalReviews ?> đánh giá)</span><?php endif; ?>
                </div>

                <?php if ($variants): ?>
                    <div class="stack" style="margin:18px 0;padding:16px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)">
                        <div>
                            <small class="muted" style="display:block;margin-bottom:6px;font-weight:600">1. Chọn vật liệu in:</small>
                            <div style="display:flex;flex-wrap:wrap;gap:8px" id="attrMaterialGroup">
                                <?php foreach ($materialsList as $mName): ?>
                                    <button type="button" class="btn btn-sm attr-pill <?= $mName === $defaultVariant['material_name'] ? 'btn-primary' : 'btn-outline' ?>" data-attr="material_name" data-val="<?= h($mName) ?>">
                                        <?= h($mName) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <small class="muted" style="display:block;margin-bottom:6px;font-weight:600">2. Chọn màu sắc:</small>
                            <div style="display:flex;flex-wrap:wrap;gap:8px" id="attrColorGroup">
                                <?php foreach ($colorsList as $cName): ?>
                                    <button type="button" class="btn btn-sm attr-pill <?= $cName === $defaultVariant['color'] ? 'btn-primary' : 'btn-outline' ?>" data-attr="color" data-val="<?= h($cName) ?>">
                                        <?= h($cName) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <small class="muted" style="display:block;margin-bottom:6px;font-weight:600">3. Chọn kích thước / độ phân giải:</small>
                            <div style="display:flex;flex-wrap:wrap;gap:8px" id="attrSizeGroup">
                                <?php foreach ($sizesList as $sName): ?>
                                    <button type="button" class="btn btn-sm attr-pill <?= $sName === $defaultVariant['size_label'] ? 'btn-primary' : 'btn-outline' ?>" data-attr="size_label" data-val="<?= h($sName) ?>">
                                        <?= h($sName) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div id="variantSelectionNote" class="muted" style="font-size:0.9rem">
                            Đang chọn: <b><?= h($defaultVariant['material_name'] . ' · ' . $defaultVariant['color'] . ' · ' . $defaultVariant['size_label']) ?></b>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($isPreview): ?>
                    <div class="stack buy-form">
                        <label>Số lượng
                            <input type="number" id="buyQtyInput" min="1" max="<?= max(1, (int)($defaultVariant ? $defaultVariant['stock'] : $p['stock'])) ?>" value="1" disabled>
                        </label>
                        <button type="button" class="btn btn-outline btn-lg" id="addToCartBtn" disabled>Chế độ xem trước (Chưa mở bán)</button>
                    </div>
                <?php else: ?>
                    <form method="post" action="<?= h(url('product', ['id' => $p['id']])) ?>" class="stack buy-form" id="addToCartForm">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="cart_add">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="variant_id" id="selectedVariantId" value="<?= $defaultVariant ? (int)$defaultVariant['id'] : 0 ?>">
                        <label>Số lượng
                            <input type="number" name="quantity" id="buyQtyInput" min="1" max="<?= max(1, (int)($defaultVariant ? $defaultVariant['stock'] : $p['stock'])) ?>" value="1" required>
                        </label>
                        <button class="btn btn-primary btn-lg" id="addToCartBtn" <?= ((int)($defaultVariant ? $defaultVariant['stock'] : $p['stock']) < 1) ? 'disabled' : '' ?>>
                            <?= ((int)($defaultVariant ? $defaultVariant['stock'] : $p['stock']) < 1) ? 'Phân loại này đã hết hàng' : '🛒 Thêm vào giỏ hàng' ?>
                        </button>
                    </form>
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

<?php if (!empty($variants)): ?>
    <script>
        (function() {
            const isPreview = <?= $isPreview ? 'true' : 'false' ?>;
            const variants = <?= json_encode($variants, JSON_UNESCAPED_UNICODE) ?>;
            const state = {
                material_name: variants[0].material_name,
                color: variants[0].color,
                size_label: variants[0].size_label
            };

            const priceEl = document.getElementById('variantPriceDisplay');
            const stockBadge = document.getElementById('variantStockBadge');
            const noteEl = document.getElementById('variantSelectionNote');
            const variantInput = document.getElementById('selectedVariantId');
            const qtyInput = document.getElementById('buyQtyInput');
            const submitBtn = document.getElementById('addToCartBtn');

            function formatVND(num) {
                return Number(num).toLocaleString('vi-VN') + 'đ';
            }

            function updateUI() {
                document.querySelectorAll('.attr-pill').forEach(btn => {
                    const attr = btn.dataset.attr;
                    const val = btn.dataset.val;
                    if (state[attr] === val) {
                        btn.classList.remove('btn-outline');
                        btn.classList.add('btn-primary');
                    } else {
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline');
                    }
                });

                const matched = variants.find(v =>
                    v.material_name === state.material_name &&
                    v.color === state.color &&
                    v.size_label === state.size_label
                );

                if (!matched) {
                    priceEl.textContent = 'Không có sẵn tổ hợp này';
                    stockBadge.textContent = 'Hết hàng';
                    noteEl.innerHTML = `<span style="color:#ff7b72">Tổ hợp <b>${state.material_name} · ${state.color} · ${state.size_label}</b> hiện không có sẵn. Hãy chọn màu/kích thước khác.</span>`;
                    if (variantInput) variantInput.value = '0';
                    if (!isPreview && submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Không khả dụng';
                    }
                    return;
                }

                priceEl.textContent = formatVND(matched.price);
                stockBadge.textContent = `Còn ${matched.stock} sản phẩm`;
                noteEl.innerHTML = `Đang chọn: <b>${matched.material_name} · ${matched.color} · ${matched.size_label}</b>`;
                if (variantInput) variantInput.value = matched.id;

                if (!isPreview && submitBtn) {
                    if (Number(matched.stock) > 0) {
                        qtyInput.max = matched.stock;
                        if (Number(qtyInput.value) > Number(matched.stock)) qtyInput.value = matched.stock;
                        submitBtn.disabled = false;
                        submitBtn.textContent = '🛒 Thêm vào giỏ hàng';
                    } else {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Phân loại này đã hết hàng';
                    }
                }
            }

            document.querySelectorAll('.attr-pill').forEach(btn => {
                btn.addEventListener('click', () => {
                    const attr = btn.dataset.attr;
                    state[attr] = btn.dataset.val;

                    const exact = variants.find(v => v.material_name === state.material_name && v.color === state.color && v.size_label === state.size_label);
                    if (!exact && attr === 'material_name') {
                        const fallback = variants.find(v => v.material_name === state.material_name);
                        if (fallback) {
                            state.color = fallback.color;
                            state.size_label = fallback.size_label;
                        }
                    }
                    updateUI();
                });
            });
        })();
    </script>
<?php endif; ?>
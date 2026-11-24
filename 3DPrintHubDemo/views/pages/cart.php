<?php defined('APP_BOOTSTRAPPED') || exit;
$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;

foreach ($cart as $key => $quantity) {
    $keyStr = (string)$key;
    $qty = (int)$quantity;
    if (str_contains($keyStr, '_')) {
        [$pid, $vid] = array_map('intval', explode('_', $keyStr, 2));
        $p = public_product($pid);
        $vr = dbone('SELECT * FROM product_variants WHERE id=? AND product_id=?', [$vid, $pid]);
        if ($p && $vr) {
            $p['cart_key'] = $keyStr;
            $p['variant_label'] = $vr['material_name'] . ' · ' . $vr['color'] . ' · ' . $vr['size_label'];
            $p['price'] = $vr['price'];
            $p['stock'] = $vr['stock'];
            $p['qty'] = $qty;
            $items[] = $p;
            $total += (float)$vr['price'] * $qty;
        }
    } else {
        $p = public_product((int)$keyStr);
        if ($p) {
            $p['cart_key'] = $keyStr;
            $p['variant_label'] = '';
            $p['qty'] = $qty;
            $items[] = $p;
            $total += (float)$p['price'] * $qty;
        }
    }
}
?>
<section class="container section page-top">
    <div class="eyebrow">CHECKOUT FLOW</div>
    <h1 class="page-title">Giỏ hàng <em>của bạn</em></h1>
    <?php if (!$items): ?>
        <div class="panel empty-state">Giỏ hàng đang trống. <a href="<?= h(url('catalog')) ?>">Khám phá sản phẩm</a></div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="panel">
                <h2>Danh sách sản phẩm</h2>
                <?php foreach ($items as $p): ?>
                    <div class="cart-item">
                        <div class="thumb">
                            <?php if ($p['image_path']): ?><img src="<?= h($p['image_path']) ?>" alt=""><?php else: ?>⬡<?php endif; ?>
                        </div>
                        <div class="cart-item-name">
                            <b><?= h($p['name']) ?></b>
                            <?php if ($p['variant_label']): ?>
                                <small style="color:#7ce7ac">Phân loại: <?= h($p['variant_label']) ?></small>
                            <?php endif; ?>
                            <small><?= h($p['shop_name']) ?></small>
                            <span><?= money($p['price']) ?></span>
                        </div>
                        <form method="post" action="<?= h(url('cart')) ?>" class="cart-qty">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="cart_update">
                            <input type="hidden" name="cart_key" value="<?= h($p['cart_key']) ?>">
                            <input type="number" name="quantity" min="0" max="<?= $p['stock'] ?>" value="<?= $p['qty'] ?>">
                            <button class="btn btn-ghost btn-sm">Lưu</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="panel cart-summary">
                <h2>Tổng thanh toán</h2>
                <div class="summary-total"><span>Thành tiền</span><strong><?= money($total) ?></strong></div>
                <p class="muted">Nếu có sản phẩm từ nhiều shop, hệ thống tách thành đơn riêng cho từng shop.</p>
                <a class="btn btn-primary btn-lg" href="<?= h(url('checkout')) ?>">Tiến hành đặt hàng →</a>
                <form method="post" action="<?= h(url('cart')) ?>">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="cart_clear">
                    <button class="btn btn-ghost btn-sm">Xóa giỏ hàng</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>
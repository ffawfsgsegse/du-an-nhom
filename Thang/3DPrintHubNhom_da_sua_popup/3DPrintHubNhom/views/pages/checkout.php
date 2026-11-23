<?php
declare(strict_types=1);

defined('APP_BOOTSTRAPPED') || exit;

$user = need_login();
$quote = $_SESSION['checkout_quote'] ?? null;
if (!$quote || $quote['user_id'] !== (int)$user['id'] || time() - $quote['created_at'] > 600) {
    $quote = null;
}
$receiverName = is_string($_POST['receiver_name'] ?? null) ? trim($_POST['receiver_name']) : ($quote['name'] ?? $user['full_name']);
$receiverPhone = is_string($_POST['receiver_phone'] ?? null) ? trim($_POST['receiver_phone']) : ($quote['phone'] ?? ($user['phone'] ?? ''));
$address = is_string($_POST['address'] ?? null) ? trim($_POST['address']) : ($quote['address'] ?? '');
$method = ($_POST['payment_option'] ?? ($quote['method'] ?? 'cod')) === 'wallet' ? 'wallet' : 'cod';
if ($quote && ($address !== $quote['address'] || $method !== $quote['method'])) {
    $quote = null;
}
?>

<section class="container section page-top">
    <h1 class="page-title">Thanh toán</h1>
    <?php if (empty($_SESSION['cart'])): ?>
        <p class="notice notice-info">Giỏ hàng đang trống.</p>
        <a class="btn btn-primary" href="<?= h(url('catalog')) ?>">Xem sản phẩm</a>
    <?php else: ?>
        <div class="checkout-grid">
            <div class="panel">
                <h2>Thông tin giao hàng</h2>
                <form method="post" action="<?= h(url('checkout')) ?>" class="stack">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="place_order">
                    <label>
                        Người nhận
                        <input name="receiver_name" autocomplete="name" required maxlength="100" value="<?= h($receiverName) ?>">
                    </label>
                    <label>
                        Số điện thoại
                        <input type="tel" name="receiver_phone" autocomplete="tel" required maxlength="20" value="<?= h($receiverPhone) ?>">
                    </label>
                    <label>
                        Địa chỉ nhận hàng
                        <textarea name="address" autocomplete="street-address" required minlength="10" maxlength="255" rows="3" placeholder="Số nhà, đường, phường/xã, tỉnh/thành"><?= h($address) ?></textarea>
                    </label>

                    <fieldset>
                        <legend>Phương thức thanh toán</legend>
                        <label class="payment-option">
                            <input type="radio" name="payment_option" value="cod" <?= $method === 'cod' ? 'checked' : '' ?>>
                            <span>COD · Thanh toán khi nhận hàng</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_option" value="wallet" <?= $method === 'wallet' ? 'checked' : '' ?>>
                            <span>Ví 3DPrintHub</span>
                        </label>
                        <a href="<?= h(url('topup')) ?>">Nạp tiền vào ví</a>
                    </fieldset>

                    <button type="submit" name="checkout_step" value="quote" class="btn <?= $quote ? 'btn-outline' : 'btn-primary' ?>">
                        <?= $quote ? 'Tính lại phí giao hàng' : 'Tính phí giao hàng' ?>
                    </button>
                    <?php if ($quote): ?>
                        <input type="hidden" name="quote_id" value="<?= h($quote['id']) ?>">
                        <button type="submit" name="checkout_step" value="confirm" class="btn btn-primary btn-lg">
                            Xác nhận đặt hàng · <?= h(money($quote['total'])) ?>
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="panel">
                <h2>Chi tiết đơn hàng</h2>
                <?php if (!$quote): ?>
                    <p>Nhập địa chỉ và chọn phương thức thanh toán để xem cước Viettel Post.</p>
                    <a href="<?= h(url('cart')) ?>">Về giỏ hàng</a>
                <?php else: ?>
                    <div class="stack">
                        <?php foreach ($quote['groups'] as $vendor => $group): $rate = $quote['rates'][$vendor]; ?>
                            <div>
                                <h3><?= h($group['shop_name']) ?></h3>
                                <ul>
                                    <?php foreach ($group['items'] as $item): ?>
                                        <li><?= h($item['name']) ?> × <?= h($item['quantity']) ?>
                                            · <?= h(money($item['price'] * $item['quantity'])) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <p>Tiền hàng <strong><?= h(money($group['subtotal'])) ?></strong></p>
                                <p>Phí Viettel Post <strong><?= h(money($rate['fee'])) ?></strong>
                                    · <?= h($rate['service_name']) ?></p>
                            </div>
                        <?php endforeach; ?>
                        <p><strong><?= $method === 'cod' ? 'Tổng thu khi giao' : 'Tổng trừ ví' ?></strong>
                            <strong><?= h(money($quote['total'])) ?></strong></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

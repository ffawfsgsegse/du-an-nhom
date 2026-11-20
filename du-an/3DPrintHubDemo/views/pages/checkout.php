<?php
declare(strict_types=1);

defined('APP_BOOTSTRAPPED') || exit;
require_once __DIR__ . '/../../app/checkout_delivery.php';

$user = need_login();
$quote = $_SESSION['checkout_quote'] ?? null;
if (!$quote || $quote['user_id'] !== (int)$user['id'] || time() - $quote['created_at'] > 600) {
    $quote = null;
}
$receiverName = is_string($_POST['receiver_name'] ?? null) ? trim($_POST['receiver_name']) : ($quote['name'] ?? $user['full_name']);
$receiverPhone = is_string($_POST['receiver_phone'] ?? null) ? trim($_POST['receiver_phone']) : ($quote['phone'] ?? ($user['phone'] ?? ''));
$delivery = checkout_delivery_fields($_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : ($quote['address_fields'] ?? []));
$address = checkout_delivery_address($delivery);
$note = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['shipping_note'] ?? '') : ($quote['note'] ?? '');
$note = is_string($note) ? $note : '';
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
                        Số nhà và tên đường
                        <input name="shipping_street" autocomplete="address-line1" maxlength="255" required
                               value="<?= h($delivery['shipping_street']) ?>">
                    </label>
                    <label>
                        Phường / Xã
                        <input name="shipping_ward" autocomplete="address-level3" maxlength="100" required
                               value="<?= h($delivery['shipping_ward']) ?>">
                    </label>
                    <label>
                        Quận / Huyện (nếu có)
                        <input name="shipping_district" autocomplete="address-level2" maxlength="100"
                               value="<?= h($delivery['shipping_district']) ?>">
                    </label>
                    <label>
                        Tỉnh / Thành phố
                        <input name="shipping_province" autocomplete="address-level1" maxlength="100" required
                               value="<?= h($delivery['shipping_province']) ?>">
                    </label>
                    <label>
                        Ghi chú giao hàng (không bắt buộc)
                        <textarea name="shipping_note" maxlength="255" rows="2"
                                  placeholder="Gọi trước khi giao"><?= h($note) ?></textarea>
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

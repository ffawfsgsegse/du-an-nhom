<?php
defined('APP_BOOTSTRAPPED') || exit;
$shippingNotesShop = vendor_of_user();
$shippingNotes = $shippingNotesShop && $shippingNotesShop['status'] === 'approved'
    ? dball(
        "SELECT order_code, shipping_note FROM orders
         WHERE vendor_id = ? AND shipping_note IS NOT NULL
           AND order_status IN ('pending', 'printing', 'shipping')
         ORDER BY id DESC",
        [(int)$shippingNotesShop['id']]
    )
    : [];
?>
<?php if ($shippingNotes): ?>
    <section class="container section">
        <div class="panel stack">
            <h2>Ghi chú giao hàng</h2>
            <?php foreach ($shippingNotes as $shippingNote): ?>
                <div>
                    <strong><?= h($shippingNote['order_code']) ?></strong>
                    <p><?= nl2br(h($shippingNote['shipping_note'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

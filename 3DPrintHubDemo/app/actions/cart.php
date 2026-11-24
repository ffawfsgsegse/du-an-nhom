<?php

declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;

function cart_action(string $action): void
{
    if ($action === 'cart_clear') {
        unset($_SESSION['cart']);
        flash('Đã xóa giỏ hàng.');
        go('cart');
    }

    $id = (int)($_POST['product_id'] ?? 0);
    $variantId = (int)($_POST['variant_id'] ?? 0);
    $cartKey = (string)($_POST['cart_key'] ?? ($variantId > 0 ? "{$id}_{$variantId}" : (string)$id));

    if ($id < 1 && !str_contains($cartKey, '_')) {
        throw new RuntimeException('Mã sản phẩm không hợp lệ.');
    }

    if ($action === 'cart_add') {
        $p = public_product($id);
        if (!$p) throw new RuntimeException('Sản phẩm không còn bán.');

        if ($variantId > 0) {
            $vr = dbone('SELECT * FROM product_variants WHERE id=? AND product_id=?', [$variantId, $id]);
            if (!$vr || (int)$vr['stock'] < 1) throw new RuntimeException('Phân loại sản phẩm này đã hết hàng hoặc không tồn tại.');
            $maxStock = (int)$vr['stock'];
            $cartKey = "{$id}_{$variantId}";
        } else {
            // Nếu sản phẩm có biến thể mà chưa truyền variant_id, tự lấy biến thể đầu tiên còn hàng
            $vr = dbone('SELECT * FROM product_variants WHERE product_id=? AND stock>0 ORDER BY price ASC, id ASC LIMIT 1', [$id]);
            if ($vr) {
                $variantId = (int)$vr['id'];
                $maxStock = (int)$vr['stock'];
                $cartKey = "{$id}_{$variantId}";
            } else {
                if ((int)$p['stock'] < 1) throw new RuntimeException('Sản phẩm không còn bán.');
                $maxStock = (int)$p['stock'];
                $cartKey = (string)$id;
            }
        }

        $qty = positive_int($_POST['quantity'] ?? 1, 100);
        $new = (int)($_SESSION['cart'][$cartKey] ?? 0) + $qty;
        if ($new > $maxStock) throw new RuntimeException('Số lượng vượt quá tồn kho của phân loại này.');

        $_SESSION['cart'][$cartKey] = $new;
        flash('Đã thêm phân loại sản phẩm vào giỏ hàng.');
        go('cart');
    }

    if ($action === 'cart_update') {
        $qty = (int)($_POST['quantity'] ?? 0);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$cartKey]);
            flash('Đã bỏ sản phẩm khỏi giỏ.');
            go('cart');
        }

        if (str_contains($cartKey, '_')) {
            [$pid, $vid] = array_map('intval', explode('_', $cartKey, 2));
            $p = public_product($pid);
            $vr = dbone('SELECT * FROM product_variants WHERE id=? AND product_id=?', [$vid, $pid]);
            if (!$p || !$vr || $qty > (int)$vr['stock'] || $qty > 100) {
                throw new RuntimeException('Số lượng vượt tồn kho hoặc phân loại đã ngừng bán.');
            }
        } else {
            $pid = (int)$cartKey;
            $p = public_product($pid);
            if (!$p || $qty > (int)$p['stock'] || $qty > 100) {
                throw new RuntimeException('Số lượng vượt tồn kho hoặc sản phẩm đã ngừng bán.');
            }
        }

        $_SESSION['cart'][$cartKey] = $qty;
        flash('Đã cập nhật giỏ hàng.');
        go('cart');
    }
}

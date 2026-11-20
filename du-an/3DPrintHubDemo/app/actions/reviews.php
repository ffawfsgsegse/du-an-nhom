<?php

declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;

function review_action(string $action): void
{
    if ($action !== 'review_add') {
        throw new RuntimeException('Hành động không hợp lệ.');
    }

    $u = need_login();
    $pid = positive_int($_POST['product_id'] ?? 0);
    $rating = positive_int($_POST['rating'] ?? 5, 5);
    $comment = trim((string)($_POST['comment'] ?? ''));

    if (!public_product($pid)) {
        throw new RuntimeException('Sản phẩm không tồn tại.');
    }
    if (mb_strlen($comment) < 5 || mb_strlen($comment) > 1000) {
        throw new RuntimeException('Nội dung đánh giá cần từ 5 đến 1000 ký tự.');
    }

    $existing = dbone('SELECT id FROM reviews WHERE product_id = ? AND user_id = ?', [$pid, $u['id']]);
    if ($existing) {
        execsql('UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?', [$rating, $comment, $existing['id']]);
        flash('Đã cập nhật đánh giá của bạn.');
    } else {
        execsql('INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)', [$u['id'], $pid, $rating, $comment]);
        flash('Cảm ơn bạn đã gửi đánh giá!');
    }

    go('product', ['id' => $pid]);
}

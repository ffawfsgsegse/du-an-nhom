<?php
/**
 * ĐIỂM VÀO RIÊNG CỦA ADMIN - 3DPrintHub.
 * URL: /3DPrintHubDemo/admin.php (đăng nhập bằng tài khoản admin).
 * Muốn thêm một mục: sửa mảng $sections và menu ở views/admin/layout_start.php.
 * Mọi phân quyền / POST / dữ liệu vẫn do index.php và app/ xử lý để tránh lặp mã.
 */
declare(strict_types=1);
$sections = [
    'dashboard'  => 'admin_dashboard',
    'products'   => 'admin_products',
    'product_edit' => 'admin_product_edit',
    'categories' => 'admin_categories',
    'vendors'    => 'admin_vendors',
    'users'      => 'admin_users',
    'orders'     => 'admin_orders',
    'analytics'  => 'admin_analytics',
    'payouts'    => 'admin_payouts',
    'wallet_withdrawals' => 'admin_wallet_withdrawals',
];
$section = (string)($_GET['section'] ?? 'dashboard');
if (!isset($sections[$section])) {
    http_response_code(404);
    exit('Không tìm thấy trang quản trị.');
}
$_GET['page'] = $sections[$section];
require __DIR__ . '/index.php';

<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;
/** Icons nét mảnh SVG nội tuyến; chỉ nhận tên trong danh sách an toàn. */
function admin_icon(string $name): string {
 $paths = [
  'layout-dashboard'=>'<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
  'package'=>'<path d="m12 2 9 5-9 5-9-5 9-5ZM3 7v10l9 5 9-5V7M12 12v10"/>',
  'layers'=>'<path d="m12 2 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 17l9 5 9-5"/>',
  'store'=>'<path d="M4 10h16v11H4zM2 10l2-7h16l2 7M9 21v-8h6v8M2 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/>',
  'users'=>'<circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 5v1"/>',
  'shopping-bag'=>'<path d="M5 8h14l1 13H4L5 8ZM9 9V6a3 3 0 0 1 6 0v3"/>',
  'chart-no-axes-combined'=>'<path d="M3 3v18h18M7 17l5-6 4 3 5-8M17 6h4v4"/>',
  'wallet'=>'<rect x="3" y="5" width="18" height="15" rx="2"/><path d="M16 12h5M3 9h18"/>',
  'credit-card'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>',
  'external-link'=>'<path d="M12 5H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7M13 3h8v8M21 3l-11 11"/>',
  'search'=>'<circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/>',
  'bell'=>'<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
  'log-out'=>'<path d="M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5M16 17l5-5-5-5M21 12H9"/>',
  'menu'=>'<path d="M4 7h16M4 12h16M4 17h16"/>',
  'arrow-up-right'=>'<path d="M7 17 17 7M8 7h9v9"/>',
  'arrow-right'=>'<path d="M4 12h16m-7-7 7 7-7 7"/>',
  'refresh-cw'=>'<path d="M20 7v5h-5M4 17v-5h5M6 9a7 7 0 0 1 12-2l2 5M4 12l2 5a7 7 0 0 0 12-2"/>',
  'check'=>'<path d="m5 12 4 4L19 6"/>',
  'filter'=>'<path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z"/>',
  'plus'=>'<path d="M12 5v14M5 12h14"/>',
  'alert-circle'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/>',
 ];
 $p = $paths[$name] ?? $paths['layout-dashboard'];
 return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$p.'</svg>';
}
function admin_product_status(string $status): string { return ['pending'=>'Chờ duyệt','active'=>'Đang bán','hidden'=>'Đã ẩn'][$status] ?? $status; }
function admin_vendor_status(string $status): string { return ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'][$status] ?? $status; }
function admin_role_name(string $role): string { return ['client'=>'Khách hàng','vendor'=>'Người bán','admin'=>'Quản trị viên'][$role] ?? $role; }
function admin_payment_name(string $value): string { return ['cod'=>'COD','banking'=>'Chuyển khoản','wallet'=>'Ví điện tử'][$value] ?? $value; }

/** Lấy ký tự đầu UTF-8 mà không bắt buộc cài extension mbstring. */
function admin_initial(string $name): string {
 if (function_exists('mb_substr')) return mb_substr($name, 0, 1, 'UTF-8');
 return preg_match('/^./u', $name, $match) ? $match[0] : 'A';
}

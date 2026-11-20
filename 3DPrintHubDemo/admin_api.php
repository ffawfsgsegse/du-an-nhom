<?php
/** Endpoint đọc dữ liệu của bảng điều khiển, không nhận hành động ghi. */
declare(strict_types=1);
define('APP_BOOTSTRAPPED',true);
require __DIR__.'/config.php';
require __DIR__.'/app/helpers.php';
require __DIR__.'/app/authorization.php';
require __DIR__.'/app/repositories/analytics.php';
require __DIR__.'/app/repositories/sales_insights.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
if(!user() || user()['role']!=='admin'){
 http_response_code(403);
 echo json_encode(['error'=>'Không có quyền quản trị'],JSON_UNESCAPED_UNICODE);
 exit;
}
if($_SERVER['REQUEST_METHOD']!=='GET'){
 http_response_code(405);header('Allow: GET');
 echo json_encode(['error'=>'Chỉ cho phép GET'],JSON_UNESCAPED_UNICODE);exit;
}
try {
 $data = [
  'stats'=>admin_overview(),
  'sales_insights'=>admin_sales_insights(),
  'series'=>revenue_series('month'),
  'order_statuses'=>dball('SELECT order_status,COUNT(*) n FROM orders GROUP BY order_status'),
  'recent_orders'=>dball('SELECT o.order_code,o.total_amount,o.order_status,o.created_at,v.shop_name FROM orders o JOIN vendors v ON v.id=o.vendor_id ORDER BY o.id DESC LIMIT 7'),
 ];
 echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR);
} catch(Throwable $e) {
 error_log('Admin dashboard API: '.$e->getMessage());
 http_response_code(500);echo json_encode(['error'=>'Không tải được dữ liệu thống kê'],JSON_UNESCAPED_UNICODE);
}

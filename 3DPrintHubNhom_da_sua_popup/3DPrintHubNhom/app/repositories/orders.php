<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED')||exit;
function customer_orders(int $uid): array {return dball('SELECT o.*,v.shop_name FROM orders o JOIN vendors v ON v.id=o.vendor_id WHERE o.user_id=? ORDER BY o.id DESC',[$uid]);}
function vendor_orders(int $vendor): array {return dball('SELECT o.*,u.full_name customer_name FROM orders o JOIN users u ON u.id=o.user_id WHERE o.vendor_id=? ORDER BY o.id DESC',[$vendor]);}
function order_items(int $order): array {return dball('SELECT oi.*,p.name product_name FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=? ORDER BY oi.id',[$order]);}

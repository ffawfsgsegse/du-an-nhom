<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED')||exit;
function admin_overview(): array {
 return ['users'=>(int)dbone('SELECT COUNT(*) n FROM users')['n'],'shops'=>(int)dbone('SELECT COUNT(*) n FROM vendors')['n'],'products'=>(int)dbone('SELECT COUNT(*) n FROM products WHERE deleted_at IS NULL')['n'],'orders'=>(int)dbone('SELECT COUNT(*) n FROM orders')['n'],'revenue'=>(float)dbone('SELECT COALESCE(SUM(total_amount),0) n FROM orders WHERE order_status="completed"')['n'],'pending_shops'=>(int)dbone('SELECT COUNT(*) n FROM vendors WHERE status="pending"')['n'],'pending_products'=>(int)dbone('SELECT COUNT(*) n FROM products WHERE status="pending" AND deleted_at IS NULL')['n']];
}
function revenue_series(string $period): array {
 $map=['day'=>['DATE(COALESCE(completed_at,created_at))',14], 'week'=>['YEARWEEK(COALESCE(completed_at,created_at),3)',12], 'month'=>['DATE_FORMAT(COALESCE(completed_at,created_at),"%Y-%m")',12], 'quarter'=>['CONCAT(YEAR(COALESCE(completed_at,created_at)),"-Q",QUARTER(COALESCE(completed_at,created_at)))',8], 'year'=>['YEAR(COALESCE(completed_at,created_at))',5]];
 [$group,$limit]=$map[$period]??$map['month'];
 return array_reverse(dball('SELECT '.$group.' label, COALESCE(SUM(total_amount),0) revenue, COUNT(*) orders_count FROM orders WHERE order_status="completed" GROUP BY '.$group.' ORDER BY MIN(COALESCE(completed_at,created_at)) DESC LIMIT '.$limit));
}
function rank_shops(string $sort='revenue',bool $lowest=false): array {
 $column=['revenue'=>'revenue','orders'=>'orders_count','quantity'=>'qty'][$sort]??'revenue';$dir=$lowest?'ASC':'DESC';
 return dball('SELECT v.shop_name, COUNT(o.id) orders_count, COALESCE(SUM(o.total_amount),0) revenue, COALESCE(SUM(x.qty),0) qty FROM vendors v LEFT JOIN orders o ON o.vendor_id=v.id AND o.order_status="completed" LEFT JOIN (SELECT order_id,SUM(quantity) qty FROM order_items WHERE product_id IS NOT NULL GROUP BY order_id) x ON x.order_id=o.id WHERE v.status="approved" GROUP BY v.id,v.shop_name ORDER BY '.$column.' '.$dir.',v.shop_name LIMIT 8');
}
function rank_customers(): array {return dball('SELECT u.full_name,u.email,COUNT(o.id) orders_count,COALESCE(SUM(o.total_amount),0) revenue FROM users u JOIN orders o ON o.user_id=u.id AND o.order_status="completed" GROUP BY u.id,u.full_name,u.email ORDER BY revenue DESC,orders_count DESC LIMIT 8');}

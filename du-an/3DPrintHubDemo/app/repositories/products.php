<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;
function categories(bool $active=true): array { return dball('SELECT * FROM categories'.($active?' WHERE status=1':'').' ORDER BY id ASC'); }
function public_products(int $cat=0,string $keyword='',int $limit=100): array {
 $where='p.status="active" AND p.deleted_at IS NULL AND v.status="approved" AND u.status=1 AND c.status=1';$args=[];
 if($cat>0){$where.=' AND p.category_id=?';$args[]=$cat;}
 if($keyword!==''){$where.=' AND (p.name LIKE ? OR v.shop_name LIKE ?)';$args[]='%'.$keyword.'%';$args[]='%'.$keyword.'%';}
 return dball('SELECT p.*,c.name category_name,v.shop_name,m.name material_name,pi.image_path FROM products p JOIN categories c ON c.id=p.category_id JOIN vendors v ON v.id=p.vendor_id JOIN users u ON u.id=v.user_id JOIN materials m ON m.id=p.material_id LEFT JOIN product_images pi ON pi.product_id=p.id WHERE '.$where.' ORDER BY p.id DESC LIMIT '.$limit,$args);
}
function public_product(int $id): ?array {
 return dbone('SELECT p.*,c.name category_name,v.shop_name,v.address shop_address,m.name material_name,pi.image_path FROM products p JOIN categories c ON c.id=p.category_id JOIN vendors v ON v.id=p.vendor_id JOIN users u ON u.id=v.user_id JOIN materials m ON m.id=p.material_id LEFT JOIN product_images pi ON pi.product_id=p.id WHERE p.id=? AND p.status="active" AND p.deleted_at IS NULL AND v.status="approved" AND u.status=1',[$id]);
}
function vendor_products(int $vendor): array {return dball('SELECT p.*,c.name category_name,m.name material_name,pi.image_path FROM products p JOIN categories c ON c.id=p.category_id JOIN materials m ON m.id=p.material_id LEFT JOIN product_images pi ON pi.product_id=p.id WHERE p.vendor_id=? AND p.deleted_at IS NULL ORDER BY p.id DESC',[$vendor]);}
function all_products_admin(): array {return dball('SELECT p.*,v.shop_name,c.name category_name,m.name material_name,pi.image_path FROM products p JOIN vendors v ON v.id=p.vendor_id JOIN categories c ON c.id=p.category_id JOIN materials m ON m.id=p.material_id LEFT JOIN product_images pi ON pi.product_id=p.id WHERE p.deleted_at IS NULL ORDER BY FIELD(p.status,"pending","active","hidden"),p.id DESC LIMIT 250');}
function all_vendor_materials(): array {return dball('SELECT m.*,v.shop_name FROM materials m JOIN vendors v ON v.id=m.vendor_id WHERE m.status=1 AND v.status="approved" ORDER BY v.id,m.name');}

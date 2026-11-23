<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED')||exit;
function cart_action(string $action): void {
 $id=(int)($_POST['product_id']??0);
 if($action==='cart_clear'){unset($_SESSION['cart']);flash('Đã xóa giỏ hàng.');go('cart');}
 if($id<1)throw new RuntimeException('Mã sản phẩm không hợp lệ.');
 if($action==='cart_add'){
  $p=public_product($id);if(!$p||$p['stock']<1)throw new RuntimeException('Sản phẩm không còn bán.');
  $qty=positive_int($_POST['quantity']??1,100);
  $new=($_SESSION['cart'][$id]??0)+$qty;
  if($new>(int)$p['stock'])throw new RuntimeException('Số lượng vượt tồn kho.');
  $_SESSION['cart'][$id]=$new;flash('Đã thêm vào giỏ hàng.');go('cart');
 }
 if($action==='cart_update'){
  $qty=(int)($_POST['quantity']??0);
  if($qty<=0){unset($_SESSION['cart'][$id]);flash('Đã bỏ sản phẩm khỏi giỏ.');go('cart');}
  $p=public_product($id);if(!$p||$qty>(int)$p['stock']||$qty>100)throw new RuntimeException('Số lượng vượt tồn kho hoặc sản phẩm đã ngừng bán.');
  $_SESSION['cart'][$id]=$qty;flash('Đã cập nhật giỏ hàng.');go('cart');
 }
}

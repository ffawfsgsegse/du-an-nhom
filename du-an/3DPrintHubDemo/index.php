<?php
declare(strict_types=1);
/* Router: chỉ chọn controller xử lý POST và file giao diện. */
define('APP_BOOTSTRAPPED',true);
require __DIR__.'/config.php';
require __DIR__.'/app/helpers.php';
require __DIR__.'/app/authorization.php';
require __DIR__.'/app/repositories/products.php';
require __DIR__.'/app/repositories/orders.php';
require __DIR__.'/app/vendor_payouts.php';
require __DIR__.'/app/wallet_withdrawals.php';
require __DIR__.'/app/repositories/analytics.php';
require __DIR__.'/payments.php'; // Nạp tệp chứa hàm tạo mã QR VietQR / SePay

$routes=[
 'home'=>'pages/home.php','catalog'=>'pages/catalog.php','product'=>'pages/product.php',
 'login'=>'pages/login.php','register'=>'pages/register.php','apply'=>'pages/apply.php',
 'cart'=>'pages/cart.php','checkout'=>'pages/checkout.php','my_orders'=>'pages/orders.php','demo_payment'=>'pages/demo_payment.php',
 'vendor_dashboard'=>'vendor/dashboard.php','vendor_products'=>'vendor/products.php','vendor_product_edit'=>'vendor/product_form.php','vendor_orders'=>'vendor/orders.php',
 'admin_dashboard'=>'admin/dashboard.php','admin_products'=>'admin/products.php','admin_product_edit'=>'admin/product_form.php','admin_categories'=>'admin/categories.php',
 'admin_vendors'=>'admin/vendors.php','admin_users'=>'admin/users.php','admin_orders'=>'admin/orders.php','admin_analytics'=>'admin/analytics.php',
 'topup' => 'pages/topup.php',
 'vendor_payouts'=>'pages/vendor_payouts.php','admin_payouts'=>'pages/admin_payouts.php',
 'wallet_withdrawals'=>'pages/wallet_withdrawals.php','admin_wallet_withdrawals'=>'pages/wallet_withdrawals.php',
];
$page=(string)($_GET['page']??'home');
if(!isset($routes[$page])){$page='not_found';http_response_code(404);}
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  check_csrf();$action=(string)($_POST['action']??'');
  if(in_array($action,['login','register','logout','apply_vendor'],true)){
   require __DIR__.'/app/actions/auth.php';auth_action($action);
  }elseif($action==='wallet_withdraw_request'){
   $buyer=need_role('client','vendor');wallet_withdraw_request((int)$buyer['id']);
  }elseif(str_starts_with($action,'cart_')){
   require __DIR__.'/app/actions/cart.php';cart_action($action);
  }elseif(in_array($action,['place_order','cancel_order','demo_confirm'],true)){
   require __DIR__.'/app/actions/checkout.php';checkout_action($action);
  }elseif(str_starts_with($action,'vendor_product_')||str_starts_with($action,'admin_product_')){
   require __DIR__.'/app/actions/products.php';product_action($action);
  }elseif(in_array($action,['vendor_material_add','vendor_order_status','vendor_payout_request'],true)){
   require __DIR__.'/app/actions/vendor.php';vendor_action($action);
  }elseif(str_starts_with($action,'admin_')){
   require __DIR__.'/app/actions/admin.php';admin_action($action);
  }elseif($action === 'topup' || $page === 'topup'){
   // Cho phép yêu cầu nạp tiền đi qua
  }else throw new RuntimeException('Hành động không tồn tại.');
 }catch(PDOException $e){error_log('3DPrintHub SQL: '.$e->getMessage());$errors[]=$e->getCode()==='23000'?'Dữ liệu đã tồn tại hoặc bị ràng buộc.':'Lỗi cơ sở dữ liệu, kiểm tra cấu trúc bảng và thử lại.';}
 catch(Throwable $e){$errors[]=$e->getMessage();}
}
if(in_array($page,['login','register'],true)&&user())go('home');
if(in_array($page,['cart','checkout','my_orders','demo_payment','wallet_withdrawals'],true))need_login();
if(str_starts_with($page,'vendor_'))need_role('vendor');
if(str_starts_with($page,'admin_'))need_role('admin');
$flash=$_SESSION['flash']??null;unset($_SESSION['flash']);
require __DIR__.'/views/layouts/header.php';
$view=$routes[$page]??'pages/not_found.php';
require __DIR__.'/views/'.$view;
if ($page === 'vendor_orders') {
    require __DIR__.'/views/partials/checkout_shipping_notes.php';
}
require __DIR__.'/views/layouts/footer.php';

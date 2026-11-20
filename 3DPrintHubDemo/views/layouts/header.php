<?php 
defined('APP_BOOTSTRAPPED')||exit; 

// Truy vấn lấy số dư ví của user đang đăng nhập
$walletBalance = 0;
if (function_exists('user') && user()) {
    global $pdo;
    try {
        $stmtWallet = $pdo->prepare('SELECT balance FROM wallet_accounts WHERE user_id = ?');
        $stmtWallet->execute([user()['id']]);
        $walletBalance = (int)($stmtWallet->fetchColumn() ?? 0);
    } catch (Throwable $e) {
        $walletBalance = 0;
    }
}
?>
<!doctype html><html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>3DPrintHub · Sàn sản phẩm in 3D</title>
<meta name="description" content="3DPrintHub - marketplace sản phẩm in 3D, gian hàng và quản trị">
<link rel="stylesheet" href="assets/style.css">
</head><body>
<div class="ambient ambient-one"></div><div class="ambient ambient-two"></div>
<header class="topbar">
 <a class="logo" href="<?=h(url('home'))?>"><span class="logo-icon">⬡</span>3D<span>PRINT</span>HUB</a>
 <nav aria-label="Điều hướng">
     <a href="<?=h(url('home'))?>">Trang chủ</a>
     <a href="<?=h(url('catalog'))?>">Khám phá</a>
     <?php if(user()):?>
     <a href="<?=h(url('topup'))?>">💳 Nạp tiền ví</a>
     <?php if(in_array(user()['role'],['client','vendor'],true)):?><a href="<?=h(url('wallet_withdrawals'))?>">Rút ví</a><?php endif;?>
     <?php endif;?>
     <a href="<?=h(url('apply'))?>">Bán hàng trên 3DPrintHub</a>
 </nav>
 <div class="nav-actions">
  <!-- Hiển thị số dư ví ngay cạnh giỏ hàng khi người dùng đã đăng nhập -->
  <?php if(user()):?>
  <a href="<?=h(url('topup'))?>" style="display: inline-flex; align-items: center; gap: 5px; color: #fff; text-decoration: none; background: rgba(255,255,255,0.08); padding: 5px 10px; border-radius: 16px; font-size: 0.85em;" title="Số dư ví">
      <span>💳</span>
      <span>Ví: <strong style="color: #4ade80;"><?= number_format($walletBalance) ?> đ</strong></span>
  </a>
  <?php endif;?>

  <a class="cart-link" href="<?=h(url('cart'))?>" title="Giỏ hàng">🛒 <span><?=cart_count()?></span></a>
  <?php if(user()):?>
  <?php if(user()['role']==='admin'):?><a class="btn btn-outline btn-sm" href="<?=h(url('admin_dashboard'))?>">Admin</a><a class="btn btn-outline btn-sm" href="<?=h(url('admin_payouts'))?>">Chi trả shop</a><?php endif;?>
  <?php if(user()['role']==='vendor'):?><a class="btn btn-outline btn-sm" href="<?=h(url('vendor_dashboard'))?>">Kênh bán</a><?php endif;?>
  <a class="user-pill" href="<?=h(url('my_orders'))?>"><?=h(user()['full_name'])?></a>
  <form class="inline-form" method="post" action="<?=h(url('home'))?>"><?php csrf_field();?><input type="hidden" name="action" value="logout"><button class="btn btn-ghost btn-sm">Đăng xuất</button></form>
  <?php else:?><a class="btn btn-ghost btn-sm" href="<?=h(url('login'))?>">Đăng nhập</a><a class="btn btn-primary btn-sm" href="<?=h(url('register'))?>">Đăng ký</a><?php endif;?>
 </div>
</header>
<?php if($flash):?><div class="toast <?=($flash['type']??'')==='error'?'toast-error':''?>" id="toast"><?=h($flash['message'])?></div><?php endif;?>
<?php if($errors):?><div class="notice notice-error site-error" role="alert"><?=h(implode(' · ',$errors))?></div><?php endif;?>
<main>

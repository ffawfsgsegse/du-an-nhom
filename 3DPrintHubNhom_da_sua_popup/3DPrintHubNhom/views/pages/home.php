<?php defined('APP_BOOTSTRAPPED')||exit;$featured=public_products(0,'',8);$cats=categories(); ?>
<section class="hero container"><div class="hero-content"><div class="eyebrow"><span class="pulse"></span> 3D PRINTING MARKETPLACE</div><h1>Biến ý tưởng thành <em>hiện thực.</em></h1><p>Mua mô hình 3D, phụ kiện cơ khí, thiết kế nghệ thuật từ các gian hàng sáng tạo. Theo dõi từng đơn hàng trên cùng một sàn.</p>
<div class="hero-buttons"><a class="btn btn-primary btn-lg" href="<?=h(url('catalog'))?>">Khám phá sản phẩm ↗</a><a class="btn btn-outline btn-lg" href="<?=h(url('apply'))?>">Mở gian hàng của bạn</a></div><div class="hero-stats"><div><b>3 Vai trò</b><small>Khách · Shop · Admin</small></div><div><b>COD</b><small>Nhận hàng thanh toán</small></div><div><b>DEMO</b><small>QR ngân hàng mô phỏng</small></div></div></div>
<div class="hero-art"><div class="orbit orbit1"></div><div class="orbit orbit2"></div><div class="orb-core">⬡<span>3D</span></div><div class="float-label label1">✦ Printed with precision</div><div class="float-label label2">◈ Marketplace for makers</div></div></section>
<div class="ticker"><div>✦ DESIGN · CREATE · PRINT · MARKETPLACE · 3DPRINTHUB · DESIGN · CREATE · PRINT · MARKETPLACE ✦</div></div>
<section class="container section"><div class="section-head"><div><div class="eyebrow">CHỌN DANH MỤC</div><h2>Khám phá <em>thiết kế</em></h2></div><a href="<?=h(url('catalog'))?>">Xem tất cả ↗</a></div>
<div class="categories"><?php foreach(array_slice($cats,0,3) as $i=>$c):?><a class="category-tile cat<?=($i%3)?>" href="<?=h(url('catalog',['category'=>(int)$c['id']]))?>"><div class="cat-icon"><?=['⬡','✧','◈'][$i%3]?></div><div><h3><?=h($c['name'])?></h3><small><?=h($c['description']??'')?></small></div><span>↗</span></a><?php endforeach;?></div></section>
<section class="container section"><div class="section-head"><div><div class="eyebrow">MỚI NHẤT TRÊN SÀN</div><h2>Sản phẩm <em>nổi bật</em></h2></div><a href="<?=h(url('catalog'))?>">Xem bộ sưu tập ↗</a></div>
<?php if($featured):?><div class="product-grid"><?php foreach($featured as $p)require __DIR__.'/../partials/product_card.php';?></div><?php else:?><div class="panel empty-state">Chưa có sản phẩm đang bán. Người bán có thể đăng hàng và chờ Admin duyệt.</div><?php endif;?></section>
<section class="container cta"><div class="eyebrow">GROW YOUR CREATIVE SHOP</div><h2>Bạn có thiết kế <em>ấn tượng?</em></h2><p>Tạo gian hàng, đăng sản phẩm và quản lý đơn hàng trực tiếp trên 3DPrintHub.</p><a class="btn btn-light btn-lg" href="<?=h(url('apply'))?>">Đăng ký người bán ↗</a></section>

<?php // Popup vòng quay chỉ dành cho người dùng đã đăng nhập. ?>
<?php if (!empty($_SESSION['uid'])): ?>
<div id="popupVongQuay" class="popup-vongquay" hidden role="dialog" aria-modal="true" aria-label="Vòng quay may mắn">
  <div class="popup-noidung">
    <button type="button" class="popup-dong" id="popupVongQuayDong" aria-label="Đóng popup">×</button>
    <?php require __DIR__ . '/../admin/vongquay.php'; ?>
  </div>
</div>
<style>
.popup-vongquay[hidden]{display:none!important}
.popup-vongquay:not([hidden]){position:fixed;inset:0;background:rgba(10,7,24,.82);display:flex;justify-content:center;align-items:center;z-index:9999;padding:16px}
.popup-noidung{position:relative;width:min(560px,50%);max-height:94dvh;overflow:auto;border-radius:24px}
.popup-noidung .wv-shell{margin:0!important;zoom:1!important;max-width:none!important;padding:24px 16px!important}
.popup-noidung .wv-wheel-wrap{width:min(70vw,370px)}
.popup-noidung #wv-canvas{width:100%!important;height:100%!important}
.popup-dong{position:sticky;float:right;top:10px;margin:10px 10px -48px 0;z-index:10001;width:42px;height:42px;border:0;border-radius:50%;background:#e83956;color:white;font-size:28px;cursor:pointer;box-shadow:0 3px 14px #0006}
body.popup-vongquay-open{overflow:hidden}
</style>
<script>
(function(){
 const popup=document.getElementById('popupVongQuay');
 if(!popup)return;
 const open=()=>{if(popup.querySelector('.wv-shell')){popup.hidden=false;document.body.classList.add('popup-vongquay-open');}};
 const close=()=>{popup.hidden=true;document.body.classList.remove('popup-vongquay-open');};
 document.getElementById('popupVongQuayDong').addEventListener('click',close);
 popup.addEventListener('click',e=>{if(e.target===popup)close();});
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!popup.hidden)close();});
 window.closePromoWheelPopup=close;
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',open);else open();
})();
</script>
<?php endif; ?>
<style>
/* Thu nhỏ toàn bộ popup */
#popupVongQuay .popup-noidung {
    zoom: 0.5 !important;
}
</style>

<?php defined('APP_BOOTSTRAPPED')||exit;
$oid=max(0,(int)($_GET['id']??0));$o=dbone('SELECT o.*,v.shop_name FROM orders o JOIN vendors v ON v.id=o.vendor_id WHERE o.id=? AND o.user_id=?',[$oid,user()['id']]);
$d=$o?dbone('SELECT * FROM demo_payments WHERE order_id=?',[$oid]):null;
$pay=$o?dbone('SELECT * FROM order_payments WHERE order_id=?',[$oid]):null;
if(!$o||!$d||!$pay||!in_array($pay['payment_provider'],['demo_momo','demo_bank'],true)){http_response_code(404);$o=null;}
?>
<section class="container section page-top"><div class="eyebrow">PAYMENT SIMULATION</div><h1 class="page-title">Thanh toán <em>DEMO</em></h1><p class="notice notice-info">Bản demo này KHÔNG nhận tiền thật. Không dùng ứng dụng ngân hàng hoặc MoMo quét để chuyển tiền.</p>
<?php if(!$o):?><div class="panel">Không tìm thấy giao dịch của bạn. <a href="<?=h(url('my_orders'))?>">Về đơn hàng</a></div>
<?php else:$momo=$d['provider']==='momo';$remaining=max(0,strtotime($d['expires_at'])-time());$paid=$d['status']==='paid';?>
<div class="demo-payment-grid"><div class="panel demo-qr-box"><h2><?=$momo?'MoMo DEMO':'QR ngân hàng DEMO'?></h2><div class="demo-brand-bubble <?=$momo?'brand-momo':'brand-bank'?>"><?=$momo?'M':'▦'?></div>
<?php if(!$paid):?><img class="demo-qr-image" src="assets/<?=$momo?'demo_qr_momo.png':'demo_qr_bank.png'?>" alt="QR minh họa, không dùng chuyển tiền thật"><div class="demo-stamp">DEMO · KHÔNG CHUYỂN TIỀN</div>
<div class="demo-countdown" data-demo-countdown data-seconds="<?=$remaining?>">15:00</div><?php else:?><div class="demo-success">✓ THANH TOÁN MÔ PHỎNG THÀNH CÔNG</div><?php endif;?></div>
<div class="panel demo-pay-detail"><h2>Thông tin giao dịch</h2><div class="eyebrow">MÃ ĐƠN</div><h3><?=h($o['order_code'])?></h3><div class="highlight-total"><?=money($o['total_amount'])?></div><p>Gian hàng: <b><?=h($o['shop_name'])?></b></p><p>Phương thức: <b><?=$momo?'MoMo DEMO':'QR ngân hàng DEMO'?></b></p><p class="muted">Trạng thái: <?=$paid?'Đã xác nhận DEMO':'Chờ mô phỏng'?></p>
<?php if(!$paid&&$remaining>0&&$o['order_status']!=='cancelled'):?><form method="post" action="<?=h(url('demo_payment',['id'=>$o['id']]))?>" data-confirm="Mô phỏng thanh toán thành công? Không chuyển tiền thật."><?php csrf_field();?><input type="hidden" name="action" value="demo_confirm"><input type="hidden" name="order_id" value="<?=$o['id']?>"><button class="btn btn-primary btn-lg">✓ Mô phỏng thanh toán thành công</button></form><?php elseif(!$paid):?><p class="notice notice-info">Đã hết thời gian hoặc đơn bị hủy; không có giao dịch tiền thật.</p><?php endif;?><a class="btn btn-outline" href="<?=h(url('my_orders'))?>">← Quay lại đơn hàng</a></div></div>
<?php endif;?></section>

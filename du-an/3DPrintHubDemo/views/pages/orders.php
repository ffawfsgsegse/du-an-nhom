<?php defined('APP_BOOTSTRAPPED')||exit;$rows=customer_orders((int)user()['id']);?>
<section class="container section page-top"><div class="eyebrow">MY ORDERS</div><h1 class="page-title">Lịch sử <em>đơn hàng</em></h1>
<?php if(!$rows):?><div class="panel empty-state">Bạn chưa có đơn nào. <a href="<?=h(url('catalog'))?>">Bắt đầu mua sắm</a></div><?php else:?>
<?php foreach($rows as $o):$pay=null;$demo=null;?>
<div class="panel order-panel"><div class="order-head"><div><b><?=h($o['order_code'])?></b><small>Gian hàng: <?=h($o['shop_name'])?> · <?=h($o['created_at'])?></small></div><span class="status-pill"><?=h(order_status_label($o['order_status']))?></span></div>
<div class="order-items"><?php foreach(order_items((int)$o['id']) as $item):?><div><?=h($item['product_name']??'Đơn in theo yêu cầu')?> · SL <?=$item['quantity']?> <strong><?=money($item['unit_price']*$item['quantity'])?></strong></div><?php endforeach;?></div>
<div class="order-foot"><strong>Tổng: <?=money($o['total_amount'])?></strong><span><?= $o['payment_method']==='cod'?'COD':'MoMo / QR DEMO' ?></span>
<?php if($o['payment_method']==='banking'):
 $pay=dbone('SELECT status FROM order_payments WHERE order_id=?',[$o['id']]);$demo=dbone('SELECT * FROM demo_payments WHERE order_id=?',[$o['id']]);?>
<?php if($pay&&$pay['status']==='confirmed'):?><span class="status-good">✓ Đã thanh toán DEMO</span>
<?php elseif($demo&&strtotime($demo['expires_at'])>=time()&&$o['order_status']!=='cancelled'):?><a class="btn btn-primary btn-sm" href="<?=h(url('demo_payment',['id'=>$o['id']]))?>">Xem mã thanh toán DEMO →</a>
<?php else:?><span class="muted">QR DEMO đã hết hạn hoặc đơn đã hủy</span><?php endif;?>
<?php endif;?>
<?php if($o['order_status']==='pending' && !($o['payment_method']==='banking' && isset($pay) && $pay['status']==='confirmed')):?><form method="post" action="<?=h(url('my_orders'))?>"><?php csrf_field();?><input type="hidden" name="action" value="cancel_order"><input type="hidden" name="order_id" value="<?=$o['id']?>"><button class="btn btn-ghost btn-sm" data-confirm="Hủy đơn và hoàn tồn kho?">Hủy đơn</button></form><?php endif;?>
</div></div>
<?php endforeach;endif;?></section>

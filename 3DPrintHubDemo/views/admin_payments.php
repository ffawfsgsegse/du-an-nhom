<div class="panel" id="payment-checks"><div class="section-head"><div><div class="eyebrow">PAYMENT MONITOR</div><h2>Đối soát chuyển khoản</h2></div><span class="pay-badge pay-submitted"><?=count(array_filter($payments,fn($x)=>$x['payment_status']==='submitted'))?> chờ xác nhận</span></div>
<p class="muted">Với chuyển khoản <strong>thủ công</strong>, chỉ bấm “Đã nhận tiền” sau khi kiểm tra ngân hàng. Đơn <strong>payOS</strong> được xác nhận bởi webhook đã kiểm tra chữ ký và số tiền; <strong>MoMo DEMO / QR DEMO</strong> chỉ là thao tác giả lập nội bộ, không phát sinh giao dịch thật.</p>
<div class="table-wrap"><table>
  <thead><tr><th>Đơn</th><th>Gian hàng</th><th>Khách</th><th>Số tiền</th><th>Ngân hàng</th><th>Trạng thái</th><th>Biên lai</th><th>Quyết định</th></tr></thead>
  <tbody><?php foreach($payments as $p):?><tr>
    <td><b><?=h($p['order_code'])?></b></td><td><?=h($p['shop_name'])?></td><td><?=h($p['receiver_name'])?></td><td><?=money($p['total_amount'])?></td>
    <td><?=h($p['bank_name'])?> <?=in_array($p['payment_provider']??'',['demo_momo','demo_bank'],true)?'· DEMO':''?><div class="muted"><?=h($p['bank_account'])?></div></td>
    <td><span class="pay-badge pay-<?=h(payment_badge($p['payment_status']))?>"><?=h(payment_label($p['payment_status']))?></span></td>
    <td><?php if($p['receipt_mime']):?><a class="btn btn-ghost btn-sm" target="_blank" href="receipt.php?id=<?=$p['id']?>">Xem ảnh ↗</a><?php else:?>—<?php endif;?></td>
    <td><?php require __DIR__.'/payment_review_actions.php';?></td>
  </tr><?php endforeach;?></tbody>
</table></div>
</div>

<div class="panel"><h2>📦 Đơn hàng & thanh toán</h2>
  <p class="muted">Đơn qua số dư đã được hệ thống trừ tiền; COD phải thu tại thời điểm giao hàng. Người bán xác nhận thu COD thủ công.</p>
  <div class="table-wrap"><table>
    <thead><tr><th>Mã đơn</th><th>Khách</th><th>Giá trị</th><th>Phương thức</th><th>Thanh toán</th><th>Biên lai</th><th>Trạng thái</th><th>Cập nhật</th></tr></thead>
    <tbody><?php foreach($orders as $o):?>
    <tr><td><b><?=h($o['order_code'])?></b></td><td><?=h($o['receiver_name'])?></td><td><?=money($o['total_amount'])?></td>
    <td><?=h(($o['payment_method']==='banking' && ($o['payment_provider']??'')==='demo_momo')?'MoMo DEMO':(($o['payment_method']==='banking' && ($o['payment_provider']??'')==='demo_bank')?'QR DEMO':(['banking'=>'Chuyển khoản','cod'=>'COD','wallet'=>'Số dư 3DPrintHub'][$o['payment_method']]??$o['payment_method'])))?></td>
    <td><?php if($o['payment_method']==='banking'):?><span class="pay-badge pay-<?=h(payment_badge($o['payment_status']))?>"><?=h(payment_label($o['payment_status']))?></span><?php elseif($o['payment_method']==='wallet'):?><span class="pay-badge pay-confirmed">✓ Đã trừ số dư</span><?php else:?><span class="pay-badge <?=($o['cod_collected_at']?'pay-confirmed':'pay-unpaid')?>"><?=($o['cod_collected_at']?'Đã thu COD (thủ công)':'Chưa thu COD')?></span><?php endif;?></td>
    <td><?php if($o['receipt_mime']):?><a class="btn btn-ghost btn-sm" target="_blank" href="receipt.php?id=<?=$o['id']?>">Xem biên lai ↗</a><?php else:?>—<?php endif;?></td>
    <td><span class="status"><?=h($o['order_status'])?></span></td>
    <td>
      <?php if($o['payment_method']==='cod'&&$o['order_status']==='shipping'&&!$o['cod_collected_at']):?>
        <form method="post" class="inline-form" onsubmit="return confirm('Bạn đã THỰC SỰ nhận đủ tiền COD chưa?')"><?php formtoken();?><input type="hidden" name="action" value="cod_collect"><input type="hidden" name="id" value="<?=$o['id']?>"><button class="btn btn-primary btn-sm">✓ Đã thu COD</button></form>
      <?php endif;?>
      <?php $trans=['pending'=>['printing','cancelled'],'printing'=>['shipping','cancelled'],'shipping'=>['completed']]; foreach($trans[$o['order_status']]??[] as $st){
        if($o['payment_method']==='banking' && $st!=='cancelled' && $o['payment_status']!=='confirmed')continue;
        if($o['payment_method']==='cod' && $st==='completed' && !$o['cod_collected_at'])continue;
        actionform('order_status',['id'=>$o['id'],'status'=>$st],$st,'btn btn-ghost btn-sm');
      } ?>
    </td></tr>
    <?php if($o['payment_method']==='banking' && ($o['payment_provider']??'manual')==='manual'):?><tr class="payment-action-row"><td colspan="8"><div class="payment-action-inner"><span>Thanh toán #<?=h($o['order_code'])?> · <?=h($o['bank_name']??'')?> · <?=h($o['bank_account']??'')?></span><?php $p=$o;require __DIR__.'/payment_review_actions.php';?></div></td></tr><?php endif;?>
    <?php endforeach;?></tbody>
  </table></div>
</div>
<div class="panel"><h2>💸 Doanh thu từ đơn đã thanh toán bằng số dư</h2>
  <?php $earnings=dball('SELECT e.*,o.order_code FROM vendor_earnings e JOIN orders o ON o.id=e.order_id WHERE e.vendor_id=? ORDER BY e.created_at DESC LIMIT 60',[$v['id']]);?>
  <div class="table-wrap"><table><tr><th>Đơn</th><th>Giá trị</th><th>Phí sàn (demo)</th><th>Phải trả</th><th>Đối soát</th></tr>
    <?php foreach($earnings as $e):?><tr><td><?=h($e['order_code'])?></td><td><?=money($e['gross_amount'])?></td><td><?=money($e['platform_fee'])?></td><td><?=money($e['net_amount'])?></td><td><?=h(['held'=>'Đang giữ chờ hoàn tất','eligible'=>'Đủ điều kiện đối soát','cancelled'=>'Đơn bị hủy','paid'=>'Đã chi trả'][$e['status']]??$e['status'])?></td></tr><?php endforeach; ?></table></div>
  <p class="muted">Đây là sổ khoản phải trả của sàn, <strong>không tự chuyển tiền vào ngân hàng người bán</strong>. Cần bổ sung quy trình chi trả, kiểm tra đối soát và hợp đồng nếu vận hành thực.</p>
</div>

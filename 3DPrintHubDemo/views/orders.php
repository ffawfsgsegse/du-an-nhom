<section class="container section page-top">
    <div class="eyebrow">MY ACCOUNT · PURCHASES</div>
    <h1 class="page-title">Đơn hàng <em>của tôi</em></h1>
    <div class="notice notice-info">💳 <a href="<?=url('wallet')?>">Xem số dư & lịch sử giao dịch</a> · Bạn có thể mua bằng số dư, COD, MoMo DEMO hoặc QR ngân hàng DEMO trong giỏ hàng.</div>
    <div class="panel">
        <h2>Đơn mua hàng</h2>
        <?php if(!$orders):?><div class="empty">Bạn chưa có đơn hàng. <a href="<?=url('catalog')?>">Khám phá sản phẩm →</a></div><?php else:?>
        <div class="table-wrap"><table>
            <thead><tr><th>Mã đơn</th><th>Xưởng</th><th>Tổng tiền</th><th>Hình thức</th><th>Thanh toán</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
            <tbody><?php foreach($orders as $o):?><tr>
                <td><b><?=h($o['order_code'])?></b></td>
                <td><?=h($o['shop_name'])?></td>
                <td><?=money($o['total_amount'])?></td>
                <td><?=h(($o['payment_method']==='banking' && ($o['payment_provider']??'')==='demo_momo')?'MoMo DEMO':(($o['payment_method']==='banking' && ($o['payment_provider']??'')==='demo_bank')?'QR DEMO':(['banking'=>'Chuyển khoản','cod'=>'COD','wallet'=>'Số dư 3DPrintHub'][$o['payment_method']]??$o['payment_method'])))?></td>
                <td><?php if($o['payment_method']==='banking'):?><span class="pay-badge pay-<?=h(payment_badge($o['payment_status']))?>"><?=h(payment_label($o['payment_status']))?></span><?php elseif($o['payment_method']==='wallet'):?><span class="pay-badge pay-<?=($o['order_status']==='cancelled'?'rejected':'confirmed')?>"><?=($o['order_status']==='cancelled'?'Đã hoàn số dư':'Đã thanh toán')?></span><?php else:?><span class="pay-badge <?=($o['cod_collected_at']?'pay-confirmed':'pay-unpaid')?>"><?=($o['cod_collected_at']?'Đã thu COD':'Trả khi nhận hàng')?></span><?php endif;?></td>
                <td><span class="status"><?=h($o['order_status'])?></span></td>
                <td><?php if($o['payment_method']==='banking'):?><a href="<?=url('payment',['id'=>$o['id']])?>" class="btn btn-primary btn-sm"><?=($o['payment_status']==='confirmed')?'Chi tiết':'Thanh toán / Kiểm tra'?> ↗</a><?php elseif($o['payment_method']==='wallet'):?><a class="btn btn-ghost btn-sm" href="<?=url('wallet')?>">Lịch sử số dư ↗</a><?php else:?>Đợi giao hàng<?php endif;?></td>
            </tr><?php endforeach;?></tbody>
        </table></div><?php endif;?>
        <p class="muted">Đơn nhiều gian hàng được tách riêng. Đơn COD thu tiền khi nhận hàng. Đơn bằng số dư được ghi nhận ngay trong sổ giao dịch.</p>
    </div>
    <div class="panel"><h2>Yêu cầu in theo file</h2><div class="table-wrap"><table><tr><th>ID</th><th>Xưởng</th><th>Giá tạm tính</th><th>Trạng thái</th></tr><?php foreach($quotes as $q):?><tr><td>#<?=$q['id']?></td><td><?=h($q['shop_name'])?></td><td><?=money($q['estimated_price'])?></td><td><?=h($q['status'])?></td></tr><?php endforeach;?></table></div></div>
</section>

<?php
if(!$demo || !wallet_demo_enabled()){
 http_response_code(404);
 echo '<section class="container section page-top"><h1>Không tìm thấy trang thanh toán DEMO</h1><a href="index.php?page=orders">Xem đơn hàng</a></section>';
}else{
 $isTopup=$kind==='topup';
 $provider=$demo['provider'];$momo=$provider==='momo';
 $isPaid=$demo['status']==='paid';
 $isCancelled=(!$isTopup && ($order['order_status']??'')==='cancelled');
 $seconds=max(0,(int)($demo['seconds_left']??0));
 $backUrl=$isTopup?url('wallet'):url('orders');
 $entryId=$isTopup?(int)$topup['id']:(int)$order['id'];
?>
<section class="container section page-top demo-pay-page">
 <div class="eyebrow">3DPRINTHUB · PAYMENT EXPERIENCE</div>
 <h1 class="page-title">Thanh toán <em><?= $momo?'MoMo':'QR ngân hàng' ?></em></h1>
 <p class="muted">Giao diện giả lập phục vụ bài tập PHP + MySQL trên Laragon, <strong>không nhận hoặc chuyển tiền thật</strong>.</p>
 <div class="demo-alert">🧪 <b>CHẾ ĐỘ DEMO</b> — QR chỉ mã hóa văn bản minh họa. Không chuyển khoản, không dùng ứng dụng ngân hàng để trả tiền.</div>
 <div class="demo-pay-layout">
  <div class="panel demo-pay-visual <?= $momo?'momo-mode':'bank-mode' ?>">
   <div class="demo-provider-bar"><span class="demo-provider-mark <?= $momo?'momo-mark':'bank-mark' ?>"><?= $momo?'M':'▦' ?></span><div><b><?= $momo?'MoMo Demo':'QR Ngân hàng Demo' ?></b><small>Thanh toán thử nghiệm nội bộ 3DPrintHub</small></div><span class="demo-pill">DEMO</span></div>
   <?php if($isPaid):?>
     <div class="demo-success-icon">✓</div><h2>Thanh toán thành công!</h2><p class="muted">Đây là kết quả giả lập, không có giao dịch ngân hàng.</p>
   <?php else:?>
     <div class="demo-qr-stage"><img src="assets/<?= $momo?'demo_qr_momo.png':'demo_qr_bank.png' ?>" alt="QR minh họa <?= $momo?'MoMo':'ngân hàng' ?>, không dùng để chuyển tiền" width="260" height="260"><span class="demo-qr-stamp">DEMO · KHÔNG CHUYỂN TIỀN</span></div>
     <p class="demo-timer">Mã minh họa còn hiệu lực <strong data-demo-countdown data-seconds="<?=$seconds?>">15:00</strong></p>
   <?php endif; ?>
  </div>
  <div class="panel demo-pay-detail">
    <span class="payment-step">01 · THÔNG TIN GIAO DỊCH</span>
    <h2><?= $isTopup?'Nạp số dư 3DPrintHub':'Thanh toán đơn hàng' ?></h2>
    <div class="demo-amount"><?=money($demoAmount)?></div>
    <div class="pay-row"><span>Mã tham chiếu</span><div><b><?=h($demoCode)?></b></div></div>
    <div class="pay-row"><span>Phương thức</span><div><b><?= $momo?'MoMo DEMO':'QR ngân hàng DEMO' ?></b></div></div>
    <div class="pay-row"><span>Trạng thái</span><div><span class="pay-badge <?= $isPaid?'pay-confirmed':'pay-unpaid' ?>"><?= $isPaid?'✓ Đã thanh toán (DEMO)':'Chờ xác nhận DEMO' ?></span></div></div>
    <div class="demo-steps"><span>1</span> Xem QR mô phỏng <span>→</span> <span>2</span> Bấm nút xác nhận <span>→</span> <span>3</span> Hệ thống cập nhật MySQL</div>
    <?php if($isPaid):?>
       <div class="notice notice-success">✓ Giao dịch đã được ghi nhận trong cơ sở dữ liệu. <?= $isTopup?'Tiền DEMO đã được cộng vào số dư.':'Người bán đã có thể xử lý đơn hàng.' ?></div>
       <a class="btn btn-primary btn-lg" href="<?=h($backUrl)?>"><?= $isTopup?'Xem số dư và lịch sử':'Xem đơn hàng' ?> ↗</a>
    <?php elseif($isCancelled):?>
       <div class="notice notice-danger">Đơn hàng này đã hủy, không thể xác nhận thanh toán.</div>
    <?php else:?>
       <div class="notice notice-info">Bấm nút dưới đây để <b>mô phỏng thành công</b>. Hệ thống không kiểm tra MoMo, không gọi ngân hàng, không rút tiền thật.</div>
       <?php if($seconds>0):?>
        <form method="post" class="stack" data-confirm="Xác nhận mô phỏng thanh toán thành công? KHÔNG phát sinh tiền thật.">
          <?php formtoken();?><input type="hidden" name="action" value="demo_payment_confirm"><input type="hidden" name="kind" value="<?=h($kind)?>"><input type="hidden" name="id" value="<?=$entryId?>">
          <button class="btn btn-primary btn-lg demo-confirm-button">✓ Mô phỏng thanh toán thành công</button>
        </form>
       <?php endif; ?>
       <form method="post" class="stack demo-refresh-form" <?= $seconds>0?'hidden':'' ?> id="demo-refresh-form">
         <?php formtoken();?><input type="hidden" name="action" value="demo_payment_refresh"><input type="hidden" name="kind" value="<?=h($kind)?>"><input type="hidden" name="id" value="<?=$entryId?>">
         <button class="btn btn-ghost btn-lg">↻ Làm mới mã DEMO (15 phút)</button>
       </form>
       <a class="btn btn-ghost" href="<?=h($backUrl)?>">← Quay lại</a>
    <?php endif; ?>
  </div>
 </div>
</section>
<?php } ?>

<section class="container section page-top payment-shell">
 <div class="eyebrow">3DPRINTHUB · PAYOS · THANH TOÁN TỰ ĐỘNG</div>
 <h1 class="page-title">Thanh toán <em>VietQR thật</em></h1>
 <p class="muted">Đơn <?=h($order['order_code'])?> · Gian hàng <?=h($order['shop_name'])?></p>
 <div class="payment-layout">
  <div class="panel payment-qr-panel" style="display:flex;flex-direction:column;justify-content:center;gap:16px;align-items:center;">
    <div class="bank-icon" style="width:80px;height:80px;font-size:44px;display:grid;place-items:center;">▦</div>
    <h2>Quét QR qua payOS</h2>
    <p class="muted" style="text-align:center;">Bạn sẽ được chuyển đến cổng payOS để quét mã bằng ứng dụng ngân hàng. Không phải tự tải biên lai.</p>
    <div class="payment-amount" style="font-size:32px;"><?=money($order['total_amount'])?></div>
    <?php if($payment['status']!=='confirmed' && $order['order_status']!=='cancelled'):?>
     <form method="post" action="payos_start.php">
       <?php formtoken();?><input type="hidden" name="order_id" value="<?=$order['id']?>">
       <button class="btn btn-primary btn-lg" type="submit">Thanh toán ngay qua payOS ↗</button>
     </form>
    <?php endif;?>
  </div>
  <div class="panel payment-details">
    <span class="payment-step">Đơn hàng <?=h($order['order_code'])?></span>
    <div class="pay-row"><span>Gian hàng</span><strong><?=h($order['shop_name'])?></strong></div>
    <div class="pay-row"><span>Số tiền</span><strong><?=money($order['total_amount'])?></strong></div>
    <div class="pay-row"><span>Phương thức</span><strong>payOS · Chuyển khoản VietQR</strong></div>
    <div class="pay-row"><span>Trạng thái</span><strong><?=h(payment_label($payment['status']))?></strong></div>
    <?php if($payment['status']==='confirmed'):?>
      <div class="notice notice-success">✓ Ngân hàng đã xác nhận thanh toán qua payOS. Bạn không cần chuyển thêm tiền.</div>
    <?php elseif($order['order_status']==='cancelled'):?>
      <div class="notice notice-danger">Đơn đã hủy. Không thanh toán đơn này.</div>
    <?php else:?>
      <div class="notice notice-info">Sau khi chuyển khoản thành công, payOS sẽ thông báo về website. Có thể mất vài giây mới cập nhật. Nhấn tải lại để xem trạng thái.</div>
    <?php endif;?>
    <p class="muted">Tiền chuyển vào tài khoản của kênh payOS do chủ sàn cấu hình, <strong>không</strong> chuyển trực tiếp đến tài khoản từng người bán.</p>
    <div class="payment-footer-links"><a class="btn btn-outline" href="<?=url('payment',['id'=>$order['id']])?>">↻ Tải lại trạng thái</a><a class="btn btn-ghost" href="<?=url('orders')?>">Đơn hàng của tôi</a></div>
  </div>
 </div>
</section>

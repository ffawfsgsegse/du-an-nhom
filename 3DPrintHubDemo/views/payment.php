<section class="container section page-top payment-shell">
    <div class="eyebrow">SECURE BANK TRANSFER · VIETQR</div>
    <h1 class="page-title">Thanh toán <em>chuyển khoản</em></h1>
    <p class="muted">Đơn <?=h($order['order_code'])?> · <?=h($order['shop_name'])?></p>
    <?php if($order['order_status']==='cancelled'):?>
        <div class="notice notice-danger">Đơn hàng đã hủy. Không thực hiện chuyển khoản cho đơn này. Nếu đã gửi tiền, hãy liên hệ gian hàng để đối soát và hoàn tiền.</div>
    <?php elseif($payment['status']==='confirmed'):?>
        <div class="notice notice-success">✓ Xưởng đã xác nhận nhận đủ tiền. Đơn hàng đang được xử lý.</div>
    <?php elseif($payment['status']==='submitted'):?>
        <div class="notice notice-info">⏳ Bạn đã gửi thông báo chuyển khoản. Người bán/Admin sẽ kiểm tra giao dịch trên tài khoản ngân hàng trước khi xác nhận.</div>
    <?php elseif($payment['status']==='rejected'):?>
        <div class="notice notice-danger">⚠ Người bán chưa xác nhận được giao dịch. <?=h($payment['review_note']?:'Vui lòng kiểm tra số tiền, tài khoản nhận và nội dung chuyển khoản rồi gửi lại.')?></div>
    <?php endif;?>
    <div class="payment-layout">
        <div class="panel payment-qr-panel">
            <span class="payment-step">01 · Quét mã để chuyển tiền</span>
            <?php $qr=vietqr_image($payment['bank_name'],$payment['bank_account'],$order['total_amount'],$order['order_code'],$payment['bank_account_holder']);?>
            <?php if($qr):?>
                <div class="qr-frame"><img src="<?=h($qr)?>" alt="VietQR chuyển khoản cho đơn <?=h($order['order_code'])?>" loading="eager" referrerpolicy="no-referrer"></div>
                <p class="muted qr-caption">Mở ứng dụng ngân hàng → quét mã QR → đối chiếu người nhận, số tiền và nội dung → xác nhận chuyển tiền.</p>
            <?php else:?>
                <div class="qr-unavailable">⌁<strong>Chưa tạo được QR</strong><span>Ngân hàng chưa có mã VietQR trong danh sách hỗ trợ. Bạn vẫn có thể chuyển khoản thủ công theo thông tin bên cạnh.</span></div>
            <?php endif;?>
            <div class="payment-note">Mã QR do VietQR cung cấp (cần Internet). Việc quét mã <strong>không tự động thanh toán</strong> hay tự xác nhận tiền đã nhận.</div>
        </div>
        <div class="panel payment-details">
            <span class="payment-step">02 · Kiểm tra thông tin nhận tiền</span>
            <div class="bank-brand"><span class="bank-icon">▦</span><div><strong><?=h($payment['bank_name'])?></strong><small>Ngân hàng của <?=h($order['shop_name'])?></small></div></div>
            <div class="pay-row"><span>Số tài khoản</span><div><b><?=h($payment['bank_account'])?></b><button type="button" class="copy-btn" data-copy="<?=h($payment['bank_account'])?>">Sao chép</button></div></div>
            <div class="pay-row"><span>Chủ tài khoản</span><div><b><?=h($payment['bank_account_holder'] ?: 'Xác minh trên ứng dụng ngân hàng')?></b></div></div>
            <div class="pay-row"><span>Số tiền cần chuyển</span><div><b class="payment-amount"><?=money($order['total_amount'])?></b><button type="button" class="copy-btn" data-copy="<?=h((string)(int)round($order['total_amount']))?>">Sao chép</button></div></div>
            <?php $transferNote=preg_replace('/[^A-Za-z0-9]/','',$order['order_code']);?>
            <div class="pay-row"><span>Nội dung chuyển khoản <strong class="required">(bắt buộc)</strong></span><div><b class="transfer-code"><?=h($transferNote)?></b><button type="button" class="copy-btn" data-copy="<?=h($transferNote)?>">Sao chép</button></div></div>
            <?php if(!$payment['bank_account_holder']):?><div class="notice notice-danger">Gian hàng chưa khai báo tên chủ tài khoản. Hãy xác minh đúng chủ tài khoản trong ứng dụng ngân hàng trước khi chuyển tiền.</div><?php endif;?>
            <div class="payment-note">Mỗi gian hàng có <strong>mã đơn và tài khoản riêng</strong>. Nếu mua từ nhiều gian hàng, hãy chuyển từng đơn riêng.</div>
            <?php if($order['order_status']!=='cancelled' && $payment['status']!=='confirmed'):?>
                <form method="post" enctype="multipart/form-data" class="stack proof-form">
                    <?php formtoken();?><input type="hidden" name="action" value="payment_submit"><input type="hidden" name="order_id" value="<?=$order['id']?>">
                    <span class="payment-step">03 · Thông báo đã chuyển khoản</span>
                    <label>Ảnh biên lai / màn hình chuyển khoản (không bắt buộc, tối đa 2MB, JPG/PNG/WEBP)
                        <input type="file" name="proof" accept="image/jpeg,image/png,image/webp">
                    </label>
                    <button class="btn btn-primary btn-lg" type="submit" data-confirm="Chỉ nhấn sau khi bạn thực sự chuyển khoản. Đây chưa phải xác nhận đã nhận tiền. Tiếp tục?">✓ Tôi đã chuyển khoản · Gửi xác nhận</button>
                    <p class="muted">Chỉ gửi thông báo sau khi bạn đã chuyển tiền thật. Người bán sẽ kiểm tra giao dịch trước khi cập nhật trạng thái.</p>
                </form>
            <?php endif;?>
        </div>
    </div>
    <div class="payment-footer-links"><a class="btn btn-outline" href="<?=url('orders')?>">← Xem đơn hàng của tôi</a><a class="btn btn-ghost" href="<?=url('catalog')?>">Tiếp tục mua sắm</a></div>
</section>

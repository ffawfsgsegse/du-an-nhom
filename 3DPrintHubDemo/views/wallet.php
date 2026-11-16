<section class="container section page-top wallet-page">
  <div class="eyebrow">3DPRINTHUB · SỐ DƯ MUA HÀNG</div>
  <h1 class="page-title">Số dư <em>của tôi</em></h1>
  <div class="wallet-hero">
    <div><div class="wallet-kicker">SỐ DƯ KHẢ DỤNG · DEMO</div><div class="wallet-amount"><?=money($wallet['balance'])?></div><span class="wallet-sub">Chỉ thanh toán sản phẩm trên 3DPrintHub · Không chuyển tiền ngang hàng / rút tiền</span></div>
    <div class="wallet-ring">✦<span>3D</span></div>
  </div>
  <div class="wallet-columns">
    <div class="panel">
      <h2>⚡ Nạp số dư</h2>
      <?php if(wallet_topup_enabled()):?>
        <p class="muted">Chuyển khoản qua payOS. Số dư chỉ tăng khi webhook thanh toán được xác minh thành công.</p>
        <form action="wallet_topup_start.php" method="post" class="stack">
          <?php formtoken(); ?>
          <label>Số tiền (VNĐ)<input type="number" name="amount" min="10000" max="5000000" step="1000" value="100000" required></label>
          <div class="wallet-presets"><button type="button" data-wallet-amount="50000">50.000đ</button><button type="button" data-wallet-amount="100000">100.000đ</button><button type="button" data-wallet-amount="200000">200.000đ</button><button type="button" data-wallet-amount="500000">500.000đ</button></div>
          <button class="btn btn-primary btn-lg">Nạp qua ngân hàng ↗</button>
        </form>
      <?php else:?>
        <div class="notice notice-info">Chế độ nạp tiền ngân hàng đang <strong>tắt</strong>. Bản demo có thể sử dụng số dư thử nghiệm do Admin cấp trên máy localhost. Để nạp tiền thật cần kênh thanh toán payOS, HTTPS/webhook và cơ sở pháp lý phù hợp.</div>
        <a class="btn btn-ghost" href="<?=url('catalog')?>">Khám phá sản phẩm ↗</a>
      <?php endif;?>
<?php if(wallet_demo_enabled()):?>
 <div class="demo-wallet-box">
  <h3>🧪 Nạp tiền bằng MoMo / QR — DEMO</h3>
  <p class="muted">Chọn số tiền ảo, xem mã QR và nhấn mô phỏng thành công. Chỉ chạy trên localhost; không có giao dịch thực.</p>
  <form method="post" class="stack" action="index.php?page=wallet">
   <?php formtoken();?><input type="hidden" name="action" value="demo_topup_start">
   <label>Số tiền DEMO (10.000 – 2.000.000đ)<input type="number" name="amount" min="10000" max="2000000" step="1000" value="100000" required></label>
   <div class="wallet-presets"><button type="button" data-wallet-amount="50000">50.000đ</button><button type="button" data-wallet-amount="100000">100.000đ</button><button type="button" data-wallet-amount="200000">200.000đ</button><button type="button" data-wallet-amount="500000">500.000đ</button></div>
   <label>Phương thức<select name="provider"><option value="momo">MoMo DEMO</option><option value="bank_qr">QR ngân hàng DEMO</option></select></label>
   <button class="btn btn-primary btn-lg">Tạo giao dịch nạp DEMO ↗</button>
  </form>
 </div>
<?php endif; ?>
      <p class="muted" style="font-size:12px;margin-top:22px">Không được xem việc quay lại trang thành công hay gửi ảnh biên lai là bằng chứng ngân hàng đã chuyển tiền.</p>
    </div>
    <div class="panel">
      <h2>🧾 Lịch sử nạp tiền</h2>
      <?php if(!$topups):?><p class="muted">Chưa có yêu cầu nạp.</p><?php else:?>
      <div class="table-wrap"><table><tr><th>Mã</th><th>Số tiền</th><th>Trạng thái</th><th>Thời gian</th><th>Thao tác</th></tr>
      <?php foreach($topups as $t):?><tr><td>#<?=$t['id']?></td><td><?=money($t['amount'])?></td><td><span class="pay-badge"><?=h(['pending'=>'Đang chờ','paid'=>'Đã xác nhận','failed'=>'Lỗi','cancelled'=>'Đã hủy'][$t['status']]??$t['status'])?></span></td><td><?=h($t['created_at'])?></td><td><?php if($t['status']==='pending' && $t['checkout_url']):?><a class="btn btn-ghost btn-sm" href="<?=h($t['checkout_url'])?>" target="_blank" rel="noopener noreferrer">Thanh toán ↗</a><?php endif;?></td></tr><?php endforeach;?></table></div>
      <?php endif;?>
    </div>
  </div>
  <div class="panel"><h2>📜 Lịch sử biến động số dư</h2>
    <?php if(!$transactions):?><div class="empty">Chưa có giao dịch. Số dư chỉ được thay đổi qua hệ thống thanh toán hoặc cấp demo có kiểm soát.</div><?php else:?>
    <div class="table-wrap"><table><thead><tr><th>Thời gian</th><th>Nội dung</th><th>Thay đổi</th><th>Số dư sau</th></tr></thead><tbody>
    <?php foreach($transactions as $t):?><tr><td><?=h($t['created_at'])?></td><td><?=h($t['description'])?></td><td style="color:<?=$t['direction']==='credit'?'#75e7b9':'#ffb1bc'?>;font-weight:700"><?=$t['direction']==='credit'?'+':'−'?><?=money($t['amount'])?></td><td><?=money($t['balance_after'])?></td></tr><?php endforeach;?></tbody></table></div>
    <?php endif;?>
  </div>
</section>

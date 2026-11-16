<div class="panel"><h2>💳 Quản lý số dư · Nạp tiền</h2>
  <div class="stat-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));margin:16px 0 25px">
    <div class="stat-card">Tổng số dư khách hàng<strong><?=money($walletLiability['total'])?></strong></div>
    <div class="stat-card">Giao dịch nạp được đối soát<strong><?=h($topupStats['paid_count'])?></strong></div>
    <div class="stat-card">Đơn thanh toán số dư<strong><?=h($walletOrderCount['n'])?></strong></div>
  </div>
  <?php if(wallet_demo_enabled()):?>
  <div class="notice notice-info">CHẾ ĐỘ DEMO LOCALHOST: Số tiền tạo dưới đây chỉ để thử chức năng, không tương ứng với khoản ngân hàng nhận được. Không có trên máy chủ public.</div>
  <form class="stack" method="post"><?php formtoken();?><input type="hidden" name="action" value="wallet_demo_credit"><div class="form-grid"><label>Khách hàng thử nghiệm<select name="user_id" required><?php foreach($walletClients as $c):?><option value="<?=$c['id']?>"><?=h($c['full_name'].' · '.$c['email'])?></option><?php endforeach;?></select></label><label>Cộng số dư DEMO<input type="number" name="amount" min="1000" max="200000" step="1000" value="100000" required></label></div><button class="btn btn-primary">Cộng điểm thử nghiệm</button></form>
  <?php endif;?>
  <div class="table-wrap"><table><tr><th>ID</th><th>Khách</th><th>Số tiền</th><th>Trạng thái</th><th>Thời gian</th></tr>
    <?php foreach($adminTopups as $t):?><tr><td>#<?=$t['id']?></td><td><?=h($t['full_name'])?></td><td><?=money($t['amount'])?></td><td><?=h($t['status'])?></td><td><?=h($t['created_at'])?></td></tr><?php endforeach;?></table></div>
</div>

<div class="panel"><h2>🏦 Khoản phải đối soát và chi trả cho người bán</h2>
  <p class="muted">Các giao dịch này được tạo từ đơn thanh toán bằng số dư, nhưng hệ thống <strong>chưa chi tiền tự động</strong>. Bản demo để trạng thái đối soát riêng nhằm tránh hiểu nhầm là tiền đã tới ngân hàng người bán.</p>
  <div class="table-wrap"><table><tr><th>Mã đơn</th><th>Gian hàng</th><th>Doanh số</th><th>Khoản phải trả</th><th>Trạng thái</th></tr>
  <?php foreach($walletPayables as $e):?><tr><td><?=h($e['order_code'])?></td><td><?=h($e['shop_name'])?></td><td><?=money($e['gross_amount'])?></td><td><?=money($e['net_amount'])?></td><td><?=h($e['status'])?></td></tr><?php endforeach;?></table></div>
</div>
<div class="panel"><h2>📦 Theo dõi thanh toán khi nhận hàng (COD)</h2>
  <p class="muted">Nhãn "Đã thu COD" là do người bán tự ghi nhận, không xác nhận tự động từ hãng vận chuyển.</p>
  <div class="table-wrap"><table><tr><th>Đơn</th><th>Gian hàng</th><th>Số tiền</th><th>Đơn hàng</th><th>Thu COD</th></tr>
  <?php foreach($codForAdmin as $o):?><tr><td><?=h($o['order_code'])?></td><td><?=h($o['shop_name'])?></td><td><?=money($o['total_amount'])?></td><td><?=h($o['order_status'])?></td><td><?= $o['collected_at']?'Người bán đã xác nhận':'Chưa xác nhận'?></td></tr><?php endforeach;?></table></div>
</div>

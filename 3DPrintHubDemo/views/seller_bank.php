<div class="panel seller-bank-panel" id="bank-settings">
  <div class="section-head"><div><div class="eyebrow">PAYOUT SETTINGS</div><h2>🏦 Tài khoản nhận chuyển khoản</h2></div><span class="pay-badge">Dành cho gian hàng</span></div>
  <p class="muted">Mỗi đơn chuyển khoản sẽ hiện tài khoản ngân hàng của xưởng bạn. Hãy nhập <strong>tài khoản nhận tiền thật do bạn sở hữu</strong> và xác minh trước khi mở bán.</p>
  <form method="post" class="stack">
    <?php formtoken();?><input type="hidden" name="action" value="save_bank_account">
    <div class="form-grid">
      <label>Ngân hàng (tên hoặc mã BIN)
        <input name="bank_name" value="<?=h($v['bank_name'])?>" list="bank-options" placeholder="Vietcombank" maxlength="100" required>
      </label>
      <label>Số tài khoản nhận tiền
        <input name="bank_account" value="<?=h($v['bank_account'])?>" maxlength="25" inputmode="numeric" pattern="[0-9]{4,25}" required>
      </label>
      <label>Tên chủ tài khoản (trùng với ngân hàng)
        <input name="bank_holder" value="<?=h($v['bank_account_holder']??'')?>" maxlength="120" placeholder="NGUYEN VAN A" required>
      </label>
    </div>
    <datalist id="bank-options"><option value="Vietcombank"><option value="Techcombank"><option value="MB Bank"><option value="BIDV"><option value="VietinBank"><option value="Agribank"><option value="ACB"><option value="VPBank"><option value="Sacombank"><option value="TPBank"><option value="VIB"><option value="SHB"><option value="OCB"></datalist>
    <button class="btn btn-primary" type="submit">Lưu thông tin nhận tiền ↗</button>
  </form>
  <div class="payment-note">Các tài khoản có sẵn trong <code>database.sql</code> là dữ liệu minh họa. Đổi thành số tài khoản thật trước khi nhận chuyển khoản từ khách.</div>
</div>

<?php defined('APP_BOOTSTRAPPED')||exit;$v=vendor_of_user();?>
<section class="container section page-top">
  <div class="eyebrow">GROW YOUR SHOP</div>
  <h1 class="page-title">Bắt đầu <em>bán hàng</em></h1>
  <div class="checkout-grid">
    <div class="panel">
      <h2>Gian hàng trên 3DPrintHub</h2>
      <p class="muted">Đăng sản phẩm in 3D, nhận đơn gia công theo yêu cầu, quản lý tồn kho, vật liệu và đối soát doanh thu minh bạch.</p>
      <div class="flow-tip">① Đăng ký tài khoản khách hàng.</div>
      <div class="flow-tip">② Khai báo hồ sơ năng lực xưởng &amp; tài khoản ngân hàng.</div>
      <div class="flow-tip">③ Admin thẩm định thiết bị và cấp quyền Vendor.</div>
      <div class="flow-tip">④ Cấu hình vật liệu, đăng sản phẩm và nhận đơn.</div>
      <div class="notice notice-info" style="margin-top:16px">
        <b>Lưu ý kiểm duyệt:</b> Hồ sơ có thông tin máy in, khổ in tối đa và tài khoản ngân hàng chính chủ rõ ràng sẽ được duyệt trong vòng 24h.
      </div>
    </div>

    <div class="panel">
      <?php if(!user()):?>
        <h2>Đăng nhập để đăng ký</h2>
        <p class="muted">Bạn cần có tài khoản thành viên trước khi nộp hồ sơ mở xưởng in.</p>
        <a class="btn btn-primary" href="<?=h(url('login'))?>">Đăng nhập ngay</a>
      <?php elseif($v):?>
        <h2>Hồ sơ gian hàng của bạn</h2>
        <p><b><?=h($v['shop_name'])?></b> · <span class="status-pill"><?=h(['pending'=>'Đang chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Bị từ chối'][$v['status']]??$v['status'])?></span></p>
        <div class="order-items">
          <div><span>Địa chỉ xưởng:</span> <strong><?=h($v['address'])?></strong></div>
          <?php if(!empty($v['contact_phone'])):?><div><span>Điện thoại:</span> <strong><?=h($v['contact_phone'])?></strong></div><?php endif;?>
          <?php if(!empty($v['printer_tech'])):?><div><span>Công nghệ in:</span> <strong><?=h($v['printer_tech'])?></strong></div><?php endif;?>
          <?php if(!empty($v['max_build_volume'])):?><div><span>Khổ in tối đa:</span> <strong><?=h($v['max_build_volume'])?></strong></div><?php endif;?>
          <?php if(!empty($v['printer_capacity'])):?><div><span>Thiết bị máy in:</span> <strong><?=h($v['printer_capacity'])?></strong></div><?php endif;?>
          <?php if(!empty($v['bank_name'])):?><div><span>Ngân hàng đối soát:</span> <strong><?=h($v['bank_name'].' - '.$v['bank_account'])?></strong></div><?php endif;?>
        </div>
        <?php if(user()['role']==='vendor'):?><a class="btn btn-primary" href="<?=h(url('vendor_dashboard'))?>">Vào kênh người bán →</a><?php endif;?>
      <?php elseif(user()['role']!=='client'):?>
        <h2>Tài khoản đã có quyền quản trị</h2>
      <?php else:?>
        <h2>Hồ sơ đăng ký mở xưởng in 3D</h2>
        <form method="post" action="<?=h(url('apply'))?>" class="stack">
          <?php csrf_field();?>
          <input type="hidden" name="action" value="apply_vendor">

          <div class="eyebrow" style="margin:6px 0 0">1. THÔNG TIN ĐỊNH DANH XƯỞNG</div>
          <label>Tên gian hàng / Xưởng in (*)
            <input name="shop_name" required maxlength="150" value="<?=h($_POST['shop_name']??'')?>" placeholder="VD: Xưởng in 3D TechLab">
          </label>
          <div class="market-form-row">
            <label>Số điện thoại liên hệ (*)
              <input name="contact_phone" required maxlength="20" value="<?=h($_POST['contact_phone']??'')?>" placeholder="0988 123 456">
            </label>
            <label>Số CCCD / Mã số thuế (*)
              <input name="tax_or_cccd" required maxlength="30" value="<?=h($_POST['tax_or_cccd']??'')?>" placeholder="001099xxxxxx">
            </label>
          </div>
          <label>Địa chỉ xưởng sản xuất (*)
            <input name="address" required maxlength="255" value="<?=h($_POST['address']??'')?>" placeholder="Số nhà, đường, quận/huyện, tỉnh/thành phố">
          </label>

          <div class="eyebrow" style="margin:10px 0 0">2. NĂNG LỰC THIẾT BỊ &amp; CÔNG NGHỆ IN</div>
          <div>
            <span style="display:block;font-size:13px;font-weight:600;color:#c7cbe0;margin-bottom:8px">Công nghệ in hỗ trợ (*)</span>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
              <?php foreach(['FDM','SLA/DLP Resin','SLS','MJF'] as $t):?>
                <label class="payment-option" style="flex:1;min-width:130px;padding:10px 12px">
                  <input type="checkbox" name="printer_tech[]" value="<?=h($t)?>" <?=in_array($t,(array)($_POST['printer_tech']??['FDM']),true)?'checked':''?>>
                  <span><?=h($t)?></span>
                </label>
              <?php endforeach;?>
            </div>
          </div>
          <div class="market-form-row">
            <label>Danh sách máy in hiện có (*)
              <input name="capacity" required maxlength="255" value="<?=h($_POST['capacity']??'')?>" placeholder="VD: 5 máy Bambu Lab P1S, 2 Resin 8K">
            </label>
            <label>Khổ in tối đa (Dài × Rộng × Cao)
              <input name="max_build_volume" maxlength="100" value="<?=h($_POST['max_build_volume']??'')?>" placeholder="VD: 256 x 256 x 256 mm">
            </label>
          </div>
          <label>Giới thiệu năng lực gia công &amp; hậu kỳ
            <textarea name="description" maxlength="1000" rows="3" placeholder="Mô tả độ mịn lớp in, vật liệu thế mạnh, hỗ trợ chà nhám/sơn hoàn thiện..."><?=h($_POST['description']??'')?></textarea>
          </label>

          <div class="eyebrow" style="margin:10px 0 0">3. TÀI KHOẢN NGÂN HÀNG NHẬN ĐỐI SOÁT</div>
          <div class="market-form-row">
            <label>Ngân hàng (*)
              <select name="bank_name" required>
                <option value="">-- Chọn ngân hàng --</option>
                <?php foreach(['Vietcombank','Techcombank','MB Bank','BIDV','VietinBank','ACB','VPBank','TPBank'] as $b):?>
                  <option value="<?=h($b)?>" <?=($_POST['bank_name']??'')===$b?'selected':''?>><?=h($b)?></option>
                <?php endforeach;?>
              </select>
            </label>
            <label>Số tài khoản (*)
              <input name="bank_account" required maxlength="50" value="<?=h($_POST['bank_account']??'')?>" placeholder="001100xxxxxxx">
            </label>
          </div>
          <label>Tên chủ tài khoản (viết hoa không dấu) (*)
            <input name="bank_account_holder" required maxlength="120" value="<?=h($_POST['bank_account_holder']??'')?>" placeholder="NGUYEN VAN A">
          </label>

          <label class="payment-option">
            <input type="checkbox" name="agree_terms" value="1" required>
            <span>Tôi cam kết bảo mật file thiết kế 3D của khách hàng, không nhận in sản phẩm vi phạm pháp luật và đồng ý chính sách phí sàn 5%.</span>
          </label>

          <button class="btn btn-primary btn-lg">GỬI HỒ SƠ MỞ GIAN HÀNG →</button>
        </form>
      <?php endif;?>
    </div>
  </div>
</section>
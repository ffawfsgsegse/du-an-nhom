<?php
defined('APP_BOOTSTRAPPED') || exit;
$v = approved_vendor();
$selectedTech = array_map('trim', explode(',', (string)($v['printer_tech'] ?? 'FDM')));
?>
<section class="container section page-top">
    <div class="dash-layout">
        <?php require __DIR__ . '/../partials/side_nav.php'; ?>
        <div class="dash-main">
            <div class="eyebrow">SHOP SETTINGS</div>
            <h1 class="page-title">Hồ sơ <em>gian hàng</em></h1>
            <p class="muted">Cập nhật thông tin xưởng in, năng lực thiết bị và tài khoản ngân hàng nhận tiền đối soát.</p>

            <div class="panel">
                <form method="post" action="<?= h(url('vendor_profile')) ?>" class="stack product-editor-form">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="vendor_profile_save">

                    <div class="eyebrow" style="margin:4px 0 0">1. THÔNG TIN CƠ BẢN &amp; PHÁP LÝ</div>
                    <div class="market-form-row">
                        <label>Tên gian hàng / Xưởng in (*)
                            <input name="shop_name" required maxlength="150" value="<?= h($v['shop_name']) ?>">
                        </label>
                        <label>Số điện thoại liên hệ
                            <input name="contact_phone" maxlength="20" value="<?= h($v['contact_phone'] ?? '') ?>" placeholder="0988 123 456">
                        </label>
                    </div>

                    <div class="market-form-row">
                        <label>Địa chỉ xưởng sản xuất (*)
                            <input name="address" required maxlength="255" value="<?= h($v['address']) ?>">
                        </label>
                        <?php $hasTax = !empty($v['tax_or_cccd']); ?>
                        <label>CCCD / Mã số thuế <?= $hasTax ? '(Đã xác minh)' : '(*)' ?>
                            <input name="tax_or_cccd" maxlength="30" value="<?= h($v['tax_or_cccd'] ?? '') ?>" placeholder="001099xxxxxx" <?= $hasTax ? 'disabled' : 'required' ?>>
                        </label>
                    </div>

                    <div class="eyebrow" style="margin:12px 0 0">2. NĂNG LỰC MÁY IN &amp; KỸ THUẬT</div>
                    <div>
                        <span style="display:block;font-size:13px;font-weight:600;color:#aeb8cf;margin-bottom:8px">Công nghệ in hỗ trợ (*)</span>
                        <div style="display:flex;gap:12px;flex-wrap:wrap">
                            <?php foreach (['FDM', 'SLA/DLP Resin', 'SLS', 'MJF'] as $t): ?>
                                <label class="payment-option" style="display:flex;align-items:center;gap:8px;flex:1;min-width:130px;padding:10px 12px;cursor:pointer">
                                    <input style="width:auto;margin:0" type="checkbox" name="printer_tech[]" value="<?= h($t) ?>" <?= in_array($t, $selectedTech, true) ? 'checked' : '' ?>>
                                    <span><?= h($t) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="market-form-row">
                        <label>Danh sách thiết bị / máy in (*)
                            <input name="printer_capacity" required maxlength="255" value="<?= h($v['printer_capacity'] ?? '') ?>" placeholder="VD: 8 máy FDM Bambu Lab P1S">
                        </label>
                        <label>Khổ in tối đa (mm)
                            <input name="max_build_volume" maxlength="100" value="<?= h($v['max_build_volume'] ?? '') ?>" placeholder="VD: 256 x 256 x 256 mm">
                        </label>
                    </div>

                    <label>Giới thiệu năng lực gia công &amp; hậu kỳ
                        <textarea name="description" maxlength="1000" rows="3" placeholder="Mô tả độ mịn lớp in, dịch vụ tháo support, sơn hoàn thiện..."><?= h($v['description'] ?? '') ?></textarea>
                    </label>

                    <div class="eyebrow" style="margin:12px 0 0">3. TÀI KHOẢN NGÂN HÀNG NHẬN ĐỐI SOÁT</div>
                    <div class="market-form-row">
                        <label>Ngân hàng (*)
                            <select name="bank_name" required>
                                <option value="">-- Chọn ngân hàng --</option>
                                <?php foreach (['Vietcombank', 'Techcombank', 'MB Bank', 'BIDV', 'VietinBank', 'ACB', 'VPBank', 'TPBank'] as $b): ?>
                                    <option value="<?= h($b) ?>" <?= ($v['bank_name'] ?? '') === $b ? 'selected' : '' ?>><?= h($b) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Số tài khoản (*)
                            <input name="bank_account" required maxlength="50" value="<?= h($v['bank_account'] ?? '') ?>">
                        </label>
                    </div>

                    <label>Tên chủ tài khoản (viết hoa không dấu) (*)
                        <input name="bank_account_holder" required maxlength="120" value="<?= h($v['bank_account_holder'] ?? '') ?>" placeholder="TRAN HOANG MINH">
                    </label>

                    <div class="dashboard-actions">
                        <button class="btn btn-primary btn-lg">LƯU THAY ĐỔI HỒ SƠ →</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php defined('APP_BOOTSTRAPPED') || exit;
/** @var bool $adminMode */
/** @var ?array $product */
/** @var array $cats */
/** @var array $materials */
/** @var array $vendors */
$editMode = isset($product) && $product;
$actionName = $adminMode ? 'admin_product_save' : 'vendor_product_save';
$pageName = $adminMode ? 'admin_product_edit' : 'vendor_product_edit';
$returnPage = $adminMode ? 'admin_products' : 'vendor_products';

$variants = $editMode
    ? dball('SELECT * FROM product_variants WHERE product_id=? ORDER BY id ASC', [$product['id']])
    : [];
if (!$variants) {
    $variants = [[
        'id' => 0,
        'material_name' => '',
        'color' => '',
        'size_label' => 'Tiêu chuẩn',
        'price' => $product['price'] ?? '',
        'stock' => $product['stock'] ?? 10,
    ]];
}
?>
<form method="post" enctype="multipart/form-data" action="<?= h(url($pageName, $editMode ? ['id' => $product['id']] : [])) ?>" class="stack product-editor-form">
    <?php csrf_field(); ?>
    <input type="hidden" name="action" value="<?= h($actionName) ?>">
    <input type="hidden" name="id" value="<?= $editMode ? (int)$product['id'] : 0 ?>">
    <?php if ($editMode && ($product['status'] ?? '') === 'rejected' && !empty($product['reject_reason'])): ?>
        <div class="notice" style="background:rgba(255,107,107,.12);border:1px solid rgba(255,107,107,.35);color:#ffb3b3">
            <b>⚠️ Sản phẩm từng bị Admin từ chối duyệt:</b> <?= h($product['reject_reason']) ?><br>
            <small>Hãy chỉnh sửa theo góp ý trên và bấm <b>Lưu sản phẩm</b> để gửi duyệt lại.</small>
        </div>
    <?php endif; ?>

    <?php if ($adminMode): ?>
        <label>Chọn gian hàng (đã duyệt)
            <select name="vendor_id" id="vendorSelect" required>
                <option value="">-- Chọn shop --</option>
                <?php foreach ($vendors as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= ($editMode && (int)$product['vendor_id'] === (int)$v['id']) ? 'selected' : '' ?>><?= h($v['shop_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>

    <div class="market-form-row">
        <label>Tên sản phẩm
            <input name="name" required maxlength="200" minlength="3" value="<?= h($product['name'] ?? '') ?>" placeholder="Ví dụ: Cánh tay Robot 4 bậc">
        </label>
        <label>Danh mục
            <select name="category_id" required>
                <option value="">-- Chọn danh mục --</option>
                <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($editMode && (int)$product['category_id'] === (int)$c['id']) ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <div class="market-form-row">
        <label>Vật liệu mặc định (hiển thị tổng quan)
            <select name="material_id" id="materialSelect" required>
                <option value="">-- Chọn vật liệu --</option>
                <?php foreach ($materials as $m): ?>
                    <option data-vendor="<?= $m['vendor_id'] ?>" value="<?= $m['id'] ?>" <?= ($editMode && (int)$product['material_id'] === (int)$m['id']) ? 'selected' : '' ?>>
                        <?= h($m['name']) ?><?= $adminMode ? ' · ' . h($m['shop_name']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Kích thước mô hình (mô tả)
            <input name="dimensions" maxlength="100" value="<?= h($product['dimensions'] ?? '') ?>" placeholder="180 × 120 × 210 mm">
        </label>
    </div>

    <!-- Thông số đóng gói & giao hàng -->
    <div class="panel" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.08);padding:16px">
        <div class="eyebrow">SHIPPING SPECIFICATIONS</div>
        <h3 style="margin:4px 0 12px">Thông số đóng gói &amp; Vận chuyển (Tính phí ship)</h3>
        <div class="market-form-row" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
            <label>Cân nặng sau đóng gói (gram) *
                <input type="number" name="weight_gram" min="1" max="50000" required value="<?= h((string)($product['weight_gram'] ?? 200)) ?>" placeholder="VD: 250">
            </label>
            <label>Chiều dài gói hàng (cm) *
                <input type="number" name="pkg_length" min="1" max="200" step="0.1" required value="<?= h((string)($product['pkg_length'] ?? 15)) ?>" placeholder="VD: 20">
            </label>
            <label>Chiều rộng gói hàng (cm) *
                <input type="number" name="pkg_width" min="1" max="200" step="0.1" required value="<?= h((string)($product['pkg_width'] ?? 15)) ?>" placeholder="VD: 15">
            </label>
            <label>Chiều cao gói hàng (cm) *
                <input type="number" name="pkg_height" min="1" max="200" step="0.1" required value="<?= h((string)($product['pkg_height'] ?? 10)) ?>" placeholder="VD: 10">
            </label>
        </div>
        <small class="muted" style="display:block;margin-top:6px">Dùng để đơn vị vận chuyển (ViettelPost / GHN) tính cước phí và khối lượng quy đổi thể tích.</small>
    </div>

    <!-- Bảng đa thuộc tính -->
    <div class="panel" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.08);padding:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:wrap">
            <div>
                <div class="eyebrow">MULTI-ATTRIBUTE VARIANTS</div>
                <h3 style="margin:4px 0 0">Phân loại thuộc tính (Vật liệu · Màu sắc · Kích thước)</h3>
            </div>
            <button type="button" class="btn btn-outline btn-sm" id="addVariantBtn">+ Thêm phân loại</button>
        </div>
        <div style="overflow-x:auto">
            <table class="table" id="variantsTable" style="width:100%;min-width:620px">
                <thead>
                    <tr>
                        <th>Vật liệu in *</th>
                        <th>Màu sắc *</th>
                        <th>Kích thước / Độ mịn *</th>
                        <th>Giá bán (VNĐ) *</th>
                        <th>Tồn kho *</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="variantsBody">
                    <?php foreach ($variants as $idx => $vr): ?>
                        <tr class="variant-row">
                            <td>
                                <input type="hidden" name="variants[<?= $idx ?>][id]" value="<?= (int)($vr['id'] ?? 0) ?>">
                                <input name="variants[<?= $idx ?>][material_name]" required maxlength="80" value="<?= h($vr['material_name']) ?>" placeholder="VD: PLA+, PETG, Resin 8K">
                            </td>
                            <td>
                                <input name="variants[<?= $idx ?>][color]" required maxlength="60" value="<?= h($vr['color']) ?>" placeholder="VD: Đen nhám, Trắng">
                            </td>
                            <td>
                                <input name="variants[<?= $idx ?>][size_label]" required maxlength="80" value="<?= h($vr['size_label']) ?>" placeholder="VD: 15cm / Layer 0.2mm">
                            </td>
                            <td>
                                <input type="number" name="variants[<?= $idx ?>][price]" min="1000" max="1000000000" step="1000" required value="<?= h((string)($vr['price'] !== '' ? (int)$vr['price'] : '')) ?>" placeholder="150000">
                            </td>
                            <td>
                                <input type="number" name="variants[<?= $idx ?>][stock]" min="0" max="100000" required value="<?= h((string)$vr['stock']) ?>">
                            </td>
                            <td>
                                <button type="button" class="btn btn-ghost btn-sm remove-variant-btn" title="Xóa dòng">✕</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <small class="muted" style="display:block;margin-top:8px">Giá hiển thị ngoài danh mục sẽ tự động lấy theo phân loại có giá thấp nhất; tổng tồn kho là tổng của tất cả phân loại.</small>
    </div>

    <label>Ảnh sản phẩm JPG/PNG/WEBP (tối đa 3MB)
        <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
    </label>
    <?php if ($editMode && !empty($product['image_path'])): ?>
        <div class="editor-preview">
            <img src="<?= h($product['image_path']) ?>" alt="Ảnh hiện tại">
            <span>Ảnh đang sử dụng. Chọn ảnh mới để thay.</span>
        </div>
    <?php endif; ?>

    <div class="dashboard-actions">
        <button class="btn btn-primary btn-lg">LƯU SẢN PHẨM →</button>
        <a class="btn btn-outline" href="<?= h(url($returnPage)) ?>">Hủy</a>
    </div>
</form>

<script>
    (function() {
        const tbody = document.getElementById('variantsBody');
        const addBtn = document.getElementById('addVariantBtn');
        let nextIdx = tbody.querySelectorAll('tr.variant-row').length;

        addBtn?.addEventListener('click', () => {
            const tr = document.createElement('tr');
            tr.className = 'variant-row';
            tr.innerHTML = `
            <td>
                <input type="hidden" name="variants[${nextIdx}][id]" value="0">
                <input name="variants[${nextIdx}][material_name]" required maxlength="80" placeholder="VD: PETG, Resin 8K">
            </td>
            <td><input name="variants[${nextIdx}][color]" required maxlength="60" placeholder="VD: Trắng sứ"></td>
            <td><input name="variants[${nextIdx}][size_label]" required maxlength="80" value="Tiêu chuẩn"></td>
            <td><input type="number" name="variants[${nextIdx}][price]" min="1000" max="1000000000" step="1000" required placeholder="200000"></td>
            <td><input type="number" name="variants[${nextIdx}][stock]" min="0" max="100000" required value="10"></td>
            <td><button type="button" class="btn btn-ghost btn-sm remove-variant-btn" title="Xóa dòng">✕</button></td>
        `;
            tbody.appendChild(tr);
            nextIdx++;
        });

        tbody?.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-variant-btn');
            if (!btn) return;
            const rows = tbody.querySelectorAll('tr.variant-row');
            if (rows.length <= 1) {
                alert('Sản phẩm phải có ít nhất 1 phân loại thuộc tính.');
                return;
            }
            btn.closest('tr').remove();
        });
    })();
</script>
<?php defined('APP_BOOTSTRAPPED') || exit;
$v = approved_vendor();
$rows = vendor_products((int)$v['id']);
$materials = dball('SELECT * FROM materials WHERE vendor_id=? ORDER BY id DESC', [$v['id']]); ?>
<section class="container section page-top">
    <div class="dash-layout"><?php require __DIR__ . '/../partials/side_nav.php'; ?><div class="dash-main">
            <div class="eyebrow">SELLER INVENTORY</div>
            <h1 class="page-title">Sản phẩm <em>của shop</em></h1>
            <p class="muted">Cập nhật số lượng tồn kho có hiệu lực ngay; thay đổi tên, ảnh, giá hoặc phân loại sẽ chuyển sang chờ duyệt.</p>
            <div class="dashboard-actions"><a class="btn btn-primary" href="<?= h(url('vendor_product_edit')) ?>">+ Đăng sản phẩm mới</a></div>
            <div class="panel">
                <h2>Danh sách sản phẩm (<?= count($rows) ?>)</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Tồn kho</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($rows as $p): ?><tr>
                                    <td>
                                        <div class="mini-thumb"><?php if ($p['image_path']): ?><img src="<?= h($p['image_path']) ?>" alt=""><?php else: ?>⬡<?php endif; ?></div>
                                    </td>
                                    <td>
                                        <b><?= h($p['name']) ?></b>
                                        <?php if ($p['status'] === 'rejected' && !empty($p['reject_reason'])): ?>
                                            <div style="margin-top:4px;font-size:0.85rem;color:#ff7b72">
                                                ✕ Lý do từ chối: <?= h($p['reject_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= money($p['price']) ?></td>
                                    <td><?= $p['stock'] ?></td>
                                    <td>
                                        <?php if ($p['status'] === 'rejected'): ?>
                                            <span class="status-pill" style="background:rgba(255,107,107,.16);color:#ff7b72;border-color:rgba(255,107,107,.35)">rejected</span>
                                        <?php else: ?>
                                            <span class="status-pill"><?= h($p['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-cell">
                                        <a class="btn btn-ghost btn-sm" href="<?= h(url('product', ['id' => $p['id']])) ?>"><?= $p['status'] === 'active' ? 'Xem' : 'Xem trước' ?></a>
                                        <a class="btn btn-outline btn-sm" href="<?= h(url('vendor_product_edit', ['id' => $p['id']])) ?>">Sửa</a>
                                        <?php if ($p['status'] !== 'hidden'): ?><form method="post" action="<?= h(url('vendor_products')) ?>"><?php csrf_field(); ?><input type="hidden" name="action" value="vendor_product_hide"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="btn btn-ghost btn-sm">Ẩn</button></form><?php endif; ?>
                                        <form method="post" action="<?= h(url('vendor_products')) ?>" data-confirm="Xóa mềm sản phẩm này? Không xóa lịch sử đơn hàng."><?php csrf_field(); ?><input type="hidden" name="action" value="vendor_product_delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="btn btn-ghost btn-sm">Xóa</button></form>
                                    </td>
                                </tr><?php endforeach; ?></tbody>
                    </table>
                </div><?php if (!$rows): ?><p class="muted">Chưa có sản phẩm. Bấm Đăng sản phẩm mới để bắt đầu.</p><?php endif; ?>
            </div>
            <div class="panel">
                <h2>Vật liệu của shop</h2>
                <p class="muted">Bạn cần tạo ít nhất một vật liệu trước khi đăng sản phẩm.</p>
                <div class="materials-tags"><?php foreach ($materials as $m): ?><span class="pill"><?= h($m['name']) ?> (<?= money($m['price_per_cm3']) ?> / cm³)</span><?php endforeach; ?></div>
                <form method="post" action="<?= h(url('vendor_products')) ?>" class="market-form-inline"><?php csrf_field(); ?><input type="hidden" name="action" value="vendor_material_add"><label>Tên vật liệu<input name="name" required placeholder="PLA+, ABS, Resin..."></label><label>Đơn giá / cm³<input type="number" name="price_per_cm3" min="1000" step="1000" required placeholder="3000"></label><button class="btn btn-primary">+ Thêm vật liệu</button></form>
            </div>
        </div>
    </div>
</section>
<?php
/** Bộ lọc nhiều thuộc tính, thao tác trực tiếp ngay khi gõ/chọn (JS). */
defined('APP_BOOTSTRAPPED') || exit;
// Trước khi require: khai báo $filterTable, $filterPlaceholder và $filterFields.
$filterFields = $filterFields ?? [];
?>
<div class="admin-filter-toolbar" data-filter-table="<?=h($filterTable)?>">
 <label class="admin-search-box"><?=admin_icon('search')?><input type="search" data-filter-search placeholder="<?=h($filterPlaceholder ?? 'Tìm kiếm...')?>" value="<?=h((string)($_GET['q']??''))?>" aria-label="Tìm kiếm trong bảng" autocomplete="off"></label>
 <?php foreach ($filterFields as $filter):?>
 <label class="admin-filter-select-wrap"><span><?=h($filter['label'])?></span><select data-filter-key="<?=h($filter['key'])?>" aria-label="Lọc theo <?=h($filter['label'])?>"><option value="">Tất cả</option><?php foreach($filter['options'] as $v=>$label):?><option value="<?=h((string)$v)?>"><?=h($label)?></option><?php endforeach;?></select></label>
 <?php endforeach; ?>
 <button type="button" class="admin-clear-filter" data-filter-clear title="Đặt lại bộ lọc"><?=admin_icon('refresh-cw')?><span>Xóa lọc</span></button>
</div>
<div class="admin-filter-summary"><span data-filter-count="<?=h($filterTable)?>">Đang hiển thị dữ liệu</span><span>Lọc tức thì theo nhiều thuộc tính</span></div>
<?php unset($filterFields,$filterTable,$filterPlaceholder); ?>

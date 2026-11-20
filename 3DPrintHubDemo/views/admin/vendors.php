<?php defined('APP_BOOTSTRAPPED')||exit;$rows=dball('SELECT v.*,u.full_name,u.email,u.status user_status FROM vendors v JOIN users u ON u.id=v.user_id ORDER BY FIELD(v.status,"pending","approved","rejected"),v.id DESC'); ?>
<section class="container section page-top"><div class="dash-layout"><?php require __DIR__.'/../partials/side_nav.php';?><div class="dash-main"><div class="eyebrow">SHOP APPROVAL</div><h1 class="page-title">Duyệt <em>gian hàng</em></h1><p class="muted">Kiểm tra năng lực máy in, thông tin định danh và tài khoản đối soát trước khi cấp quyền Vendor.</p>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>Gian hàng &amp; Năng lực in</th><th>Chủ xưởng &amp; Pháp lý</th><th>Tài khoản nhận tiền</th><th>Trạng thái</th><th>Duyệt</th></tr></thead><tbody><?php foreach($rows as $v):?>
<tr>
  <td>
    <strong><?=h($v['shop_name'])?></strong>
    <small class="table-sub">📍 <?=h($v['address'])?><?= !empty($v['contact_phone']) ? ' · 📞 '.h($v['contact_phone']) : '' ?></small>
    <?php if(!empty($v['printer_tech'])||!empty($v['max_build_volume'])):?>
      <small class="table-sub">⚙️ <?=h($v['printer_tech']??'FDM')?><?= !empty($v['max_build_volume']) ? ' · Khổ tối đa: '.h($v['max_build_volume']) : '' ?></small>
    <?php endif;?>
    <small class="table-sub"><?=h($v['printer_capacity']??'')?></small>
  </td>
  <td>
    <?=h($v['full_name'])?>
    <small class="table-sub"><?=h($v['email'])?></small>
    <?php if(!empty($v['tax_or_cccd'])):?><small class="table-sub">CCCD/MST: <b><?=h($v['tax_or_cccd'])?></b></small><?php endif;?>
  </td>
  <td>
    <?php if(!empty($v['bank_name'])):?>
      <b><?=h($v['bank_name'])?></b>
      <small class="table-sub"><?=h($v['bank_account']??'')?></small>
      <small class="table-sub"><?=h($v['bank_account_holder']??'')?></small>
    <?php else:?><span class="muted">Chưa cập nhật</span><?php endif;?>
  </td>
  <td><span class="status-pill"><?=h($v['status'])?></span></td>
  <td>
    <div style="display:flex;gap:6px;align-items:center">
      <form method="post" action="<?=h(url('admin_vendors'))?>" style="margin:0"><?php csrf_field();?><input type="hidden" name="action" value="admin_vendor_review"><input type="hidden" name="id" value="<?=$v['id']?>"><input type="hidden" name="status" value="approved"><button class="btn btn-primary btn-sm">Duyệt</button></form>
      <form method="post" action="<?=h(url('admin_vendors'))?>" style="margin:0"><?php csrf_field();?><input type="hidden" name="action" value="admin_vendor_review"><input type="hidden" name="id" value="<?=$v['id']?>"><input type="hidden" name="status" value="rejected"><button class="btn btn-ghost btn-sm">Từ chối</button></form>
    </div>
  </td>
</tr>
<?php endforeach;?></tbody></table></div></div></div></div></section>
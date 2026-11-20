<?php
defined('APP_BOOTSTRAPPED') || exit;
$vendor = approved_vendor();
$rows = payout_available_orders((int)$vendor['id']);
$available = array_sum(array_map(static fn(array $row): int => (int)$row['available'], $rows));
$history = dball('SELECT * FROM vendor_payouts WHERE vendor_id=? ORDER BY id DESC LIMIT 50', [$vendor['id']]);
$statuses = ['pending'=>'Chờ xử lý','processing'=>'Đang xử lý','paid'=>'Đã chi trả','rejected'=>'Từ chối'];
?>
<section class="container section page-top">
    <a href="<?= h(url('vendor_dashboard')) ?>">Về kênh bán</a>
    <h1 class="page-title">Rút doanh thu</h1>
    <div class="panel">
        <p>Có thể rút <strong><?= h(money($available)) ?></strong></p>
        <p>Phí mỗi yêu cầu <strong><?= h(money(VENDOR_PAYOUT_FEE)) ?></strong></p>
        <?php if ($available > VENDOR_PAYOUT_FEE): ?>
            <form method="post" action="<?= h(url('vendor_payouts')) ?>" class="stack" id="withdraw-form">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="vendor_payout_request">
                <input type="hidden" name="request_key" value="<?= h(bin2hex(random_bytes(16))) ?>">
                <label>Số tiền muốn rút
                    <input id="withdraw-amount" type="number" name="amount" min="<?= VENDOR_PAYOUT_FEE + 1 ?>" max="<?= h($available) ?>" required>
                </label>
                <p>Thực nhận <strong id="withdraw-net" aria-live="polite">0 đ</strong></p>
                <?php require __DIR__ . '/../partials/withdrawal_bank.php'; ?>
                <label>Số tài khoản <input name="bank_account" inputmode="numeric" maxlength="30" required value="<?= h($vendor['bank_account'] ?? '') ?>"></label>
                <label>Chủ tài khoản <input name="bank_holder" maxlength="120" required value="<?= h($vendor['bank_account_holder'] ?? '') ?>"></label>
                <button class="btn btn-primary">Gửi yêu cầu rút tiền</button>
            </form>
        <?php else: ?>
            <p>Doanh thu đã đối soát phải lớn hơn phí rút.</p>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h2>Lịch sử yêu cầu</h2>
        <?php if (!$history): ?><p>Chưa có yêu cầu rút tiền.</p><?php else: ?>
        <div class="table-wrap"><table>
            <thead><tr><th>Ngày gửi</th><th>Số tiền rút</th><th>Phí</th><th>Thực nhận</th><th>Trạng thái</th></tr></thead>
            <tbody><?php foreach ($history as $row): ?>
                <tr>
                    <td><?= h($row['created_at']) ?></td>
                    <td><?= h(money($row['amount'])) ?></td>
                    <td><?= h(money($row['fee'])) ?></td>
                    <td><?= h(money($row['transfer_amount'])) ?></td>
                    <td><?= h($statuses[$row['status']]) ?>
                        <?php if ($row['bank_reference'] !== null): ?><br><?= h($row['bank_reference']) ?><?php endif; ?>
                        <?php if ($row['rejection_reason'] !== null): ?><br><?= h($row['rejection_reason']) ?><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?></tbody>
        </table></div>
        <?php endif; ?>
    </div>
</section>
<script>
const withdrawAmount = document.getElementById('withdraw-amount');
if (withdrawAmount) {
    const withdrawNet = document.getElementById('withdraw-net');
    const withdrawFee = <?= VENDOR_PAYOUT_FEE ?>;
    const showNet = () => {
        withdrawNet.textContent = Math.max(0, Number(withdrawAmount.value) - withdrawFee).toLocaleString('vi-VN') + ' đ';
    };
    withdrawAmount.addEventListener('input', showNet);
    showNet();
}
</script>

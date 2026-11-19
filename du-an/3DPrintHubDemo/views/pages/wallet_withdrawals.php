<?php
defined('APP_BOOTSTRAPPED') || exit;
$isAdminPage = $page === 'admin_wallet_withdrawals';
$user = $isAdminPage ? need_role('admin') : need_role('client', 'vendor');
$statuses = ['pending'=>'Chờ xử lý','processing'=>'Đang xử lý','paid'=>'Đã chi trả','rejected'=>'Từ chối'];
if ($isAdminPage) {
    $history = dball("SELECT w.*,u.full_name FROM wallet_withdrawals w JOIN users u ON u.id=w.user_id
                     ORDER BY FIELD(w.status,'processing','pending','paid','rejected'),w.id DESC LIMIT 100");
} else {
    $wallet = dbone('SELECT balance FROM wallet_accounts WHERE user_id=?', [$user['id']]);
    $available = wallet_withdrawable((int)$user['id'], (int)($wallet['balance'] ?? 0));
    $history = dball('SELECT * FROM wallet_withdrawals WHERE user_id=? ORDER BY id DESC LIMIT 50', [$user['id']]);
}
?>
<section class="container section page-top">
    <h1 class="page-title"><?= $isAdminPage ? 'Chi trả rút ví khách hàng' : 'Rút tiền từ ví' ?></h1>
    <?php if ($isAdminPage): ?>
        <a href="<?= h(url('admin_payouts')) ?>">Chi trả doanh thu shop</a>
    <?php else: ?>
        <div class="panel">
            <p>Số tiền có thể rút <strong><?= h(money($available)) ?></strong></p>
            <p>Phí mỗi yêu cầu <strong><?= h(money(VENDOR_PAYOUT_FEE)) ?></strong></p>
            <?php if ($available > VENDOR_PAYOUT_FEE): ?>
                <form method="post" action="<?= h(url('wallet_withdrawals')) ?>" class="stack">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="wallet_withdraw_request">
                    <input type="hidden" name="request_key" value="<?= h(bin2hex(random_bytes(16))) ?>">
                    <label>Số tiền muốn rút
                        <input id="wallet-withdraw-amount" type="number" name="amount" min="<?= VENDOR_PAYOUT_FEE + 1 ?>" max="<?= h($available) ?>" required>
                    </label>
                    <p>Thực nhận <strong id="wallet-withdraw-net" aria-live="polite">0 đ</strong></p>
                    <label>Ngân hàng <input name="bank_name" maxlength="100" required></label>
                    <label>Số tài khoản <input name="bank_account" inputmode="numeric" maxlength="30" required></label>
                    <label>Chủ tài khoản <input name="bank_holder" maxlength="120" required></label>
                    <button class="btn btn-primary">Gửi yêu cầu rút ví</button>
                </form>
            <?php else: ?>
                <p>Số dư có nguồn nạp SePay đã xác minh cần lớn hơn phí rút.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <h2>Lịch sử yêu cầu</h2>
        <?php if (!$history): ?><p>Chưa có yêu cầu rút ví.</p><?php endif; ?>
        <?php foreach ($history as $row):
            $assigned = $isAdminPage && $row['status'] === 'processing'
                && (int)$row['handled_by'] === (int)$user['id'];
        ?>
            <article class="stack">
                <p><strong>#<?= h($row['id']) ?></strong> · <?= h($row['created_at']) ?> · <?= h($statuses[$row['status']]) ?>
                    <?php if ($isAdminPage): ?> · <?= h($row['full_name']) ?><?php endif; ?></p>
                <p>Rút <?= h(money($row['amount'])) ?> · Phí <?= h(money($row['fee'])) ?>
                    · Thực nhận <?= h(money($row['transfer_amount'])) ?></p>
                <?php if ($row['bank_reference'] !== null): ?><p>Mã chuyển khoản <?= h($row['bank_reference']) ?></p><?php endif; ?>
                <?php if ($row['rejection_reason'] !== null): ?><p><?= h($row['rejection_reason']) ?></p><?php endif; ?>
                <?php if ($isAdminPage && $row['status'] === 'pending'): ?>
                    <form method="post" action="<?= h(url('admin_wallet_withdrawals')) ?>">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= h($row['id']) ?>">
                        <button class="btn btn-outline" name="action" value="admin_wallet_withdraw_claim">Nhận xử lý</button>
                    </form>
                <?php endif; ?>
                <?php if ($assigned): ?>
                    <p><?= h($row['bank_name']) ?> · <?= h($row['bank_account']) ?> · <?= h($row['bank_holder']) ?></p>
                    <form class="stack" method="post" action="<?= h(url('admin_wallet_withdrawals')) ?>">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= h($row['id']) ?>">
                        <label>Mã giao dịch chuyển khoản <input name="bank_reference" maxlength="120" required></label>
                        <button class="btn btn-primary" name="action" value="admin_wallet_withdraw_paid">Xác nhận đã chuyển tiền</button>
                    </form>
                <?php endif; ?>
                <?php if ($isAdminPage && ($assigned || $row['status'] === 'pending')): ?>
                    <form class="stack" method="post" action="<?= h(url('admin_wallet_withdrawals')) ?>">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= h($row['id']) ?>">
                        <label>Lý do từ chối <input name="reason" maxlength="255" required></label>
                        <button class="btn btn-outline" name="action" value="admin_wallet_withdraw_reject">Từ chối và trả lại tiền vào ví</button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<script>
const walletWithdrawAmount = document.getElementById('wallet-withdraw-amount');
if (walletWithdrawAmount) {
    walletWithdrawAmount.addEventListener('input', () => {
        const net = Math.max(0, Number(walletWithdrawAmount.value) - <?= VENDOR_PAYOUT_FEE ?>);
        document.getElementById('wallet-withdraw-net').textContent = net.toLocaleString('vi-VN') + ' đ';
    });
}
</script>

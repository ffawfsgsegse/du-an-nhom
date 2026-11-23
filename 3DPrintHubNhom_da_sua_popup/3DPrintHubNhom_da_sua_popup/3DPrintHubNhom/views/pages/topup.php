<?php
declare(strict_types=1);

need_login();
$currentUser = user();
$userId = (int)$currentUser['id'];

$errorMessage = '';
$topup = null;
$qrUrl = '';
$walletBalance = null;
$bankName = 'ACB';
$accountNo = '3555441';
$accountName = 'PHI VIET DUNG';

try {
    global $pdo;
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException('PDO connection unavailable');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $submittedAmount = $_POST['amount'] ?? '';

        if (!is_string($submittedAmount) || !preg_match('/\A[1-9][0-9]{3,13}\z/', $submittedAmount)) {
            $errorMessage = 'Nhập số tiền nguyên từ 1.000 đ trở lên.';
        } else {
            $newCode = 'NAP' . $userId . 'XA' . strtoupper(bin2hex(random_bytes(6)));
            $orderCode = date('ymdHis') . random_int(100000, 999999);

            $stmt = $pdo->prepare("
                INSERT INTO wallet_topups (user_id, amount, status, payos_order_code, payment_link_id)
                VALUES (?, ?, 'pending', ?, ?)
            ");
            if (!$stmt->execute([$userId, $submittedAmount, $orderCode, $newCode])) {
                throw new RuntimeException('Could not create top-up order');
            }

            $topup = [
                'payment_link_id' => $newCode,
                'amount' => $submittedAmount,
                'status' => 'pending',
            ];
        }
    } elseif (isset($_GET['code'])) {
        $code = is_string($_GET['code']) ? strtoupper(trim($_GET['code'])) : '';

        if (preg_match('/\ANAP[1-9][0-9]{0,9}X(?:A[0-9A-F]{12}|[0-9]{4})\z/', $code)) {
            $stmt = $pdo->prepare("
                SELECT payment_link_id, amount, status FROM wallet_topups
                WHERE user_id = ? AND payment_link_id = ? LIMIT 2
            ");
            if (!$stmt->execute([$userId, $code])) {
                throw new RuntimeException('Could not read top-up order');
            }

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($orders) === 1) {
                $topup = $orders[0];
            }
        }

        if ($topup === null) {
            $errorMessage = 'Không tìm thấy lệnh nạp thuộc tài khoản đang đăng nhập.';
        }
    }

    $stmt = $pdo->prepare('SELECT balance FROM wallet_accounts WHERE user_id = ?');
    if (!$stmt->execute([$userId])) {
        throw new RuntimeException('Could not read wallet balance');
    }

    $balance = $stmt->fetchColumn();
    $walletBalance = $balance === false ? 0 : $balance;

    if ($topup !== null && $topup['status'] === 'pending') {
        $qrUrl = vietqr_image(
            $bankName,
            $accountNo,
            (int)$topup['amount'],
            $topup['payment_link_id'],
            $accountName
        );
    }
} catch (Throwable $e) {
    error_log('3DPrintHub top-up [' . $userId . ']: ' . $e->getMessage());
    $errorMessage = 'Chưa thể xử lý lệnh nạp. Vui lòng thử lại.';
}

$status = $topup['status'] ?? '';
$paymentLinkId = $topup['payment_link_id'] ?? '';
$statusUrl = 'index.php?page=topup&code=' . rawurlencode($paymentLinkId);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Nạp tiền vào ví 3DPrintHub</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 small">
                        Tài khoản <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong> (ID #<?= $userId ?>)
                        <?php if ($walletBalance !== null): ?>
                            <br>Số dư ví <strong><?= number_format((float)$walletBalance, 0, ',', '.') ?> đ</strong>
                        <?php endif; ?>
                    </div>

                    <?php if ($errorMessage !== ''): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>

                    <?php if ($topup !== null): ?>
                        <div id="sepay-topup-status" data-status="<?= htmlspecialchars($status) ?>">
                            <?php if ($status === 'paid'): ?>
                                <div class="alert alert-success">Đã cộng tiền vào ví.</div>
                            <?php elseif ($status === 'pending' && $qrUrl !== ''): ?>
                                <div class="text-center my-4">
                                    <img src="<?= htmlspecialchars($qrUrl) ?>" alt="Mã QR nạp tiền"
                                         class="img-fluid border rounded p-2" style="max-width: 260px;">
                                    <p class="mt-3 mb-1">
                                        Chuyển đúng số tiền <strong><?= number_format((float)$topup['amount'], 0, ',', '.') ?> đ</strong>
                                    </p>
                                    <p class="mb-1">Giữ nguyên nội dung chuyển khoản</p>
                                    <h5 class="text-danger fw-bold bg-light py-2 border rounded"><?= htmlspecialchars($paymentLinkId) ?></h5>
                                    <p class="text-muted small mt-3">Đang chờ xác nhận từ SePay.</p>
                                </div>
                            <?php elseif ($status !== 'pending'): ?>
                                <div class="alert alert-warning">Lệnh nạp này đã đóng.</div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <a href="index.php?page=topup" class="btn btn-secondary">Tạo lệnh nạp mới</a>
                            <?php if ($status === 'pending'): ?>
                                <a id="sepay-topup-check" href="<?= htmlspecialchars($statusUrl) ?>" class="btn btn-success">Kiểm tra số dư</a>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['code'])): ?>
                        <form method="POST" action="index.php?page=topup">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Số tiền nạp (VNĐ)</label>
                                <input type="number" class="form-control" id="amount" name="amount"
                                       min="1000" max="99999999999999" step="1000" value="2000" required>
                            </div>
                            <div class="mb-3">
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('amount').value=1000">1.000 đ</button>
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('amount').value=2000">2.000 đ</button>
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('amount').value=5000">5.000 đ</button>
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('amount').value=10000">10.000 đ</button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Tạo mã QR nạp tiền</button>
                        </form>
                    <?php else: ?>
                        <a href="index.php?page=topup" class="btn btn-secondary">Quay lại trang nạp tiền</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($status === 'pending'): ?>
<script>
(() => {
    const statusUrl = document.getElementById('sepay-topup-check').href;
    const expires = Date.now() + 120000;

    async function checkTopup() {
        if (Date.now() >= expires) return;

        try {
            const response = await fetch(statusUrl, { cache: 'no-store' });
            if (response.ok) {
                const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                const status = page.getElementById('sepay-topup-status')?.dataset.status;

                if (status && status !== 'pending') {
                    window.location.replace(statusUrl);
                    return;
                }
            }
        } catch (error) {
            // Người dùng vẫn có thể bấm Kiểm tra số dư.
        }

        window.setTimeout(checkTopup, 5000);
    }

    window.setTimeout(checkTopup, 5000);
})();
</script>
<?php endif; ?>
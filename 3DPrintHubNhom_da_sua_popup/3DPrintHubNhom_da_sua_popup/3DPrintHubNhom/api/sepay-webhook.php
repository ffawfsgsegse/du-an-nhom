<?php
declare(strict_types=1);

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('X-3DPrintHub-Webhook: sepay-wallet-3');

function sepayResponse(int $status, bool $success, string $message): void
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    sepayResponse(405, false, 'POST required');
}

$transactionId = '';
http_response_code(500);

try {
    if (!defined('APP_BOOTSTRAPPED')) {
        define('APP_BOOTSTRAPPED', true);
    }

    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../app/helpers.php';

    $apiKey = defined('SEPAY_WEBHOOK_API_KEY') ? SEPAY_WEBHOOK_API_KEY : '';
    if ($apiKey === '') {
        $apiKey = getenv('SEPAY_WEBHOOK_API_KEY');
    }

    if (!is_string($apiKey) || $apiKey === '') {
        throw new RuntimeException('SEPAY_WEBHOOK_API_KEY is missing');
    }

    $authorization = trim((string)($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
    if ($authorization === '') {
        $authorization = trim((string)($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''));
    }

    if ($authorization === '' && function_exists('getallheaders')) {
        $headers = array_change_key_case(getallheaders() ?: [], CASE_LOWER);
        $authorization = trim((string)($headers['authorization'] ?? ''));
    }

    if ($authorization === '') {
        sepayResponse(401, false, 'Authorization header missing');
    }

    if (!preg_match('/^Apikey[ \t]+([^\s]+)$/iD', $authorization, $key)) {
        sepayResponse(401, false, 'Invalid Authorization format');
    }

    if (!hash_equals($apiKey, $key[1])) {
        sepayResponse(401, false, 'Invalid API key');
    }

    $payload = file_get_contents('php://input', false, null, 0, 16385);
    if ($payload === false || strlen($payload) > 16384) {
        sepayResponse(400, false, 'Invalid webhook payload size');
    }

    $data = json_decode($payload, true, 32, JSON_BIGINT_AS_STRING);
    if (!is_array($data)) {
        sepayResponse(400, false, 'Invalid JSON payload');
    }

    if (($data['transferType'] ?? null) === 'out') {
        sepayResponse(200, true, 'Outgoing transfer ignored');
    }

    if (($data['transferType'] ?? null) !== 'in'
        || ($data['accountNumber'] ?? null) !== '3555441') {
        sepayResponse(422, false, 'Unexpected transfer type or receiving account');
    }

    $transactionId = $data['id'] ?? '';
    if ((!is_int($transactionId) && !is_string($transactionId))
        || !preg_match('/\A[1-9][0-9]{0,19}\z/', (string)$transactionId)) {
        $transactionId = '';
        sepayResponse(422, false, 'SePay transaction ID required');
    }

    $transactionId = (string)$transactionId;
    $amount = $data['transferAmount'] ?? null;

    if ((!is_int($amount) && !is_string($amount))
        || !preg_match('/\A[1-9][0-9]{0,13}\z/', (string)$amount)) {
        sepayResponse(422, false, 'Invalid transfer amount');
    }

    $amount = (string)$amount;
    $sepayReference = 'SEPAY:' . $transactionId;
    $bankReference = is_string($data['referenceCode'] ?? null)
        ? trim($data['referenceCode'])
        : '';
    $legacyReference = $bankReference !== '' && strlen($bankReference) <= 120
        ? $bankReference
        : $transactionId;

    $codePattern = '/NAP([1-9][0-9]{0,9})X(?:A[0-9A-F]{12}|[0-9A-F]{4})/i';
    $codes = [];

    foreach (['code', 'content', 'description'] as $field) {
        if (!is_string($data[$field] ?? null)) {
            continue;
        }

        // Giữ đúng độ dài của mã nạp khi ngân hàng nối thêm nội dung.
        preg_match_all($codePattern, $data[$field], $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $codes[strtoupper($match[0])] = $match[1];
        }
    }

    if (count($codes) > 20) {
        sepayResponse(422, false, 'Too many payment codes');
    }

    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException('PDO connection unavailable');
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    // Đối chiếu theo ID SePay. Với mã tham chiếu cũ, kiểm tra thêm mã nạp và chủ ví.
    $stmt = $pdo->prepare("
        SELECT user_id, payment_link_id, bank_reference
        FROM wallet_topups
        WHERE status = 'paid' AND bank_reference IN (?, ?)
    ");
    $stmt->execute([$sepayReference, $legacyReference]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $paidTopup) {
        if ($paidTopup['bank_reference'] === $sepayReference) {
            $pdo->commit();
            sepayResponse(200, true, 'Transaction already credited');
        }

        $storedCode = (string)$paidTopup['payment_link_id'];
        if (preg_match($codePattern, $storedCode, $paidCode)
            && ($codes[strtoupper($paidCode[0])] ?? null) === (string)$paidTopup['user_id']) {
            $pdo->commit();
            sepayResponse(200, true, 'Transaction already credited');
        }
    }

    if ($codes === []) {
        $pdo->rollBack();
        sepayResponse(422, false, 'No top-up code found');
    }

    $placeholders = implode(', ', array_fill(0, count($codes), '?'));
    $stmt = $pdo->prepare("
        SELECT id FROM wallet_topups
        WHERE user_id IN ($placeholders) AND payment_link_id IN ($placeholders)
        LIMIT 2
    ");
    $stmt->execute(array_merge(array_values($codes), array_keys($codes)));
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($ids) !== 1) {
        $pdo->rollBack();
        sepayResponse(409, false, 'Top-up code missing from database or ambiguous');
    }

    $stmt = $pdo->prepare("
        SELECT id, user_id, amount, status, bank_reference, payment_link_id
        FROM wallet_topups WHERE id = ? FOR UPDATE
    ");
    $stmt->execute([$ids[0]]);
    $topup = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$topup
        || ($codes[strtoupper((string)$topup['payment_link_id'])] ?? null) !== (string)$topup['user_id']) {
        $pdo->rollBack();
        sepayResponse(409, false, 'Top-up order changed or owner does not match');
    }

    if ($topup['status'] === 'paid' && $topup['bank_reference'] === $sepayReference) {
        $pdo->commit();
        sepayResponse(200, true, 'Transaction already credited');
    }

    if ($topup['status'] !== 'pending' || $topup['bank_reference'] !== null) {
        $pdo->rollBack();
        sepayResponse(409, false, 'Top-up is not pending');
    }

    if ((string)$topup['amount'] !== $amount) {
        $pdo->rollBack();
        sepayResponse(422, false, 'Transfer amount does not match top-up');
    }

    $userId = $topup['user_id'];

    $stmt = $pdo->prepare("
        UPDATE wallet_topups SET status = 'paid', bank_reference = ?, paid_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$sepayReference, $topup['id']]);

    $stmt = $pdo->prepare("
        INSERT INTO wallet_accounts (user_id, balance) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE balance = balance + ?
    ");
    $stmt->execute([$userId, $amount, $amount]);

    $stmt = $pdo->prepare('SELECT balance FROM wallet_accounts WHERE user_id = ?');
    $stmt->execute([$userId]);
    $balance = $stmt->fetchColumn();

    $description = 'Nạp tiền SePay ' . $topup['payment_link_id'];
    if ($bankReference !== '' && strlen($bankReference) <= 120) {
        $description .= ' | Mã ngân hàng ' . $bankReference;
    }

    $stmt = $pdo->prepare("
        INSERT INTO wallet_transactions
          (user_id, direction, amount, balance_after, type, reference_type, reference_id, description)
        VALUES (?, 'credit', ?, ?, 'topup', 'wallet_topups', ?, ?)
    ");
    $stmt->execute([$userId, $amount, $balance, $topup['id'], $description]);

    $pdo->commit();
    sepayResponse(200, true, 'Top-up credited');
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('SePay webhook [' . $transactionId . ']: ' . $e->getMessage());
    sepayResponse(500, false, 'Webhook processing failed');
}
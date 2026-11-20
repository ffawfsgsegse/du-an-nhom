<?php
declare(strict_types=1);

defined('APP_BOOTSTRAPPED') || exit;

function viettelpost_request(string $path, array $payload): array
{
    $token = defined('VIETTEL_POST_TOKEN') ? VIETTEL_POST_TOKEN : getenv('VIETTEL_POST_TOKEN');
    if (!is_string($token) || trim($token) === '') {
        throw new RuntimeException('Chưa cấu hình token Viettel Post.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('Cần bật tiện ích cURL của PHP để tính phí Viettel Post.');
    }

    $request = curl_init('https://partner.viettelpost.vn' . $path);
    curl_setopt_array($request, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Token: ' . trim($token),
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 10,
    ]);

    $body = curl_exec($request);
    $httpStatus = curl_getinfo($request, CURLINFO_HTTP_CODE);
    if (!is_string($body) || $httpStatus < 200 || $httpStatus >= 300) {
        error_log('Viettel Post quote endpoint returned HTTP ' . $httpStatus);
        throw new RuntimeException('Chưa thể lấy phí giao hàng từ Viettel Post. Vui lòng thử lại.');
    }

    $response = json_decode($body, true);
    if (!is_array($response) || !empty($response['error'])
        || (isset($response['status']) && (int)$response['status'] !== 200)) {
        throw new RuntimeException('Viettel Post không báo được phí cho kiện hàng này. Hãy kiểm tra địa chỉ và khối lượng.');
    }

    return is_array($response['data'] ?? null) ? $response['data'] : $response;
}

/**
 * Báo cước một kiện của một xưởng. Khối lượng tính bằng gram, tiền tính bằng VNĐ.
 * Nếu có số đo kiện thực tế, truyền length_cm, width_cm và height_cm.
 */
function viettelpost_quote(
    string $senderAddress,
    string $receiverAddress,
    int $weightGrams,
    int $goodsValue,
    bool $cashOnDelivery,
    array $dimensions = []
): array {
    $senderAddress = trim($senderAddress);
    $receiverAddress = trim($receiverAddress);
    if ($senderAddress === '' || $receiverAddress === '' || $weightGrams <= 0 || $goodsValue < 0) {
        throw new RuntimeException('Cần địa chỉ gửi, địa chỉ nhận và khối lượng kiện để tính phí giao hàng.');
    }

    $parcel = [
        'SENDER_ADDRESS' => $senderAddress,
        'RECEIVER_ADDRESS' => $receiverAddress,
        'PRODUCT_TYPE' => 'HH',
        'PRODUCT_WEIGHT' => $weightGrams,
        'PRODUCT_PRICE' => $goodsValue,
        'MONEY_COLLECTION' => $cashOnDelivery ? $goodsValue : 0,
    ];

    foreach (['length_cm' => 'PRODUCT_LENGTH', 'width_cm' => 'PRODUCT_WIDTH', 'height_cm' => 'PRODUCT_HEIGHT'] as $field => $apiField) {
        if ($dimensions === []) {
            break;
        }
        $size = filter_var($dimensions[$field] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($size === false) {
            throw new RuntimeException('Hãy nhập đủ ba kích thước kiện hàng theo centimet.');
        }
        $parcel[$apiField] = $size;
    }

    $available = viettelpost_request('/v2/order/getPriceAllNlp', $parcel + ['TYPE' => 1]);
    $service = null;
    $options = is_array($available['RESULT'] ?? null) ? $available['RESULT'] : [];
    foreach ($options as $option) {
        if (!is_array($option)) {
            continue;
        }
        $baseFee = filter_var($option['GIA_CUOC'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($baseFee === false || !is_string($option['MA_DV_CHINH'] ?? null)
            || trim($option['MA_DV_CHINH']) === '') {
            continue;
        }
        if ($service === null || $baseFee < $service['base_fee']) {
            $service = [
                'code' => $option['MA_DV_CHINH'],
                'name' => (string)($option['TEN_DICHVU'] ?? $option['MA_DV_CHINH']),
                'delivery_time' => (string)($option['THOI_GIAN'] ?? ''),
                'base_fee' => $baseFee,
            ];
        }
    }
    if ($service === null) {
        throw new RuntimeException('Viettel Post chưa có dịch vụ phù hợp với địa chỉ và kiện hàng này.');
    }

    // Lấy tổng cước của dịch vụ đã chọn để đưa phí thu hộ vào báo giá.
    $price = viettelpost_request('/v2/order/getPriceNlp', $parcel + [
        'NATIONAL_TYPE' => 1,
        'ORDER_SERVICE' => $service['code'],
        'ORDER_SERVICE_ADD' => '',
    ]);
    $fee = filter_var($price['MONEY_TOTAL'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($fee === false) {
        throw new RuntimeException('Viettel Post chưa trả về tổng cước hợp lệ.');
    }

    return [
        'fee' => $fee,
        'service_code' => $service['code'],
        'service_name' => $service['name'],
        'delivery_time' => $service['delivery_time'],
    ];
}

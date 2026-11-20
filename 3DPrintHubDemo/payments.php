<?php
declare(strict_types=1);

/**
 * Tạo URL mã QR VietQR động để nạp tiền ví / thanh toán
 */
function vietqr_image(string $bankName, string $accountNo, int $amount, string $addInfo, string $accountName = ''): string {
    // Sử dụng API VietQR công khai để tạo mã QR tự động
    $template = 'compact'; // hoặc print
    $bankCode = strtoupper(trim($bankName)); // Ví dụ: MB, VCB, TCB...
    
    // URL chuẩn của VietQR QuickLink
    return "https://img.vietqr.io/image/{$bankCode}-{$accountNo}-{$template}.png?amount={$amount}&addInfo=" . urlencode($addInfo) . "&accountName=" . urlencode($accountName);
}
<?php
declare(strict_types=1);

defined('APP_BOOTSTRAPPED') || exit;

function withdrawal_banks(): array
{
    return [
        'Vietcombank' => ['VCB', 'Ngoại thương', 'Ngân hàng TMCP Ngoại thương Việt Nam'],
        'MB Bank' => ['MB', 'Quân đội', 'MBBank', 'Ngân hàng TMCP Quân đội'],
        'Techcombank' => ['TCB', 'Kỹ thương', 'Ngân hàng TMCP Kỹ thương Việt Nam'],
        'BIDV' => ['Đầu tư và Phát triển', 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam'],
        'VietinBank' => ['ICB', 'Công thương', 'Ngân hàng TMCP Công thương Việt Nam'],
        'Agribank' => ['VBA', 'Nông nghiệp', 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam'],
        'ACB' => ['Á Châu', 'Ngân hàng TMCP Á Châu'],
        'VPBank' => ['VPB', 'Việt Nam Thịnh Vượng', 'Ngân hàng TMCP Việt Nam Thịnh Vượng'],
        'TPBank' => ['TPB', 'Tiên Phong', 'Ngân hàng TMCP Tiên Phong'],
        'Sacombank' => ['STB', 'Sài Gòn Thương Tín'],
        'HDBank' => ['HDB', 'Phát triển Thành phố Hồ Chí Minh'],
        'VIB' => ['Quốc tế', 'Ngân hàng TMCP Quốc tế Việt Nam'],
        'SHB' => ['Sài Gòn - Hà Nội'],
        'OCB' => ['Phương Đông'],
        'LPBank' => ['LPB', 'Lộc Phát', 'Liên Việt Post Bank', 'LienVietPostBank'],
        'MSB' => ['Hàng Hải', 'Maritime Bank'],
        'Eximbank' => ['EIB', 'Xuất Nhập khẩu'],
        'ABBANK' => ['ABB', 'An Bình'],
        'NCB' => ['Quốc Dân'],
        'BacABank' => ['BAB', 'Bắc Á'],
        'NamABank' => ['NAB', 'Nam Á'],
        'KienLongBank' => ['KLB', 'Kiên Long'],
        'SeABank' => ['SEAB', 'Đông Nam Á', 'SSB'],
        'VietBank' => ['VBB', 'Việt Nam Thương Tín'],
        'PVcomBank' => ['PVCB', 'Đại Chúng Việt Nam'],
    ];
}

function withdrawal_bank_key(string $value): string
{
    foreach ([
        'a'=>'àáảãạăằắẳẵặâầấẩẫậ', 'e'=>'èéẻẽẹêềếểễệ', 'i'=>'ìíỉĩị',
        'o'=>'òóỏõọôồốổỗộơờớởỡợ', 'u'=>'ùúủũụưừứửữự', 'y'=>'ỳýỷỹỵ', 'd'=>'đ',
    ] as $letter => $accents) {
        $value = preg_replace('/[' . $accents . ']/iu', $letter, $value) ?? $value;
    }

    return preg_replace('/[^a-z0-9]+/', '', strtolower($value)) ?? '';
}

function withdrawal_bank_name(string $typed): string
{
    $typed = trim($typed);
    $key = withdrawal_bank_key($typed);

    if ($key === '') {
        return $typed;
    }

    foreach (withdrawal_banks() as $name => $aliases) {
        foreach ([$name, ...$aliases] as $alias) {
            if ($key === withdrawal_bank_key($alias)) {
                return $name;
            }
        }
    }

    return $typed;
}

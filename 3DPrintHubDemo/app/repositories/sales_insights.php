<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;

/**
 * Xếp hạng theo SỐ LƯỢNG SẢN PHẨM đã bán trong đơn hoàn tất.
 * Chỉ so sánh sản phẩm đang mở bán, chưa bị xóa mềm.
 * LEFT JOIN giúp giữ sản phẩm chưa từng bán (0 chiếc) trong nhóm ít nhất.
 * Tiền hàng sử dụng đơn giá tại lúc đặt, không cộng tiền vận chuyển.
 */
function admin_product_sales_rank(bool $ascending = false, int $limit = 5): array
{
    $direction = $ascending ? 'ASC' : 'DESC';
    $limit = max(1, min(20, $limit));
    return dball('SELECT p.id, p.name, v.shop_name,
            COALESCE(s.units_sold, 0) AS units_sold,
            COALESCE(s.orders_count, 0) AS orders_count,
            COALESCE(s.product_revenue, 0) AS product_revenue
        FROM products p
        JOIN vendors v ON v.id = p.vendor_id
        LEFT JOIN (
            SELECT oi.product_id,
                   SUM(oi.quantity) AS units_sold,
                   COUNT(DISTINCT oi.order_id) AS orders_count,
                   SUM(oi.quantity * oi.unit_price) AS product_revenue
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id AND o.order_status = "completed"
            WHERE oi.product_id IS NOT NULL
            GROUP BY oi.product_id
        ) s ON s.product_id = p.id
        WHERE p.deleted_at IS NULL AND p.status = "active"
        ORDER BY units_sold ' . $direction . ', p.name ASC, p.id ASC
        LIMIT ' . $limit);
}

/**
 * Địa chỉ đang lưu ở orders.shipping_address (VARCHAR tự do) chứ chưa có
 * cột province_id. Chỉ nhận diện tỉnh/thành ở cuối địa chỉ; không đoán quận
 * hay phường thành một tỉnh để bảo đảm bảng xếp hạng không gây hiểu nhầm.
 * Danh sách gồm địa danh đang/đã được dùng trong địa chỉ VN.
 */
function admin_normalize_place(string $value): string
{
    $map = [];
    foreach ([
        'a' => 'àáạảãâầấậẩẫăằắặẳẵÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴ',
        'e' => 'èéẹẻẽêềếệểễÈÉẸẺẼÊỀẾỆỂỄ',
        'i' => 'ìíịỉĩÌÍỊỈĨ',
        'o' => 'òóọỏõôồốộổỗơờớợởỡÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠ',
        'u' => 'ùúụủũưừứựửữÙÚỤỦŨƯỪỨỰỬỮ',
        'y' => 'ỳýỵỷỹỲÝỴỶỸ',
        'd' => 'đĐ',
    ] as $latin => $letters) {
        foreach (preg_split('//u', $letters, -1, PREG_SPLIT_NO_EMPTY) as $letter) {
            $map[$letter] = $latin;
        }
    }
    $value = strtolower(strtr(trim($value), $map));
    return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $value));
}

function admin_province_from_shipping_address(string $address): ?string
{
    static $names = null;
    if ($names === null) {
        $provinces = [
            'Hà Nội', 'Hồ Chí Minh', 'Hải Phòng', 'Đà Nẵng', 'Cần Thơ', 'Huế',
            'An Giang', 'Bà Rịa - Vũng Tàu', 'Bắc Giang', 'Bắc Kạn', 'Bạc Liêu',
            'Bắc Ninh', 'Bến Tre', 'Bình Định', 'Bình Dương', 'Bình Phước',
            'Bình Thuận', 'Cà Mau', 'Cao Bằng', 'Đắk Lắk', 'Đắk Nông',
            'Điện Biên', 'Đồng Nai', 'Đồng Tháp', 'Gia Lai', 'Hà Giang',
            'Hà Nam', 'Hà Tĩnh', 'Hải Dương', 'Hậu Giang', 'Hòa Bình',
            'Hưng Yên', 'Khánh Hòa', 'Kiên Giang', 'Kon Tum', 'Lai Châu',
            'Lâm Đồng', 'Lạng Sơn', 'Lào Cai', 'Long An', 'Nam Định',
            'Nghệ An', 'Ninh Bình', 'Ninh Thuận', 'Phú Thọ', 'Phú Yên',
            'Quảng Bình', 'Quảng Nam', 'Quảng Ngãi', 'Quảng Ninh',
            'Quảng Trị', 'Sóc Trăng', 'Sơn La', 'Tây Ninh', 'Thái Bình',
            'Thái Nguyên', 'Thanh Hóa', 'Thừa Thiên Huế', 'Tiền Giang',
            'Trà Vinh', 'Tuyên Quang', 'Vĩnh Long', 'Vĩnh Phúc', 'Yên Bái',
        ];
        $names = [];
        foreach ($provinces as $province) {
            $names[admin_normalize_place($province)] = $province === 'Thừa Thiên Huế' ? 'Huế' : $province;
        }
        foreach ([
            'hcm' => 'Hồ Chí Minh', 'tphcm' => 'Hồ Chí Minh',
            'tp hcm' => 'Hồ Chí Minh', 'ho chi minh city' => 'Hồ Chí Minh',
            'sai gon' => 'Hồ Chí Minh', 'saigon' => 'Hồ Chí Minh',
            'hn' => 'Hà Nội', 'hanoi' => 'Hà Nội',
            'da nang city' => 'Đà Nẵng', 'danang' => 'Đà Nẵng',
            'can tho city' => 'Cần Thơ', 'hai phong city' => 'Hải Phòng',
            'thua thien hue' => 'Huế',
        ] as $alias => $province) {
            $names[$alias] = $province;
        }
        // Sắp xếp dài trước để kiểm tra hậu tố tránh khớp phần tên ngắn.
        uksort($names, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
    }

    $address = admin_normalize_place($address);
    if ($address === '') {
        return null;
    }
    // Bỏ hậu tố quốc gia nếu khách gõ thêm.
    $address = trim((string)preg_replace('/(?:^| )(?:viet nam|vietnam)$/', '', $address));
    foreach ($names as $key => $province) {
        if ($address === $key || str_ends_with($address, ' ' . $key)) {
            return $province;
        }
    }
    return null;
}

/** Trả về các tỉnh có đơn hoàn tất; tỉnh chưa xuất hiện không được coi là 0 đơn. */
function admin_location_sales_rank(int $limit = 5): array
{
    $limit = max(1, min(20, $limit));
    $groups = [];
    $unknownOrders = 0;
    $unknownRevenue = 0.0;
    // Gộp chuỗi địa chỉ trong SQL trước, chỉ chuyển dữ liệu tổng hợp lên PHP.
    $addresses = dball('SELECT shipping_address, COUNT(*) AS orders_count,
            COALESCE(SUM(total_amount), 0) AS revenue
        FROM orders
        WHERE order_status = "completed"
        GROUP BY shipping_address');
    foreach ($addresses as $row) {
        $orders = (int)$row['orders_count'];
        $revenue = (float)$row['revenue'];
        $province = admin_province_from_shipping_address((string)$row['shipping_address']);
        if ($province === null) {
            $unknownOrders += $orders;
            $unknownRevenue += $revenue;
            continue;
        }
        if (!isset($groups[$province])) {
            $groups[$province] = ['name' => $province, 'orders_count' => 0, 'revenue' => 0.0];
        }
        $groups[$province]['orders_count'] += $orders;
        $groups[$province]['revenue'] += $revenue;
    }
    $rows = array_values($groups);
    $top = $rows;
    usort($top, static fn(array $a, array $b): int =>
        ($b['orders_count'] <=> $a['orders_count']) ?: ($b['revenue'] <=> $a['revenue']) ?: strcmp($a['name'], $b['name']));
    $bottom = $rows;
    usort($bottom, static fn(array $a, array $b): int =>
        ($a['orders_count'] <=> $b['orders_count']) ?: ($a['revenue'] <=> $b['revenue']) ?: strcmp($a['name'], $b['name']));
    return [
        'top' => array_slice($top, 0, $limit),
        'bottom' => array_slice($bottom, 0, $limit),
        'unknown_orders' => $unknownOrders,
        'unknown_revenue' => $unknownRevenue,
        'location_count' => count($rows),
    ];
}

/** Dữ liệu chung cho Dashboard, trang Thống kê và API làm mới 30 giây. */
function admin_sales_insights(): array
{
    return [
        'top_products' => admin_product_sales_rank(false),
        'bottom_products' => admin_product_sales_rank(true),
        'locations' => admin_location_sales_rank(),
    ];
}

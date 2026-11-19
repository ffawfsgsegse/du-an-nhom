<?php

declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;
function vendor_action(string $action): void
{
    global $pdo;
    $vendor = approved_vendor();
    if ($action === 'vendor_material_add') {
        $name = trim((string)($_POST['name'] ?? ''));
        $price = safe_price($_POST['price_per_cm3'] ?? 0);
        if (strlen($name) < 2 || strlen($name) > 100) throw new RuntimeException('Tên vật liệu không hợp lệ.');
        execsql('INSERT INTO materials(vendor_id,name,price_per_cm3,status) VALUES(?,?,?,1)', [$vendor['id'], $name, $price]);
        flash('Đã thêm vật liệu.');
        go('vendor_products');
    }
    if ($action === 'vendor_order_status') {
        $oid = positive_int($_POST['order_id'] ?? 0);
        $next = (string)($_POST['next_status'] ?? '');
        $map = ['pending' => 'printing', 'printing' => 'shipping', 'shipping' => 'completed'];
        $pdo->beginTransaction();
        try {
            $o = dbone('SELECT * FROM orders WHERE id=? AND vendor_id=? FOR UPDATE', [$oid, $vendor['id']]);
            if (!$o || !isset($map[$o['order_status']]) || $map[$o['order_status']] !== $next) throw new RuntimeException('Không thể chuyển trạng thái đơn theo yêu cầu.');
            if ($o['payment_method'] === 'banking') {
                $pay = dbone('SELECT status FROM order_payments WHERE order_id=?', [$oid]);
                if (!$pay || $pay['status'] !== 'confirmed') throw new RuntimeException('Đơn QR DEMO chưa được xác nhận.');
            }
            execsql('UPDATE orders SET order_status=?,completed_at=IF(?="completed",NOW(),completed_at) WHERE id=?', [$next, $next, $oid]);
            if ($next === 'completed') {
                if ($o['payment_method'] === 'cod') execsql('INSERT IGNORE INTO cod_collections(order_id,collected_by,note) VALUES(?,?,"DEMO: người bán xác nhận đã thu tiền COD")', [$oid, user()['id']]);
                $fee = round((float)$o['total_amount'] * 0.05);
                $net = (float)$o['total_amount'] - $fee;
                execsql('INSERT IGNORE INTO vendor_earnings(order_id,vendor_id,gross_amount,platform_fee,net_amount,status,eligible_at) VALUES(?,?,?,?,?,"eligible",NOW())', [$oid, $vendor['id'], $o['total_amount'], $fee, $net]);
            }
            $pdo->commit();
            flash('Đã chuyển đơn sang ' . order_status_label($next) . '.');
            go('vendor_orders');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
    if ($action === 'vendor_profile_save') {
        $name = trim((string)($_POST['shop_name'] ?? ''));
        $phone = trim((string)($_POST['contact_phone'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $tech = implode(', ', array_intersect((array)($_POST['printer_tech'] ?? []), ['FDM', 'SLA/DLP Resin', 'SLS', 'MJF']));
        $capacity = trim((string)($_POST['printer_capacity'] ?? ''));
        $volume = trim((string)($_POST['max_build_volume'] ?? ''));
        $desc = trim((string)($_POST['description'] ?? ''));
        $bankName = trim((string)($_POST['bank_name'] ?? ''));
        $bankAcc = trim((string)($_POST['bank_account'] ?? ''));
        $bankHolder = strtoupper(trim((string)($_POST['bank_account_holder'] ?? '')));
        $tax = $vendor['tax_or_cccd'] ?: trim((string)($_POST['tax_or_cccd'] ?? ''));
        if (strlen($tax) < 9 || strlen($tax) > 30) throw new RuntimeException('Số CCCD hoặc Mã số thuế từ 9–30 ký tự.');

        if (strlen($name) < 3 || strlen($name) > 150) throw new RuntimeException('Tên gian hàng từ 3–150 ký tự.');
        if ($phone !== '' && !preg_match('/^[+0-9 .-]{8,20}$/', $phone)) throw new RuntimeException('Số điện thoại liên hệ không hợp lệ.');
        if (strlen($address) < 5 || strlen($address) > 255) throw new RuntimeException('Địa chỉ xưởng không hợp lệ.');
        if ($tech === '') throw new RuntimeException('Hãy chọn ít nhất một công nghệ in 3D.');
        if ($bankName === '' || strlen($bankAcc) < 6 || strlen($bankHolder) < 3) throw new RuntimeException('Vui lòng nhập đầy đủ thông tin tài khoản ngân hàng.');

        execsql(
            'UPDATE vendors SET shop_name=?,address=?,contact_phone=?,printer_capacity=?,printer_tech=?,max_build_volume=?,description=?,tax_or_cccd=?,bank_name=?,bank_account=?,bank_account_holder=? WHERE id=?',
            [$name, $address, $phone ?: null, $capacity, $tech, $volume ?: null, $desc ?: null, $tax, $bankName, $bankAcc, $bankHolder, $vendor['id']]
        );
        flash('Đã cập nhật hồ sơ gian hàng.');
        go('vendor_profile');
    }
}

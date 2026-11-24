<?php

declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;

function checkout_action(string $action): void
{
    global $pdo;
    $u = need_role('client', 'vendor');

    if ($action === 'place_order') {
        $name = trim((string)($_POST['receiver_name'] ?? ''));
        $phone = trim((string)($_POST['receiver_phone'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $option = (string)($_POST['payment_option'] ?? 'cod');
        $method = $option === 'cod' ? 'cod' : 'banking';

        if (strlen($name) < 2 || strlen($name) > 100 || !preg_match('/^[+0-9 .-]{8,20}$/', $phone) || strlen($address) < 10 || strlen($address) > 255) {
            throw new RuntimeException('Hãy nhập đúng tên, số điện thoại và địa chỉ nhận hàng.');
        }
        if (!in_array($option, ['cod', 'demo_bank', 'demo_momo'], true)) {
            throw new RuntimeException('Phương thức thanh toán không hợp lệ.');
        }

        $cart = $_SESSION['cart'] ?? [];
        if (!$cart) throw new RuntimeException('Giỏ hàng đang trống.');

        ksort($cart, SORT_STRING);
        $pdo->beginTransaction();
        try {
            $groups = [];
            foreach ($cart as $cartKey => $qty) {
                $qty = positive_int($qty, 100);
                $keyStr = (string)$cartKey;
                $pid = str_contains($keyStr, '_') ? (int)explode('_', $keyStr, 2)[0] : (int)$keyStr;
                $vid = str_contains($keyStr, '_') ? (int)explode('_', $keyStr, 2)[1] : 0;

                $p = dbone('SELECT p.id,p.vendor_id,p.name,p.price,p.stock,p.status,p.deleted_at,v.status vendor_status,u.status user_status FROM products p JOIN vendors v ON v.id=p.vendor_id JOIN users u ON u.id=v.user_id WHERE p.id=? FOR UPDATE', [$pid]);
                if (!$p || $p['status'] !== 'active' || $p['deleted_at'] !== null || $p['vendor_status'] !== 'approved' || !(int)$p['user_status']) {
                    throw new RuntimeException('Một sản phẩm đã ngừng bán. Hãy kiểm tra lại giỏ.');
                }

                $unitPrice = (float)$p['price'];
                $variantInfo = null;
                if ($vid > 0) {
                    $vr = dbone('SELECT * FROM product_variants WHERE id=? AND product_id=? FOR UPDATE', [$vid, $pid]);
                    if (!$vr || $qty > (int)$vr['stock']) {
                        throw new RuntimeException('Một phân loại sản phẩm đã hết hàng. Hãy kiểm tra lại giỏ.');
                    }
                    $unitPrice = (float)$vr['price'];
                    $variantInfo = $vr['material_name'] . ' · ' . $vr['color'] . ' · ' . $vr['size_label'];
                } else {
                    if ($qty > (int)$p['stock']) {
                        throw new RuntimeException('Một sản phẩm đã hết hàng. Hãy kiểm tra lại giỏ.');
                    }
                }

                $groups[$p['vendor_id']][] = [
                    'p' => $p,
                    'variant_id' => $vid > 0 ? $vid : null,
                    'variant_info' => $variantInfo,
                    'unit_price' => $unitPrice,
                    'qty' => $qty,
                ];
            }

            $orders = [];
            foreach ($groups as $vendor => $items) {
                $total = 0;
                foreach ($items as $row) $total += $row['unit_price'] * $row['qty'];
                $code = '3D' . date('ymd') . strtoupper(bin2hex(random_bytes(5)));

                execsql('INSERT INTO orders(order_code,user_id,vendor_id,receiver_name,receiver_phone,shipping_address,total_amount,payment_method,order_status) VALUES(?,?,?,?,?,?,?,?, "pending")', [$code, $u['id'], $vendor, $name, $phone, $address, $total, $method]);
                $oid = (int)$pdo->lastInsertId();
                $orders[] = $oid;

                foreach ($items as $row) {
                    execsql(
                        'INSERT INTO order_items(order_id,product_id,variant_id,variant_info,quantity,unit_price) VALUES(?,?,?,?,?,?)',
                        [$oid, $row['p']['id'], $row['variant_id'], $row['variant_info'], $row['qty'], $row['unit_price']]
                    );
                    if ($row['variant_id']) {
                        execsql('UPDATE product_variants SET stock=stock-? WHERE id=? AND stock>=?', [$row['qty'], $row['variant_id'], $row['qty']]);
                    }
                    execsql('UPDATE products SET stock=GREATEST(0, stock-?) WHERE id=?', [$row['qty'], $row['p']['id']]);
                }

                if ($method === 'banking') {
                    execsql('INSERT INTO order_payments(order_id,status,payment_provider,bank_name,bank_account,bank_account_holder) VALUES(?,"unpaid",?,"DEMO","DEMO","KHÔNG CHUYỂN TIỀN")', [$oid, $option]);
                    execsql('INSERT INTO demo_payments(order_id,provider,status,expires_at) VALUES(?, ?, "pending",DATE_ADD(NOW(),INTERVAL 15 MINUTE))', [$oid, $option === 'demo_momo' ? 'momo' : 'bank_qr']);
                }
            }

            $pdo->commit();
            unset($_SESSION['cart']);
            flash(count($orders) . ' đơn hàng đã được tạo thành công.' . ($method === 'banking' ? ' Vào lịch sử đơn để mở trang MoMo/QR DEMO.' : ''));
            go('my_orders');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    if ($action === 'demo_confirm') {
        if (!in_array(strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]), ['localhost', '127.0.0.1'], true)) {
            throw new RuntimeException('QR DEMO chỉ dùng khi chạy localhost.');
        }
        $oid = positive_int($_POST['order_id'] ?? 0);
        $pdo->beginTransaction();
        try {
            $o = dbone('SELECT * FROM orders WHERE id=? AND user_id=? AND payment_method="banking" FOR UPDATE', [$oid, $u['id']]);
            $d = dbone('SELECT * FROM demo_payments WHERE order_id=? FOR UPDATE', [$oid]);
            if (!$o || !$d || $o['order_status'] === 'cancelled') throw new RuntimeException('Không thể xác nhận đơn này.');
            if ($d['status'] === 'pending') {
                if (strtotime($d['expires_at']) < time()) throw new RuntimeException('Mã DEMO hết hạn. Không có giao dịch thực tế.');
                execsql('UPDATE demo_payments SET status="paid",confirmed_at=NOW() WHERE id=? AND status="pending"', [$d['id']]);
                execsql('UPDATE order_payments SET status="confirmed" WHERE order_id=? AND payment_provider IN ("demo_bank","demo_momo")', [$oid]);
            }
            $pdo->commit();
            flash('Đã ghi nhận thanh toán DEMO (không có chuyển tiền thật).');
            go('my_orders');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    if ($action === 'cancel_order') {
        $oid = positive_int($_POST['order_id'] ?? 0);
        $pdo->beginTransaction();
        try {
            $o = dbone('SELECT * FROM orders WHERE id=? AND user_id=? FOR UPDATE', [$oid, $u['id']]);
            if (!$o || $o['order_status'] !== 'pending') throw new RuntimeException('Chỉ hủy được đơn đang chờ xác nhận.');
            $pay = dbone('SELECT status FROM order_payments WHERE order_id=?', [$oid]);
            if ($pay && $pay['status'] === 'confirmed') throw new RuntimeException('Đơn đã thanh toán DEMO. Liên hệ Admin để xử lý.');

            execsql('UPDATE orders SET order_status="cancelled" WHERE id=?', [$oid]);
            foreach (order_items($oid) as $row) {
                if (!empty($row['variant_id'])) {
                    execsql('UPDATE product_variants SET stock=stock+? WHERE id=?', [$row['quantity'], $row['variant_id']]);
                }
                if (!empty($row['product_id'])) {
                    execsql('UPDATE products SET stock=stock+? WHERE id=?', [$row['quantity'], $row['product_id']]);
                }
            }
            $pdo->commit();
            flash('Đã hủy đơn và hoàn lại số lượng tồn kho.');
            go('my_orders');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}

<?php
declare(strict_types=1);

defined('APP_BOOTSTRAPPED') || exit;
require_once __DIR__ . '/../viettelpost.php';
require_once __DIR__ . '/../checkout_delivery.php';

/** Giá lưu trong MySQL và số dư ví được đối chiếu theo đơn vị đồng. */
function checkout_dong(mixed $value): int
{
    if ((!is_string($value) && !is_int($value))
        || !preg_match('/\A([0-9]{1,14})(?:\.00)?\z/', (string)$value, $parts)) {
        throw new RuntimeException('Giá hoặc số dư phải là số nguyên đồng.');
    }
    return (int)$parts[1];
}

function checkout_post_text(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? trim($value) : '';
}

/** Tách giỏ theo xưởng và lập một kiện cho mỗi xưởng. */
function checkout_groups(array $cart, bool $lock = false): array
{
    if ($cart === []) {
        throw new RuntimeException('Giỏ hàng đang trống.');
    }

    $quantities = [];
    foreach ($cart as $productId => $quantity) {
        $quantities[positive_int($productId, 2147483647)] = positive_int($quantity, 100);
    }
    ksort($quantities, SORT_NUMERIC);

    $placeholders = implode(',', array_fill(0, count($quantities), '?'));
    $rows = dball(
        "SELECT p.id, p.vendor_id, p.name, p.price, p.stock, p.status, p.deleted_at,
                p.shipping_weight_grams, p.shipping_length_cm, p.shipping_width_cm, p.shipping_height_cm,
                v.shop_name, v.address, v.status AS vendor_status, u.status AS seller_status
         FROM products p JOIN vendors v ON v.id = p.vendor_id JOIN users u ON u.id = v.user_id
         WHERE p.id IN ($placeholders) ORDER BY p.id" . ($lock ? ' FOR UPDATE' : ''),
        array_keys($quantities)
    );
    if (count($rows) !== count($quantities)) {
        throw new RuntimeException('Có sản phẩm đã bị gỡ. Hãy kiểm tra lại giỏ.');
    }

    $groups = [];
    foreach ($rows as $row) {
        $quantity = $quantities[(int)$row['id']];
        if ($row['status'] !== 'active' || $row['deleted_at'] !== null
            || $row['vendor_status'] !== 'approved' || !(int)$row['seller_status']
            || $quantity > (int)$row['stock']) {
            throw new RuntimeException('Sản phẩm “' . $row['name'] . '” hiện không đủ tồn kho hoặc đã ngừng bán.');
        }

        $package = [
            'weight_grams' => (int)$row['shipping_weight_grams'],
            'length_cm' => (int)$row['shipping_length_cm'],
            'width_cm' => (int)$row['shipping_width_cm'],
            'height_cm' => (int)$row['shipping_height_cm'],
        ];
        if (min($package) < 1 || trim((string)$row['address']) === '') {
            throw new RuntimeException('Xưởng cần cập nhật địa chỉ gửi và thông số đóng gói của “' . $row['name'] . '”.');
        }

        $vendor = (int)$row['vendor_id'];
        $groups[$vendor] ??= [
            'shop_name' => $row['shop_name'], 'address' => trim($row['address']), 'items' => [],
            'subtotal' => 0, 'weight_grams' => 0, 'length_cm' => 0, 'width_cm' => 0, 'height_cm' => 0,
        ];
        $group =& $groups[$vendor];
        $price = checkout_dong($row['price']);
        $group['items'][] = [
            'id' => (int)$row['id'], 'name' => $row['name'], 'quantity' => $quantity, 'price' => $price,
        ];
        $group['subtotal'] += $price * $quantity;
        $group['weight_grams'] += $package['weight_grams'] * $quantity;
        $group['length_cm'] = max($group['length_cm'], $package['length_cm']);
        $group['width_cm'] = max($group['width_cm'], $package['width_cm']);
        $group['height_cm'] += $package['height_cm'] * $quantity;
        unset($group);
    }
    return $groups;
}

function checkout_action(string $action): void
{
    global $pdo;
    $user = need_role('client', 'vendor');
    if ($action === 'place_order') {
        $name = checkout_post_text('receiver_name');
        $phone = checkout_post_text('receiver_phone');
        $delivery = checkout_delivery_fields($_POST);
        $address = checkout_delivery_address($delivery);
        $note = checkout_post_text('shipping_note');
        $method = checkout_post_text('payment_option', 'cod');
        if (strlen($name) < 2 || strlen($name) > 100 || !preg_match('/^[+0-9 .-]{8,20}$/D', $phone)) {
            throw new RuntimeException('Hãy nhập đúng tên và số điện thoại người nhận.');
        }
        if ($delivery['shipping_street'] === '' || $delivery['shipping_ward'] === ''
            || $delivery['shipping_province'] === '' || !preg_match('/\A.{1,255}\z/u', $address)) {
            throw new RuntimeException('Hãy nhập số nhà và đường, phường/xã, tỉnh/thành phố. Địa chỉ tối đa 255 ký tự.');
        }
        if (!preg_match('/\A.{0,255}\z/us', $note)) {
            throw new RuntimeException('Ghi chú giao hàng tối đa 255 ký tự.');
        }
        if (!in_array($method, ['cod', 'wallet'], true)) {
            throw new RuntimeException('Hãy chọn COD hoặc thanh toán bằng ví.');
        }
        $userId = (int)$user['id'];
        $cart = $_SESSION['cart'] ?? [];

        if (checkout_post_text('checkout_step') !== 'confirm') {
            unset($_SESSION['checkout_quote']);
            $groups = checkout_groups($cart);
            $rates = [];
            $total = 0;
            foreach ($groups as $vendor => $group) {
                if ($group['subtotal'] > 9999999999) {
                    throw new RuntimeException('Tổng một đơn vượt giới hạn. Hãy giảm số lượng sản phẩm.');
                }
                $rate = viettelpost_quote(
                    $group['address'], $address, $group['weight_grams'], $group['subtotal'], $method === 'cod', $group
                );
                $groupTotal = $group['subtotal'] + $rate['fee'];
                if ($groupTotal > 9999999999) {
                    throw new RuntimeException('Tổng một đơn vượt giới hạn. Hãy giảm số lượng sản phẩm.');
                }
                $rates[$vendor] = $rate;
                $total += $groupTotal;
            }
            $_SESSION['checkout_quote'] = [
                'id' => bin2hex(random_bytes(16)), 'created_at' => time(), 'user_id' => $userId,
                'name' => $name, 'phone' => $phone, 'address' => $address, 'method' => $method,
                'address_fields' => $delivery, 'note' => $note,
                'groups' => $groups, 'rates' => $rates, 'total' => $total,
            ];
            return;
        }

        $quote = $_SESSION['checkout_quote'] ?? null;
        if (!$quote || $quote['user_id'] !== $userId || time() - $quote['created_at'] > 600
            || !hash_equals($quote['id'], checkout_post_text('quote_id'))
            || $quote['address'] !== $address || $quote['method'] !== $method) {
            throw new RuntimeException('Thông tin giao hàng đã đổi hoặc báo giá đã hết hiệu lực. Hãy tính lại phí.');
        }

        $pdo->beginTransaction();
        try {
            $groups = checkout_groups($cart, true);
            if ($groups !== $quote['groups']) {
                unset($_SESSION['checkout_quote']);
                throw new RuntimeException('Giỏ hàng, giá hoặc thông số kiện đã thay đổi. Hãy tính lại phí.');
            }

            $balance = 0;
            if ($method === 'wallet') {
                $account = dbone('SELECT balance FROM wallet_accounts WHERE user_id = ? FOR UPDATE', [$userId]);
                $balance = checkout_dong($account['balance'] ?? 0);
                if ($balance < $quote['total']) {
                    throw new RuntimeException('Ví không đủ số dư để thanh toán. Bạn có thể nạp thêm hoặc chọn COD.');
                }
            }

            foreach ($groups as $vendor => $group) {
                $rate = $quote['rates'][$vendor];
                $total = $group['subtotal'] + $rate['fee'];
                $code = '3D' . date('ymd', $quote['created_at'])
                    . strtoupper(substr(hash('sha256', $quote['id'] . ':' . $vendor), 0, 20));
                execsql(
                    "INSERT INTO orders
                     (order_code,user_id,vendor_id,receiver_name,receiver_phone,shipping_address,shipping_note,
                      shipping_fee,shipping_service_code,total_amount,payment_method,order_status)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,'pending')",
                    [$code, $userId, $vendor, $name, $phone, $address, $note !== '' ? $note : null,
                     $rate['fee'], $rate['service_code'], $total, $method]
                );
                $orderId = (int)$pdo->lastInsertId();

                foreach ($group['items'] as $item) {
                    execsql(
                        'INSERT INTO order_items(order_id,product_id,quantity,unit_price) VALUES(?,?,?,?)',
                        [$orderId, $item['id'], $item['quantity'], $item['price']]
                    );
                    if (execsql('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?',
                        [$item['quantity'], $item['id'], $item['quantity']]) !== 1) {
                        throw new RuntimeException('Tồn kho vừa thay đổi. Hãy kiểm tra lại giỏ.');
                    }
                }

                if ($method === 'wallet') {
                    if ($total > 0) {
                        if (execsql('UPDATE wallet_accounts SET balance = balance - ? WHERE user_id = ? AND balance >= ?',
                            [$total, $userId, $total]) !== 1) {
                            throw new RuntimeException('Ví không đủ số dư để thanh toán.');
                        }
                        $balance -= $total;
                    }
                    execsql(
                        "INSERT INTO wallet_transactions
                         (user_id,direction,amount,balance_after,type,reference_type,reference_id,description)
                         VALUES (?,'debit',?,?,'order_payment','orders',?,?)",
                        [$userId, $total, $balance, $orderId, 'Thanh toán đơn ' . $code]
                    );
                }
            }

            $pdo->commit();
            unset($_SESSION['cart'], $_SESSION['checkout_quote']);
            $message = $method === 'wallet'
                ? 'Đã thanh toán ' . money($quote['total']) . ' bằng ví.'
                : 'Đã đặt hàng COD. Số tiền thu khi giao là ' . money($quote['total']) . '.';
            flash(count($groups) . ' đơn hàng đã được tạo. ' . $message);
            go('my_orders');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    if ($action === 'demo_confirm') {
        if (!in_array(strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]), ['localhost','127.0.0.1'], true)) {
            throw new RuntimeException('QR DEMO chỉ dùng khi chạy localhost.');
        }
        $orderId = positive_int($_POST['order_id'] ?? 0);
        $pdo->beginTransaction();
        try {
            $order = dbone("SELECT * FROM orders WHERE id=? AND user_id=? AND payment_method='banking' FOR UPDATE", [$orderId, $user['id']]);
            $demo = dbone('SELECT * FROM demo_payments WHERE order_id=? FOR UPDATE', [$orderId]);
            if (!$order || !$demo || $order['order_status'] === 'cancelled') {
                throw new RuntimeException('Không thể xác nhận đơn này.');
            }
            if ($demo['status'] === 'pending') {
                if (strtotime($demo['expires_at']) < time()) {
                    throw new RuntimeException('Mã DEMO hết hạn.');
                }
                execsql("UPDATE demo_payments SET status='paid',confirmed_at=NOW() WHERE id=? AND status='pending'", [$demo['id']]);
                execsql("UPDATE order_payments SET status='confirmed' WHERE order_id=? AND payment_provider IN ('demo_bank','demo_momo')", [$orderId]);
            }
            $pdo->commit();
            flash('Đã ghi nhận thanh toán DEMO.');
            go('my_orders');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    if ($action === 'cancel_order') {
        $orderId = positive_int($_POST['order_id'] ?? 0);
        $pdo->beginTransaction();
        try {
            $order = dbone('SELECT * FROM orders WHERE id=? AND user_id=? FOR UPDATE', [$orderId, $user['id']]);
            if (!$order || $order['order_status'] !== 'pending') {
                throw new RuntimeException('Chỉ hủy được đơn đang chờ xác nhận.');
            }
            $payment = dbone('SELECT status FROM order_payments WHERE order_id=?', [$orderId]);
            if ($payment && $payment['status'] === 'confirmed' && $order['payment_method'] !== 'wallet') {
                throw new RuntimeException('Đơn đã thanh toán. Liên hệ Admin để xử lý.');
            }

            execsql("UPDATE orders SET order_status='cancelled' WHERE id=?", [$orderId]);
            $items = order_items($orderId);
            usort($items, static fn (array $a, array $b): int => (int)$a['product_id'] <=> (int)$b['product_id']);
            foreach ($items as $item) {
                if ($item['product_id']) {
                    execsql('UPDATE products SET stock=stock+? WHERE id=?', [$item['quantity'], $item['product_id']]);
                }
            }

            if ($order['payment_method'] === 'wallet') {
                $charge = dbone(
                    "SELECT amount FROM wallet_transactions WHERE user_id=? AND direction='debit'
                     AND type='order_payment' AND reference_type='orders' AND reference_id=?",
                    [$user['id'], $orderId]
                );
                if (!$charge || checkout_dong($charge['amount']) !== checkout_dong($order['total_amount'])) {
                    throw new RuntimeException('Không tìm thấy giao dịch ví của đơn này để hoàn tiền.');
                }
                $refund = checkout_dong($charge['amount']);
                execsql(
                    'INSERT INTO wallet_accounts(user_id,balance) VALUES(?,?) ON DUPLICATE KEY UPDATE balance=balance+?',
                    [$user['id'], $refund, $refund]
                );
                $account = dbone('SELECT balance FROM wallet_accounts WHERE user_id=?', [$user['id']]);
                execsql(
                    "INSERT INTO wallet_transactions
                     (user_id,direction,amount,balance_after,type,reference_type,reference_id,description)
                     VALUES (?,'credit',?,?,'order_refund','orders',?,?)",
                    [$user['id'], $refund, $account['balance'], $orderId, 'Hoàn tiền đơn ' . $order['order_code']]
                );
            }
            $pdo->commit();
            flash($order['payment_method'] === 'wallet' ? 'Đã hủy đơn và hoàn tiền vào ví.' : 'Đã hủy đơn và hoàn lại tồn kho.');
            go('my_orders');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}

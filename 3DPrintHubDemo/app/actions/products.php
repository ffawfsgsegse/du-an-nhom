<?php

declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;

function product_action(string $action): void
{
    global $pdo;
    $admin = str_starts_with($action, 'admin_');
    if ($admin) need_role('admin');
    else $seller = approved_vendor();

    $id = (int)($_POST['id'] ?? 0);

    if (in_array($action, ['vendor_product_delete', 'admin_product_delete'], true)) {
        $p = dbone('SELECT * FROM products WHERE id=? AND deleted_at IS NULL', [$id]);
        if (!$p || (!$admin && (int)$p['vendor_id'] !== (int)$seller['id'])) throw new RuntimeException('Không tìm thấy sản phẩm có quyền quản lý.');
        execsql('UPDATE products SET deleted_at=NOW(),status="hidden" WHERE id=?', [$id]);
        flash('Đã xóa mềm sản phẩm. Dữ liệu đơn hàng được giữ lại.');
        go($admin ? 'admin_products' : 'vendor_products');
    }

    if (in_array($action, ['admin_product_approve', 'admin_product_hide', 'vendor_product_hide'], true)) {
        $p = dbone('SELECT * FROM products WHERE id=? AND deleted_at IS NULL', [$id]);
        if (!$p || (!$admin && (int)$p['vendor_id'] !== (int)$seller['id'])) throw new RuntimeException('Không tìm thấy sản phẩm có quyền xử lý.');
        if ($action === 'admin_product_approve') {
            execsql('UPDATE products SET status="active", reject_reason=NULL WHERE id=?', [$id]);
        } else {
            execsql('UPDATE products SET status="hidden" WHERE id=?', [$id]);
        }
        flash('Đã cập nhật trạng thái sản phẩm.');
        go($admin ? 'admin_products' : 'vendor_products');
    }

    if ($action === 'admin_product_reject') {
        $p = dbone('SELECT * FROM products WHERE id=? AND deleted_at IS NULL', [$id]);
        if (!$p) throw new RuntimeException('Không tìm thấy sản phẩm.');
        $reason = trim((string)($_POST['reject_reason'] ?? ''));
        if (strlen($reason) < 5 || strlen($reason) > 255) {
            throw new RuntimeException('Vui lòng nhập lý do từ chối rõ ràng (5–255 ký tự).');
        }
        execsql('UPDATE products SET status="rejected", reject_reason=? WHERE id=?', [$reason, $id]);
        flash('Đã từ chối sản phẩm và gửi lý do cho người bán.');
        go('admin_products');
    }

    if (in_array($action, ['vendor_product_save', 'admin_product_save'], true)) {
        $vendor = $admin ? positive_int($_POST['vendor_id'] ?? 0) : (int)$seller['id'];
        $name = trim((string)($_POST['name'] ?? ''));
        $category = positive_int($_POST['category_id'] ?? 0);
        $material = positive_int($_POST['material_id'] ?? 0);
        $dimensions = trim((string)($_POST['dimensions'] ?? ''));

        $weightGram = (int)($_POST['weight_gram'] ?? 0);
        $pkgLength = (float)($_POST['pkg_length'] ?? 0);
        $pkgWidth = (float)($_POST['pkg_width'] ?? 0);
        $pkgHeight = (float)($_POST['pkg_height'] ?? 0);

        if (strlen($name) < 3 || strlen($name) > 200 || strlen($dimensions) > 100) {
            throw new RuntimeException('Tên hoặc kích thước sản phẩm không hợp lệ.');
        }
        if ($weightGram < 1 || $weightGram > 50000 || $pkgLength <= 0 || $pkgLength > 200 || $pkgWidth <= 0 || $pkgWidth > 200 || $pkgHeight <= 0 || $pkgHeight > 200) {
            throw new RuntimeException('Cân nặng (1–50.000g) và kích thước đóng gói Dài × Rộng × Cao (1–200cm) không hợp lệ.');
        }
        if (!dbone('SELECT id FROM vendors WHERE id=? AND status="approved"', [$vendor])) throw new RuntimeException('Gian hàng chưa được duyệt.');
        if (!dbone('SELECT id FROM categories WHERE id=? AND status=1', [$category])) throw new RuntimeException('Danh mục không hợp lệ.');
        if (!dbone('SELECT id FROM materials WHERE id=? AND vendor_id=? AND status=1', [$material, $vendor])) throw new RuntimeException('Vật liệu không thuộc gian hàng đã chọn.');

        $rawVariants = $_POST['variants'] ?? [];
        if (!is_array($rawVariants) || count($rawVariants) < 1) {
            throw new RuntimeException('Sản phẩm phải có ít nhất 1 phân loại thuộc tính.');
        }

        $cleanVariants = [];
        $seenCombos = [];
        foreach ($rawVariants as $row) {
            $vMat = trim((string)($row['material_name'] ?? ''));
            $vCol = trim((string)($row['color'] ?? ''));
            $vSize = trim((string)($row['size_label'] ?? 'Tiêu chuẩn'));
            $vPrice = safe_price($row['price'] ?? 0);
            $vStock = (int)($row['stock'] ?? -1);
            $vId = (int)($row['id'] ?? 0);

            if (strlen($vMat) < 1 || strlen($vMat) > 80 || strlen($vCol) < 1 || strlen($vCol) > 60 || strlen($vSize) < 1 || strlen($vSize) > 80) {
                throw new RuntimeException('Vật liệu, màu sắc và kích thước của từng phân loại không được để trống.');
            }
            if ($vStock < 0 || $vStock > 100000) {
                throw new RuntimeException('Tồn kho của phân loại không hợp lệ.');
            }

            $comboKey = mb_strtolower($vMat . '|' . $vCol . '|' . $vSize);
            if (isset($seenCombos[$comboKey])) {
                throw new RuntimeException("Phân loại '{$vMat} - {$vCol} - {$vSize}' bị trùng lặp.");
            }
            $seenCombos[$comboKey] = true;

            $cleanVariants[] = [
                'id' => $vId,
                'material_name' => $vMat,
                'color' => $vCol,
                'size_label' => $vSize,
                'price' => $vPrice,
                'stock' => $vStock,
            ];
        }

        $minPrice = min(array_column($cleanVariants, 'price'));
        $totalStock = array_sum(array_column($cleanVariants, 'stock'));

        $old = null;
        if ($id) {
            $old = dbone('SELECT * FROM products WHERE id=? AND deleted_at IS NULL', [$id]);
            if (!$old || (!$admin && (int)$old['vendor_id'] !== (int)$vendor)) throw new RuntimeException('Không được sửa sản phẩm của gian hàng khác.');
        }

        $image = upload_image();

        // Xác định trạng thái sau khi lưu:
        // Nếu Admin lưu -> active.
        // Nếu Vendor sửa sản phẩm đang 'active' và CHỈ thay đổi tồn kho (không đổi tên, ảnh, giá, thuộc tính) -> giữ nguyên 'active'.
        $nextStatus = $admin ? 'active' : 'pending';
        $nextRejectReason = null;

        if (!$admin && $old && $old['status'] === 'active' && !$image) {
            $coreUnchanged = (
                $old['name'] === $name &&
                (int)$old['category_id'] === $category &&
                (int)$old['material_id'] === $material &&
                (float)$old['price'] === (float)$minPrice
            );

            if ($coreUnchanged) {
                $oldVariants = dball('SELECT id, material_name, color, size_label, price FROM product_variants WHERE product_id=? ORDER BY id ASC', [$id]);
                if (count($oldVariants) === count($cleanVariants)) {
                    $oldMap = [];
                    foreach ($oldVariants as $ov) {
                        $oldMap[(int)$ov['id']] = $ov;
                    }
                    $variantsCoreSame = true;
                    foreach ($cleanVariants as $cv) {
                        $vid = $cv['id'];
                        if ($vid <= 0 || !isset($oldMap[$vid])) {
                            $variantsCoreSame = false;
                            break;
                        }
                        $ov = $oldMap[$vid];
                        if (
                            $ov['material_name'] !== $cv['material_name'] ||
                            $ov['color'] !== $cv['color'] ||
                            $ov['size_label'] !== $cv['size_label'] ||
                            (float)$ov['price'] !== (float)$cv['price']
                        ) {
                            $variantsCoreSame = false;
                            break;
                        }
                    }
                    if ($variantsCoreSame) {
                        $nextStatus = 'active';
                    }
                }
            }
        }

        $pdo->beginTransaction();
        try {
            if ($id) {
                execsql(
                    'UPDATE products SET vendor_id=?,category_id=?,material_id=?,name=?,price=?,stock=?,dimensions=?,weight_gram=?,pkg_length=?,pkg_width=?,pkg_height=?,status=?,reject_reason=? WHERE id=?',
                    [$vendor, $category, $material, $name, $minPrice, $totalStock, $dimensions, $weightGram, $pkgLength, $pkgWidth, $pkgHeight, $nextStatus, $nextRejectReason, $id]
                );
            } else {
                execsql(
                    'INSERT INTO products(vendor_id,category_id,material_id,name,price,stock,dimensions,weight_gram,pkg_length,pkg_width,pkg_height,status,reject_reason) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)',
                    [$vendor, $category, $material, $name, $minPrice, $totalStock, $dimensions, $weightGram, $pkgLength, $pkgWidth, $pkgHeight, $nextStatus, null]
                );
                $id = (int)$pdo->lastInsertId();
            }

            $keepIds = [];
            foreach ($cleanVariants as $cv) {
                if ($cv['id'] > 0 && dbone('SELECT id FROM product_variants WHERE id=? AND product_id=?', [$cv['id'], $id])) {
                    execsql(
                        'UPDATE product_variants SET material_name=?, color=?, size_label=?, price=?, stock=? WHERE id=? AND product_id=?',
                        [$cv['material_name'], $cv['color'], $cv['size_label'], $cv['price'], $cv['stock'], $cv['id'], $id]
                    );
                    $keepIds[] = $cv['id'];
                } else {
                    execsql(
                        'INSERT INTO product_variants(product_id, material_name, color, size_label, price, stock) VALUES(?,?,?,?,?,?)',
                        [$id, $cv['material_name'], $cv['color'], $cv['size_label'], $cv['price'], $cv['stock']]
                    );
                    $keepIds[] = (int)$pdo->lastInsertId();
                }
            }

            if ($keepIds) {
                $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
                execsql("DELETE FROM product_variants WHERE product_id=? AND id NOT IN ($placeholders)", array_merge([$id], $keepIds));
            }

            if ($image) {
                execsql('INSERT INTO product_images(product_id,image_path) VALUES(?,?) ON DUPLICATE KEY UPDATE image_path=VALUES(image_path)', [$id, $image]);
            }

            $pdo->commit();
            if ($admin) {
                flash('Đã lưu sản phẩm, thông số vận chuyển & thuộc tính.');
            } elseif ($nextStatus === 'active') {
                flash('Đã cập nhật tồn kho sản phẩm (có hiệu lực ngay, không cần chờ duyệt lại).');
            } else {
                flash('Đã gửi thay đổi sản phẩm chờ Admin duyệt.');
            }
            go($admin ? 'admin_products' : 'vendor_products');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($image && is_file(__DIR__ . '/../../' . $image)) unlink(__DIR__ . '/../../' . $image);
            throw $e;
        }
    }
    throw new RuntimeException('Thao tác sản phẩm không hợp lệ.');
}

-- ============================================================================
-- 3DPrintHub | DATABASE ALL IN ONE | MySQL 8.4 / Laragon (HeidiSQL)
-- ============================================================================
-- BAN DUNG: GOP DU 7 FILE SQL THANH 1 FILE DUY NHAT:
--   1 database.sql                 (9 bang goc + tai khoan du lieu mau)
--   2 upgrade.sql                  (product_images)
--   3 payment_upgrade.sql          (order_payments + thong tin nhan tien)
--   4 payos_upgrade.sql            (payos_transactions)
--   5 walletayments_u_upgrade.sql           (vi, giao dich, COD, thu nhap nguoi ban)
--   6 demo_ppgrade.sql    (MoMo / QR ngan hang DEMO)
--   7 admin_upgrade.sql            (xoa mem SP + completed_at thong ke)
--
-- AN TOAN DU LIEU:
--   - KHONG dung DROP TABLE/DELETE/TRUNCATE va KHONG reset data hien co.
--   - Du lieu mau chi them khi bang users trong database HOAN TOAN trong.
--   - Tren du an da co du lieu, NEN BACKUP MySQL truoc moi lan nang cap.
--   - Neu chi chay ban rut gon (giao dien + dang ky/dang nhap), PHP CHI
--     su dung bang users; nhung bang con lai duoc tao de su dung ve sau.
--
-- CACH IMPORT: Laragon -> Database (HeidiSQL) -> File -> Run SQL file...
--              Chon file nay -> Open -> F5 lam moi danh sach bang.
-- ============================================================================


-- PHAN 1 / 8: TAO DATABASE VA 9 BANG CO BAN (KHONG XOA BANG CU)

-- =====================================================================
-- CƠ SỞ DỮ LIỆU: 3DPRINTHUB (CHUẨN HÓA 3NF)
-- Hệ thống Sàn TMĐT & Dịch vụ Gia công In 3D Multi-vendor
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `3dprinthub_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `3dprinthub_db`;
-- 1. Bảng USERS (Tài khoản người dùng: Client, Vendor, Admin)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('client', 'vendor', 'admin') NOT NULL DEFAULT 'client',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động, 0: Khóa',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Bảng VENDORS (Hồ sơ Gian hàng / Xưởng in 3D)
CREATE TABLE IF NOT EXISTS `vendors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `shop_name` VARCHAR(150) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `printer_capacity` VARCHAR(255) DEFAULT NULL COMMENT 'Năng lực máy in FDM/SLA',
  `bank_name` VARCHAR(100) DEFAULT NULL,
  `bank_account` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_vendors_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Bảng CATEGORIES (Danh mục sản phẩm toàn sàn)
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- 4. Bảng MATERIALS (Vật liệu & Đơn giá in theo từng Xưởng)
CREATE TABLE IF NOT EXISTS `materials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL COMMENT 'PLA+, ABS, PETG, Resin 8K',
  `price_per_cm3` DECIMAL(10,2) NOT NULL,
  `colors` VARCHAR(255) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT `fk_materials_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Bảng PRODUCTS (Sản phẩm mẫu in 3D sẵn có)
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `material_id` INT NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `dimensions` VARCHAR(100) DEFAULT NULL COMMENT 'VD: 180 x 120 x 210 mm',
  `preview_file` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'hidden', 'pending') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_products_material` FOREIGN KEY (`material_id`) REFERENCES `materials`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. Bảng CUSTOM_QUOTES (Yêu cầu báo giá & Đặt in 3D từ file .STL/.OBJ)
CREATE TABLE IF NOT EXISTS `custom_quotes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `vendor_id` INT NOT NULL,
  `material_id` INT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `volume_cm3` FLOAT NOT NULL,
  `infill_percent` INT NOT NULL DEFAULT 20,
  `layer_height` FLOAT NOT NULL DEFAULT 0.20,
  `quantity` INT NOT NULL DEFAULT 1,
  `estimated_price` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending_review', 'approved', 'rejected', 'ordered') NOT NULL DEFAULT 'pending_review',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_quotes_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quotes_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_quotes_material` FOREIGN KEY (`material_id`) REFERENCES `materials`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Bảng ORDERS (Đơn hàng tổng)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `vendor_id` INT NOT NULL,
  `receiver_name` VARCHAR(100) NOT NULL,
  `receiver_phone` VARCHAR(20) NOT NULL,
  `shipping_address` VARCHAR(255) NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `payment_method` ENUM('cod', 'banking') NOT NULL DEFAULT 'cod',
  `order_status` ENUM('pending', 'printing', 'shipping', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_orders_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 8. Bảng ORDER_ITEMS (Chi tiết đơn hàng - Hỗ trợ cả mẫu sẵn và đơn in 3D)
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `custom_quote_id` INT DEFAULT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL,
  CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_items_quote` FOREIGN KEY (`custom_quote_id`) REFERENCES `custom_quotes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 9. Bảng REVIEWS (Đánh giá sản phẩm & Xưởng in)
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `comment` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
-- =====================================================================

-- PHAN 2 / 8: UPGRADE.SQL

USE `3dprinthub_db`;
CREATE TABLE IF NOT EXISTS product_images (
  product_id INT PRIMARY KEY,
  image_path VARCHAR(255) NOT NULL,
  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PHAN 3 / 8: PAYMENT_UPGRADE.SQL

-- 3DPrintHub: nâng cấp THANH TOÁN CHUYỂN KHOẢN (giữ nguyên dữ liệu cũ)
-- Chạy MỘT LẦN qua HeidiSQL -> File -> Run SQL file...
-- Chỉ chạy sau khi đã import database.sql và upgrade.sql.
USE `3dprinthub_db`;

-- Thêm trường tên người nhận tiền, vẫn tương thích dữ liệu gian hàng cũ.
-- MySQL 8.4 không hỗ trợ ADD COLUMN IF NOT EXISTS; kiểm tra bằng information_schema.
SET @has_holder := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vendors' AND COLUMN_NAME = 'bank_account_holder'
);
SET @alter_holder := IF(@has_holder = 0,
  'ALTER TABLE `vendors` ADD COLUMN `bank_account_holder` VARCHAR(120) NULL AFTER `bank_account`',
  'SELECT ''bank_account_holder already exists'' AS info'
);
PREPARE add_holder_stmt FROM @alter_holder;
EXECUTE add_holder_stmt;
DEALLOCATE PREPARE add_holder_stmt;

CREATE TABLE IF NOT EXISTS `order_payments` (
  `order_id` INT NOT NULL PRIMARY KEY,
  `status` ENUM('unpaid','submitted','confirmed','rejected') NOT NULL DEFAULT 'unpaid',
  `bank_name` VARCHAR(100) NOT NULL,
  `bank_account` VARCHAR(50) NOT NULL,
  `bank_account_holder` VARCHAR(120) NULL,
  `submitted_at` DATETIME NULL,
  `reviewed_at` DATETIME NULL,
  `reviewed_by` INT NULL,
  `review_note` VARCHAR(255) NULL,
  `receipt_mime` VARCHAR(30) NULL,
  `receipt_data` MEDIUMBLOB NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_order_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  INDEX `idx_payment_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hỗ trợ xem lại các đơn Banking có trước bản cập nhật: không tự đánh dấu đã trả tiền.
INSERT INTO `order_payments` (`order_id`, `bank_name`, `bank_account`, `bank_account_holder`)
SELECT o.id, COALESCE(v.bank_name,''), COALESCE(v.bank_account,''), v.bank_account_holder
FROM orders o JOIN vendors v ON v.id=o.vendor_id
WHERE o.payment_method='banking'
ON DUPLICATE KEY UPDATE `order_id` = `order_id`;

-- PHAN 4 / 8: PAYOS_UPGRADE.SQL

-- Run AFTER database.sql, upgrade.sql, payment_upgrade.sql.
-- Does not delete data. Backup before upgrading.
USE `3dprinthub_db`;
SET @has_provider := (
 SELECT COUNT(*) FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='order_payments' AND COLUMN_NAME='payment_provider'
);
SET @add_provider := IF(@has_provider=0,
 'ALTER TABLE `order_payments` ADD COLUMN `payment_provider` ENUM(''manual'',''payos'') NOT NULL DEFAULT ''manual'' AFTER `status`',
 'SELECT ''payment_provider already exists'' AS info');
PREPARE provider_stmt FROM @add_provider;
EXECUTE provider_stmt;
DEALLOCATE PREPARE provider_stmt;

CREATE TABLE IF NOT EXISTS `payos_transactions` (
 `order_id` INT NOT NULL PRIMARY KEY,
 `payos_order_code` BIGINT NOT NULL UNIQUE,
 `payment_link_id` VARCHAR(100) NULL,
 `checkout_url` VARCHAR(1024) NULL,
 `amount` BIGINT NOT NULL,
 `status` ENUM('created','paid','cancelled') NOT NULL DEFAULT 'created',
 `bank_reference` VARCHAR(150) NULL,
 `paid_at` DATETIME NULL,
 `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT `fk_payos_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
 INDEX `idx_payos_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PHAN 5 / 8: WALLET_UPGRADE.SQL

-- 3DPrintHub | Khoản trả trước dùng để mua hàng (MÔI TRƯỜNG DEMO)
-- Chạy SAU database.sql, upgrade.sql, payment_upgrade.sql, payos_upgrade.sql.
-- Không DROP bảng, không xóa dữ liệu hiện có.
USE `3dprinthub_db`;

ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('cod','banking','wallet') NOT NULL DEFAULT 'cod';

CREATE TABLE IF NOT EXISTS `wallet_accounts` (
    `user_id` INT NOT NULL PRIMARY KEY,
    `balance` DECIMAL(14,0) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_wallet_account_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wallet_transactions` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `direction` ENUM('credit','debit') NOT NULL,
    `amount` DECIMAL(14,0) NOT NULL,
    `balance_after` DECIMAL(14,0) NOT NULL,
    `type` ENUM('topup','order_payment','order_refund','demo_credit') NOT NULL,
    `reference_type` VARCHAR(30) NOT NULL,
    `reference_id` BIGINT NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_wallet_event` (`user_id`, `type`, `reference_type`, `reference_id`),
    INDEX `idx_wallet_user_time` (`user_id`, `created_at`),
    CONSTRAINT `fk_wallet_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wallet_topups` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(14,0) NOT NULL,
    `status` ENUM('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
    `payos_order_code` BIGINT NOT NULL UNIQUE,
    `payment_link_id` VARCHAR(100) DEFAULT NULL,
    `checkout_url` VARCHAR(1000) DEFAULT NULL,
    `bank_reference` VARCHAR(120) DEFAULT NULL UNIQUE,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `paid_at` DATETIME DEFAULT NULL,
    INDEX `idx_topups_user` (`user_id`, `created_at`),
    CONSTRAINT `fk_wallet_topup_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `cod_collections` (
    `order_id` INT NOT NULL PRIMARY KEY,
    `collected_by` INT NOT NULL,
    `collected_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `note` VARCHAR(255) DEFAULT NULL,
    CONSTRAINT `fk_cod_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cod_collector` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vendor_earnings` (
    `order_id` INT NOT NULL PRIMARY KEY,
    `vendor_id` INT NOT NULL,
    `gross_amount` DECIMAL(14,0) NOT NULL,
    `platform_fee` DECIMAL(14,0) NOT NULL DEFAULT 0,
    `net_amount` DECIMAL(14,0) NOT NULL,
    `status` ENUM('held','eligible','cancelled','paid') NOT NULL DEFAULT 'held',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `eligible_at` DATETIME DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `payout_reference` VARCHAR(120) DEFAULT NULL,
    CONSTRAINT `fk_vendor_earn_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_vendor_earn_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PHAN 6 / 8: DEMO_PAYMENTS_UPGRADE.SQL

-- 3DPrintHub: MoMo DEMO + QR ngân hàng DEMO (KHÔNG CHUYỂN TIỀN THẬT)
-- Chạy SAU: database.sql -> upgrade.sql -> payment_upgrade.sql -> payos_upgrade.sql -> wallet_upgrade.sql.
-- Không xóa bảng, không xóa đơn hàng hoặc số dư cũ.
USE `3dprinthub_db`;

-- Hai lựa chọn chỉ là mô phỏng. Các giao dịch thực sự vẫn chỉ do payOS/webhook ghi nhận.
ALTER TABLE `order_payments`
 MODIFY COLUMN `payment_provider` ENUM('manual','payos','demo_momo','demo_bank')
 NOT NULL DEFAULT 'manual';

CREATE TABLE IF NOT EXISTS `demo_payments` (
 `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 `order_id` INT NULL UNIQUE,
 `topup_id` BIGINT NULL UNIQUE,
 `provider` ENUM('momo','bank_qr') NOT NULL,
 `status` ENUM('pending','paid') NOT NULL DEFAULT 'pending',
 `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `expires_at` DATETIME NOT NULL,
 `confirmed_at` DATETIME NULL,
 KEY `idx_demo_status` (`status`,`created_at`),
 CONSTRAINT `fk_demo_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
 CONSTRAINT `fk_demo_topup` FOREIGN KEY (`topup_id`) REFERENCES `wallet_topups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PHAN 7 / 8: ADMIN_UPGRADE.SQL

-- 3DPrintHub - Admin Dashboard nâng cao - nâng cấp KHÔNG xóa dữ liệu
-- Chạy sau: database.sql, upgrade.sql, payment_upgrade.sql, payos_upgrade.sql,
-- wallet_upgrade.sql, demo_payments_upgrade.sql (nếu cài bản đủ MoMo/QR DEMO).
-- Chỉ chạy file này trên database đã có sẵn; không chạy lại database.sql.
USE `3dprinthub_db`;

-- Xóa mềm: sản phẩm vẫn được giữ để tham chiếu trong order_items / reviews.
SET @has_deleted_at = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='deleted_at'
);
SET @sql = IF(@has_deleted_at = 0,
  'ALTER TABLE `products` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `created_at`',
  'SELECT ''products.deleted_at existed'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ghi thời gian hoàn tất thật cho các đơn hoàn thành từ phiên bản này.
SET @has_completed_at = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='completed_at'
);
SET @sql = IF(@has_completed_at = 0,
  'ALTER TABLE `orders` ADD COLUMN `completed_at` DATETIME NULL DEFAULT NULL AFTER `created_at`',
  'SELECT ''orders.completed_at existed'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Đơn từng hoàn tất trước cập nhật không có giờ hoàn tất gốc;
-- tạm dùng created_at, đây là GIÁ TRỊ XẤP XỈ cho dữ liệu lịch sử.
UPDATE `orders` SET `completed_at`=`created_at`
WHERE `order_status`='completed' AND `completed_at` IS NULL;

-- PHAN 8 / 8: DU LIEU MAU, CHI NAP KHI BANG users DANG RONG
-- Chi them data mau cho database moi; tranh sua ID / xung dot du lieu cu.
SET @seed_empty_database = (SELECT IF(COUNT(*) = 0, 1, 0) FROM `users`);

-- Du lieu mau: users
SET @seed_query = IF(@seed_empty_database = 1,
'INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `role`, `status`) VALUES
(1, ''Admin System'', ''admin@3dprinthub.vn'', ''$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'', ''0900000001'', ''admin'', 1),
(2, ''Trần Hoàng Minh'', ''techlab@3dprinthub.vn'', ''$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'', ''0911223344'', ''vendor'', 1),
(3, ''Lê Thu Hà'', ''artprint@3dprinthub.vn'', ''$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'', ''0922334455'', ''vendor'', 1),
(4, ''Lê Văn C'', ''cantho3d@gmail.com'', ''$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'', ''0912345678'', ''vendor'', 1),
(5, ''Nguyễn Văn An'', ''nguyenvana@gmail.com'', ''$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'', ''0988112233'', ''client'', 1);',
'DO 0');
PREPARE seed_stmt_3d FROM @seed_query;
EXECUTE seed_stmt_3d;
DEALLOCATE PREPARE seed_stmt_3d;

-- Du lieu mau: vendors
SET @seed_query = IF(@seed_empty_database = 1,
'INSERT INTO `vendors` (`id`, `user_id`, `shop_name`, `address`, `printer_capacity`, `bank_name`, `bank_account`, `status`) VALUES
(1, 2, ''Xưởng In TechLab'', ''Thanh Xuân, Hà Nội'', ''8 máy FDM Bambu Lab P1S'', ''Vietcombank'', ''0011001234567'', ''approved''),
(2, 3, ''ArtPrint Studio'', ''Quận 1, TP.HCM'', ''5 máy Resin 8K SLA'', ''Techcombank'', ''1903456789012'', ''approved''),
(3, 4, ''Xưởng In 3D Cần Thơ'', ''Ninh Kiều, Cần Thơ'', ''6 máy FDM (PLA, ABS)'', ''MB Bank'', ''0912345678'', ''pending'');',
'DO 0');
PREPARE seed_stmt_3d FROM @seed_query;
EXECUTE seed_stmt_3d;
DEALLOCATE PREPARE seed_stmt_3d;

-- Du lieu mau: categories
SET @seed_query = IF(@seed_empty_database = 1,
'INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `status`) VALUES
(1, ''Mô hình Cơ khí & Robot'', ''mo-hinh-co-khi'', ''Cánh tay robot, bánh răng, linh kiện kỹ thuật chính xác'', 1),
(2, ''Tượng Figure & Nghệ thuật'', ''tuong-figure'', ''Mô hình nhân vật Resin 8K độ mịn cao'', 1),
(3, ''Vỏ hộp Điện tử & IoT'', ''vo-hop-dien-tu'', ''Vỏ bảo vệ mạch Arduino, Raspberry Pi, ESP32'', 1);',
'DO 0');
PREPARE seed_stmt_3d FROM @seed_query;
EXECUTE seed_stmt_3d;
DEALLOCATE PREPARE seed_stmt_3d;

-- Du lieu mau: materials
SET @seed_query = IF(@seed_empty_database = 1,
'INSERT INTO `materials` (`id`, `vendor_id`, `name`, `price_per_cm3`, `colors`, `status`) VALUES
(1, 1, ''PLA+ Cao cấp'', 3000.00, ''Trắng sứ, Đen nhám, Cam kỹ thuật'', 1),
(2, 1, ''ABS Chịu nhiệt'', 3600.00, ''Đen, Xám bạc'', 1),
(3, 2, ''Resin 8K Tiêu chuẩn'', 6500.00, ''Xám khói, Trắng đục'', 1);',
'DO 0');
PREPARE seed_stmt_3d FROM @seed_query;
EXECUTE seed_stmt_3d;
DEALLOCATE PREPARE seed_stmt_3d;

-- Du lieu mau: products
SET @seed_query = IF(@seed_empty_database = 1,
'INSERT INTO `products` (`id`, `vendor_id`, `category_id`, `material_id`, `name`, `price`, `stock`, `dimensions`, `preview_file`, `status`) VALUES
(1, 1, 1, 1, ''Cánh tay Robot 4 bậc tự do'', 240000.00, 18, ''180 x 120 x 210 mm'', ''robot_arm.stl'', ''active''),
(2, 2, 2, 3, ''Tượng mô hình Anime Figure 8K'', 450000.00, 7, ''110 x 110 x 240 mm'', ''anime_figure.obj'', ''active''),
(3, 1, 3, 2, ''Vỏ hộp Raspberry Pi 5 tản nhiệt'', 125000.00, 30, ''95 x 68 x 32 mm'', ''rpi5_case.stl'', ''pending''),
(4, 1, 1, 1, ''Bộ bánh răng hành tinh cơ khí 3 cấp'', 185000.00, 25, ''110 x 110 x 65 mm'', ''planetary_gear.stl'', ''active''),
(5, 2, 2, 3, ''Tượng bán thân Chiến binh Cyber Resin'', 520000.00, 5, ''140 x 120 x 260 mm'', ''cyber_bust.obj'', ''active''),
(6, 1, 3, 2, ''Hộp bảo vệ mạch Arduino Uno R4 WiFi'', 95000.00, 40, ''82 x 60 x 28 mm'', ''arduino_r4_case.stl'', ''active'');',
'DO 0');
PREPARE seed_stmt_3d FROM @seed_query;
EXECUTE seed_stmt_3d;
DEALLOCATE PREPARE seed_stmt_3d;

-- Du lieu mau: custom_quotes
SET @seed_query = IF(@seed_empty_database = 1,
'INSERT INTO `custom_quotes` (`id`, `user_id`, `vendor_id`, `material_id`, `file_path`, `volume_cm3`, `infill_percent`, `layer_height`, `quantity`, `estimated_price`, `status`) VALUES
(1, 5, 1, 1, ''uploads/stl/robot_arm_v2.stl'', 45.8, 20, 0.20, 1, 120000.00, ''ordered'');',
'DO 0');
PREPARE seed_stmt_3d FROM @seed_query;
EXECUTE seed_stmt_3d;
DEALLOCATE PREPARE seed_stmt_3d;

-- Du lieu mau: orders
SET @seed_query = IF(@seed_empty_database = 1,
'INSERT INTO `orders` (`id`, `order_code`, `user_id`, `vendor_id`, `receiver_name`, `receiver_phone`, `shipping_address`, `total_amount`, `payment_method`, `order_status`) VALUES
(1, ''#ORD-202688'', 5, 1, ''Nguyễn Văn An'', ''0988112233'', ''Số 12 Nguyễn Trãi, Thanh Xuân, Hà Nội'', 370000.00, ''cod'', ''printing'');',
'DO 0');
PREPARE seed_stmt_3d FROM @seed_query;
EXECUTE seed_stmt_3d;
DEALLOCATE PREPARE seed_stmt_3d;

-- Du lieu mau: order_items
SET @seed_query = IF(@seed_empty_database = 1,
'INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `custom_quote_id`, `quantity`, `unit_price`) VALUES
(1, 1, 1, NULL, 1, 240000.00),
(2, 1, NULL, 1, 1, 120000.00);',
'DO 0');
PREPARE seed_stmt_3d FROM @seed_query;
EXECUTE seed_stmt_3d;
DEALLOCATE PREPARE seed_stmt_3d;

-- Du lieu mau: reviews
SET @seed_query = IF(@seed_empty_database = 1,
'INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`) VALUES
(1, 5, 1, 5, ''Bản in sắc nét, khớp nối chuyển động mượt mà, giao hàng nhanh.'');',
'DO 0');
PREPARE seed_stmt_3d FROM @seed_query;
EXECUTE seed_stmt_3d;
DEALLOCATE PREPARE seed_stmt_3d;

SELECT '3DPrintHub: tao/nang cap database hoan tat' AS result, (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='3dprinthub_db') AS total_tables, @seed_empty_database AS inserted_demo_seed;

-- =====================================================================
-- CƠ SỞ DỮ LIỆU: 3DPRINTHUB (CHUẨN HÓA 3NF)
-- Hệ thống Sàn TMĐT & Dịch vụ Gia công In 3D Multi-vendor
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `3dprinthub_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `3dprinthub_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Bảng USERS (Tài khoản người dùng: Client, Vendor, Admin)
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
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
DROP TABLE IF EXISTS `vendors`;
CREATE TABLE `vendors` (
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
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- 4. Bảng MATERIALS (Vật liệu & Đơn giá in theo từng Xưởng)
DROP TABLE IF EXISTS `materials`;
CREATE TABLE `materials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL COMMENT 'PLA+, ABS, PETG, Resin 8K',
  `price_per_cm3` DECIMAL(10,2) NOT NULL,
  `colors` VARCHAR(255) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT `fk_materials_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Bảng PRODUCTS (Sản phẩm mẫu in 3D sẵn có)
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
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
DROP TABLE IF EXISTS `custom_quotes`;
CREATE TABLE `custom_quotes` (
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
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
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
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
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
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `comment` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- DỮ LIỆU MẪU (SEED DATA) ĐỒNG BỘ VỚI GIAO DIỆN 3DPRINTHUB
-- =====================================================================

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `role`, `status`) VALUES
(1, 'Admin System', 'admin@3dprinthub.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0900000001', 'admin', 1),
(2, 'Trần Hoàng Minh', 'techlab@3dprinthub.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0911223344', 'vendor', 1),
(3, 'Lê Thu Hà', 'artprint@3dprinthub.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0922334455', 'vendor', 1),
(4, 'Lê Văn C', 'cantho3d@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'vendor', 1),
(5, 'Nguyễn Văn An', 'nguyenvana@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0988112233', 'client', 1);

INSERT INTO `vendors` (`id`, `user_id`, `shop_name`, `address`, `printer_capacity`, `bank_name`, `bank_account`, `status`) VALUES
(1, 2, 'Xưởng In TechLab', 'Thanh Xuân, Hà Nội', '8 máy FDM Bambu Lab P1S', 'Vietcombank', '0011001234567', 'approved'),
(2, 3, 'ArtPrint Studio', 'Quận 1, TP.HCM', '5 máy Resin 8K SLA', 'Techcombank', '1903456789012', 'approved'),
(3, 4, 'Xưởng In 3D Cần Thơ', 'Ninh Kiều, Cần Thơ', '6 máy FDM (PLA, ABS)', 'MB Bank', '0912345678', 'pending');

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `status`) VALUES
(1, 'Mô hình Cơ khí & Robot', 'mo-hinh-co-khi', 'Cánh tay robot, bánh răng, linh kiện kỹ thuật chính xác', 1),
(2, 'Tượng Figure & Nghệ thuật', 'tuong-figure', 'Mô hình nhân vật Resin 8K độ mịn cao', 1),
(3, 'Vỏ hộp Điện tử & IoT', 'vo-hop-dien-tu', 'Vỏ bảo vệ mạch Arduino, Raspberry Pi, ESP32', 1);

INSERT INTO `materials` (`id`, `vendor_id`, `name`, `price_per_cm3`, `colors`, `status`) VALUES
(1, 1, 'PLA+ Cao cấp', 3000.00, 'Trắng sứ, Đen nhám, Cam kỹ thuật', 1),
(2, 1, 'ABS Chịu nhiệt', 3600.00, 'Đen, Xám bạc', 1),
(3, 2, 'Resin 8K Tiêu chuẩn', 6500.00, 'Xám khói, Trắng đục', 1);

INSERT INTO `products` (`id`, `vendor_id`, `category_id`, `material_id`, `name`, `price`, `stock`, `dimensions`, `preview_file`, `status`) VALUES
(1, 1, 1, 1, 'Cánh tay Robot 4 bậc tự do', 240000.00, 18, '180 x 120 x 210 mm', 'robot_arm.stl', 'active'),
(2, 2, 2, 3, 'Tượng mô hình Anime Figure 8K', 450000.00, 7, '110 x 110 x 240 mm', 'anime_figure.obj', 'active'),
(3, 1, 3, 2, 'Vỏ hộp Raspberry Pi 5 tản nhiệt', 125000.00, 30, '95 x 68 x 32 mm', 'rpi5_case.stl', 'pending');

INSERT INTO `custom_quotes` (`id`, `user_id`, `vendor_id`, `material_id`, `file_path`, `volume_cm3`, `infill_percent`, `layer_height`, `quantity`, `estimated_price`, `status`) VALUES
(1, 5, 1, 1, 'uploads/stl/robot_arm_v2.stl', 45.8, 20, 0.20, 1, 120000.00, 'ordered');

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `vendor_id`, `receiver_name`, `receiver_phone`, `shipping_address`, `total_amount`, `payment_method`, `order_status`) VALUES
(1, '#ORD-202688', 5, 1, 'Nguyễn Văn An', '0988112233', 'Số 12 Nguyễn Trãi, Thanh Xuân, Hà Nội', 370000.00, 'cod', 'printing');

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `custom_quote_id`, `quantity`, `unit_price`) VALUES
(1, 1, 1, NULL, 1, 240000.00),
(2, 1, NULL, 1, 1, 120000.00);

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`) VALUES
(1, 5, 1, 5, 'Bản in sắc nét, khớp nối chuyển động mượt mà, giao hàng nhanh.');

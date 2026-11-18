-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 18, 2026 at 10:36 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `3dprinthub_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `status`) VALUES
(1, 'Mô hình Cơ khí & Robot', 'mo-hinh-co-khi', 'Cánh tay robot, bánh răng, linh kiện kỹ thuật chính xác', 1),
(2, 'Tượng Figure & Nghệ thuật', 'tuong-figure', 'Mô hình nhân vật Resin 8K độ mịn cao', 1),
(3, 'Vỏ hộp Điện tử & IoT', 'vo-hop-dien-tu', 'Vỏ bảo vệ mạch Arduino, Raspberry Pi, ESP32', 1),
(4, 'Phụ kiện Bàn làm việc & Decor', 'phu-kien-decor', 'Giá đỡ tai nghe, khay tổ chức cáp, đèn trang trí 3D', 1),
(5, 'Linh kiện Drone & RC', 'linh-kien-drone-rc', 'Khung bảo vệ cánh quạt, đế gắn camera FPV siêu nhẹ', 1),
(6, 'Khuôn mẫu & Dụng cụ gia đình', 'khuon-mau-gia-dung', 'Ngàm treo tường, khớp nối sửa chữa đồ gia dụng', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cod_collections`
--

DROP TABLE IF EXISTS `cod_collections`;
CREATE TABLE `cod_collections` (
  `order_id` int NOT NULL,
  `collected_by` int NOT NULL,
  `collected_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_quotes`
--

DROP TABLE IF EXISTS `custom_quotes`;
CREATE TABLE `custom_quotes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `material_id` int NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `volume_cm3` float NOT NULL,
  `infill_percent` int NOT NULL DEFAULT '20',
  `layer_height` float NOT NULL DEFAULT '0.2',
  `quantity` int NOT NULL DEFAULT '1',
  `estimated_price` decimal(12,2) NOT NULL,
  `status` enum('pending_review','approved','rejected','ordered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_review',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_quotes`
--

INSERT INTO `custom_quotes` (`id`, `user_id`, `vendor_id`, `material_id`, `file_path`, `volume_cm3`, `infill_percent`, `layer_height`, `quantity`, `estimated_price`, `status`, `created_at`) VALUES
(1, 5, 1, 1, 'uploads/stl/robot_arm_v2.stl', 45.8, 20, 0.2, 1, 120000.00, 'ordered', '2026-11-18 17:33:21'),
(2, 5, 2, 3, 'uploads/stl/dragon_miniature_8k.stl', 28.5, 100, 0.05, 2, 370000.00, 'approved', '2026-11-18 17:33:21'),
(3, 5, 1, 4, 'uploads/stl/gearbox_housing.obj', 62, 40, 0.16, 1, 215000.00, 'pending_review', '2026-11-18 17:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `demo_payments`
--

DROP TABLE IF EXISTS `demo_payments`;
CREATE TABLE `demo_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` int DEFAULT NULL,
  `topup_id` bigint DEFAULT NULL,
  `provider` enum('momo','bank_qr') NOT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `confirmed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

DROP TABLE IF EXISTS `materials`;
CREATE TABLE `materials` (
  `id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'PLA+, ABS, PETG, Resin 8K',
  `price_per_cm3` decimal(10,2) NOT NULL,
  `colors` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `vendor_id`, `name`, `price_per_cm3`, `colors`, `status`) VALUES
(1, 1, 'PLA+ Cao cấp', 3000.00, 'Trắng sứ, Đen nhám, Cam kỹ thuật', 1),
(2, 1, 'ABS Chịu nhiệt', 3600.00, 'Đen, Xám bạc', 1),
(3, 2, 'Resin 8K Tiêu chuẩn', 6500.00, 'Xám khói, Trắng đục', 1),
(4, 1, 'PETG Chịu lực', 3400.00, 'Trong suốt, Xanh dương, Đen', 1),
(5, 1, 'TPU 95A Dẻo đàn hồi', 4200.00, 'Đen nhám, Đỏ tươi', 1),
(6, 2, 'Resin Tough Kỹ thuật', 7800.00, 'Xanh ngọc, Đen mờ', 1),
(7, 2, 'ASA Chống tia UV', 4000.00, 'Trắng ngà, Xám đậm', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `receiver_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiver_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cod','banking','wallet') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cod',
  `order_status` enum('pending','printing','shipping','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `vendor_id`, `receiver_name`, `receiver_phone`, `shipping_address`, `total_amount`, `payment_method`, `order_status`, `created_at`, `completed_at`) VALUES
(1, '#ORD-202688', 5, 1, 'Nguyễn Văn An', '0988112233', 'Số 12 Nguyễn Trãi, Thanh Xuân, Hà Nội', 360000.00, 'cod', 'printing', '2026-11-16 17:33:21', NULL),
(2, '#ORD-202689', 5, 2, 'Nguyễn Văn An', '0988112233', 'Số 12 Nguyễn Trãi, Thanh Xuân, Hà Nội', 450000.00, 'banking', 'completed', '2026-11-03 17:33:21', '2026-11-06 17:33:21'),
(3, '#ORD-202690', 5, 1, 'Nguyễn Văn An', '0988112233', 'Số 12 Nguyễn Trãi, Thanh Xuân, Hà Nội', 350000.00, 'cod', 'completed', '2026-11-13 17:33:21', '2026-11-16 17:33:21'),
(4, '#ORD-202691', 5, 2, 'Nguyễn Văn An', '0988112233', 'Số 12 Nguyễn Trãi, Thanh Xuân, Hà Nội', 210000.00, 'banking', 'shipping', '2026-11-17 17:33:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `custom_quote_id` int DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `custom_quote_id`, `quantity`, `unit_price`) VALUES
(1, 1, 1, NULL, 1, 240000.00),
(2, 1, NULL, 1, 1, 120000.00),
(3, 2, 2, NULL, 1, 450000.00),
(4, 3, 4, NULL, 1, 185000.00),
(5, 3, 7, NULL, 1, 165000.00),
(6, 4, 8, NULL, 1, 210000.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_payments`
--

DROP TABLE IF EXISTS `order_payments`;
CREATE TABLE `order_payments` (
  `order_id` int NOT NULL,
  `status` enum('unpaid','submitted','confirmed','rejected') NOT NULL DEFAULT 'unpaid',
  `payment_provider` enum('manual','payos','demo_momo','demo_bank') NOT NULL DEFAULT 'manual',
  `bank_name` varchar(100) NOT NULL,
  `bank_account` varchar(50) NOT NULL,
  `bank_account_holder` varchar(120) DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `review_note` varchar(255) DEFAULT NULL,
  `receipt_mime` varchar(30) DEFAULT NULL,
  `receipt_data` mediumblob,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payos_transactions`
--

DROP TABLE IF EXISTS `payos_transactions`;
CREATE TABLE `payos_transactions` (
  `order_id` int NOT NULL,
  `payos_order_code` bigint NOT NULL,
  `payment_link_id` varchar(100) DEFAULT NULL,
  `checkout_url` varchar(1024) DEFAULT NULL,
  `amount` bigint NOT NULL,
  `status` enum('created','paid','cancelled') NOT NULL DEFAULT 'created',
  `bank_reference` varchar(150) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `category_id` int NOT NULL,
  `material_id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `dimensions` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VD: 180 x 120 x 210 mm',
  `preview_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','hidden','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `vendor_id`, `category_id`, `material_id`, `name`, `price`, `stock`, `dimensions`, `preview_file`, `status`, `created_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 'Cánh tay Robot 4 bậc tự do', 240000.00, 18, '180 x 120 x 210 mm', 'robot_arm.stl', 'active', '2026-11-18 17:33:21', NULL),
(2, 2, 2, 3, 'Tượng mô hình Anime Figure 8K', 450000.00, 7, '110 x 110 x 240 mm', 'anime_figure.obj', 'active', '2026-11-18 17:33:21', NULL),
(3, 1, 3, 2, 'Vỏ hộp Raspberry Pi 5 tản nhiệt', 125000.00, 30, '95 x 68 x 32 mm', 'rpi5_case.stl', 'pending', '2026-11-18 17:33:21', NULL),
(4, 1, 1, 4, 'Bộ bánh răng hành tinh cơ khí 3 cấp', 185000.00, 25, '110 x 110 x 65 mm', 'planetary_gear.stl', 'active', '2026-11-18 17:33:21', NULL),
(5, 2, 2, 3, 'Tượng bán thân Chiến binh Cyber Resin', 520000.00, 5, '140 x 120 x 260 mm', 'cyber_bust.obj', 'active', '2026-11-18 17:33:21', NULL),
(6, 1, 3, 2, 'Hộp bảo vệ mạch Arduino Uno R4 WiFi', 95000.00, 40, '82 x 60 x 28 mm', 'arduino_r4_case.stl', 'active', '2026-11-18 17:33:21', NULL),
(7, 1, 4, 1, 'Giá đỡ tai nghe hình học Polygon', 165000.00, 22, '135 x 115 x 245 mm', 'headphone_stand.stl', 'active', '2026-11-18 17:33:21', NULL),
(8, 2, 5, 6, 'Bộ khung bảo vệ cánh Drone FPV 3 inch', 210000.00, 14, '165 x 165 x 38 mm', 'fpv_guard.stl', 'active', '2026-11-18 17:33:21', NULL),
(9, 1, 6, 4, 'Ngàm treo tường đa năng Pegboard Skadis', 75000.00, 50, '120 x 45 x 40 mm', 'skadis_mount.stl', 'active', '2026-11-18 17:33:21', NULL),
(10, 2, 4, 7, 'Đèn ngủ mặt trăng Lithophane 18cm', 320000.00, 9, '180 x 180 x 180 mm', 'moon_lamp.stl', 'pending', '2026-11-18 17:33:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `product_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `rating` tinyint NOT NULL DEFAULT '5',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`) VALUES
(1, 5, 1, 5, 'Bản in sắc nét, khớp nối chuyển động mượt mà, giao hàng nhanh.', '2026-11-18 17:33:21'),
(2, 5, 2, 5, 'Chi tiết Resin 8K cực kỳ mịn, không lộ vân lớp in, đóng gói chống sốc kỹ.', '2026-11-18 17:33:21'),
(3, 5, 4, 4, 'Bánh răng ăn khớp chuẩn, tra thêm chút mỡ bò quay rất êm.', '2026-11-18 17:33:21'),
(4, 5, 7, 5, 'Giá đỡ chắc chắn, để tai nghe chụp tai nặng vẫn đứng vững.', '2026-11-18 17:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('client','vendor','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1: Hoạt động, 0: Khóa',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `role`, `status`, `created_at`) VALUES
(1, 'Admin System', 'admin@3dprinthub.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0900000001', 'admin', 1, '2026-11-18 17:33:21'),
(2, 'Trần Hoàng Minh', 'techlab@3dprinthub.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0911223344', 'vendor', 1, '2026-11-18 17:33:21'),
(3, 'Lê Thu Hà', 'artprint@3dprinthub.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0922334455', 'vendor', 1, '2026-11-18 17:33:21'),
(4, 'Lê Văn C', 'cantho3d@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'vendor', 1, '2026-11-18 17:33:21'),
(5, 'Nguyễn Văn An', 'nguyenvana@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0988112233', 'client', 1, '2026-11-18 17:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
CREATE TABLE `vendors` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `shop_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `printer_capacity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Năng lực máy in FDM/SLA',
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_holder` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `user_id`, `shop_name`, `address`, `printer_capacity`, `bank_name`, `bank_account`, `bank_account_holder`, `status`, `created_at`) VALUES
(1, 2, 'Xưởng In TechLab', 'Thanh Xuân, Hà Nội', '8 máy FDM Bambu Lab P1S', 'Vietcombank', '0011001234567', NULL, 'approved', '2026-11-18 17:33:21'),
(2, 3, 'ArtPrint Studio', 'Quận 1, TP.HCM', '5 máy Resin 8K SLA', 'Techcombank', '1903456789012', NULL, 'approved', '2026-11-18 17:33:21'),
(3, 4, 'Xưởng In 3D Cần Thơ', 'Ninh Kiều, Cần Thơ', '6 máy FDM (PLA, ABS)', 'MB Bank', '0912345678', NULL, 'pending', '2026-11-18 17:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_earnings`
--

DROP TABLE IF EXISTS `vendor_earnings`;
CREATE TABLE `vendor_earnings` (
  `order_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `gross_amount` decimal(14,0) NOT NULL,
  `platform_fee` decimal(14,0) NOT NULL DEFAULT '0',
  `net_amount` decimal(14,0) NOT NULL,
  `status` enum('held','eligible','cancelled','paid') NOT NULL DEFAULT 'held',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `eligible_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `payout_reference` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_accounts`
--

DROP TABLE IF EXISTS `wallet_accounts`;
CREATE TABLE `wallet_accounts` (
  `user_id` int NOT NULL,
  `balance` decimal(14,0) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_topups`
--

DROP TABLE IF EXISTS `wallet_topups`;
CREATE TABLE `wallet_topups` (
  `id` bigint NOT NULL,
  `user_id` int NOT NULL,
  `amount` decimal(14,0) NOT NULL,
  `status` enum('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
  `payos_order_code` bigint NOT NULL,
  `payment_link_id` varchar(100) DEFAULT NULL,
  `checkout_url` varchar(1000) DEFAULT NULL,
  `bank_reference` varchar(120) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

DROP TABLE IF EXISTS `wallet_transactions`;
CREATE TABLE `wallet_transactions` (
  `id` bigint NOT NULL,
  `user_id` int NOT NULL,
  `direction` enum('credit','debit') NOT NULL,
  `amount` decimal(14,0) NOT NULL,
  `balance_after` decimal(14,0) NOT NULL,
  `type` enum('topup','order_payment','order_refund','demo_credit') NOT NULL,
  `reference_type` varchar(30) NOT NULL,
  `reference_id` bigint NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `cod_collections`
--
ALTER TABLE `cod_collections`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_cod_collector` (`collected_by`);

--
-- Indexes for table `custom_quotes`
--
ALTER TABLE `custom_quotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quotes_user` (`user_id`),
  ADD KEY `fk_quotes_vendor` (`vendor_id`),
  ADD KEY `fk_quotes_material` (`material_id`);

--
-- Indexes for table `demo_payments`
--
ALTER TABLE `demo_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD UNIQUE KEY `topup_id` (`topup_id`),
  ADD KEY `idx_demo_status` (`status`,`created_at`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_materials_vendor` (`vendor_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `fk_orders_user` (`user_id`),
  ADD KEY `fk_orders_vendor` (`vendor_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_items_order` (`order_id`),
  ADD KEY `fk_items_product` (`product_id`),
  ADD KEY `fk_items_quote` (`custom_quote_id`);

--
-- Indexes for table `order_payments`
--
ALTER TABLE `order_payments`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_payment_status` (`status`);

--
-- Indexes for table `payos_transactions`
--
ALTER TABLE `payos_transactions`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `payos_order_code` (`payos_order_code`),
  ADD KEY `idx_payos_status` (`status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_vendor` (`vendor_id`),
  ADD KEY `fk_products_category` (`category_id`),
  ADD KEY `fk_products_material` (`material_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reviews_user` (`user_id`),
  ADD KEY `fk_reviews_product` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vendors_user` (`user_id`);

--
-- Indexes for table `vendor_earnings`
--
ALTER TABLE `vendor_earnings`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_vendor_earn_vendor` (`vendor_id`);

--
-- Indexes for table `wallet_accounts`
--
ALTER TABLE `wallet_accounts`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `wallet_topups`
--
ALTER TABLE `wallet_topups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payos_order_code` (`payos_order_code`),
  ADD UNIQUE KEY `bank_reference` (`bank_reference`),
  ADD KEY `idx_topups_user` (`user_id`,`created_at`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_wallet_event` (`user_id`,`type`,`reference_type`,`reference_id`),
  ADD KEY `idx_wallet_user_time` (`user_id`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `custom_quotes`
--
ALTER TABLE `custom_quotes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `demo_payments`
--
ALTER TABLE `demo_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wallet_topups`
--
ALTER TABLE `wallet_topups`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cod_collections`
--
ALTER TABLE `cod_collections`
  ADD CONSTRAINT `fk_cod_collector` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_cod_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_quotes`
--
ALTER TABLE `custom_quotes`
  ADD CONSTRAINT `fk_quotes_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_quotes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_quotes_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `demo_payments`
--
ALTER TABLE `demo_payments`
  ADD CONSTRAINT `fk_demo_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_demo_topup` FOREIGN KEY (`topup_id`) REFERENCES `wallet_topups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `fk_materials_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_orders_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_items_quote` FOREIGN KEY (`custom_quote_id`) REFERENCES `custom_quotes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_payments`
--
ALTER TABLE `order_payments`
  ADD CONSTRAINT `fk_order_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payos_transactions`
--
ALTER TABLE `payos_transactions`
  ADD CONSTRAINT `fk_payos_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_products_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_products_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendors`
--
ALTER TABLE `vendors`
  ADD CONSTRAINT `fk_vendors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_earnings`
--
ALTER TABLE `vendor_earnings`
  ADD CONSTRAINT `fk_vendor_earn_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_vendor_earn_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `wallet_accounts`
--
ALTER TABLE `wallet_accounts`
  ADD CONSTRAINT `fk_wallet_account_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_topups`
--
ALTER TABLE `wallet_topups`
  ADD CONSTRAINT `fk_wallet_topup_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `fk_wallet_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

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

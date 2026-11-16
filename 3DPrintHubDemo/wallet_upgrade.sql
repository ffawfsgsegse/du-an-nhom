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

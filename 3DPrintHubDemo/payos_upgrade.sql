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

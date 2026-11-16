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

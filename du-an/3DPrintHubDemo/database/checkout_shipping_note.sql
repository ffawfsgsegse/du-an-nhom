SET @shipping_note_ddl = IF(
    EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_note'
    ),
    'DO 0',
    'ALTER TABLE `orders` ADD COLUMN `shipping_note` VARCHAR(255) NULL AFTER `shipping_address`'
);
PREPARE shipping_note_stmt FROM @shipping_note_ddl;
EXECUTE shipping_note_stmt;
DEALLOCATE PREPARE shipping_note_stmt;

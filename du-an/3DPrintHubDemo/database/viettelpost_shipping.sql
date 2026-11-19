USE `3dprinthub_db`;

SET @product_columns = (
    SELECT GROUP_CONCAT(CONCAT('ADD COLUMN ', additions.definition) ORDER BY additions.position SEPARATOR ', ')
    FROM (
        SELECT 1 AS position, 'shipping_weight_grams' AS name, '`shipping_weight_grams` INT UNSIGNED NULL' AS definition
        UNION ALL SELECT 2, 'shipping_length_cm', '`shipping_length_cm` INT UNSIGNED NULL'
        UNION ALL SELECT 3, 'shipping_width_cm', '`shipping_width_cm` INT UNSIGNED NULL'
        UNION ALL SELECT 4, 'shipping_height_cm', '`shipping_height_cm` INT UNSIGNED NULL'
    ) AS additions
    WHERE NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS c
        WHERE c.TABLE_SCHEMA = DATABASE() AND c.TABLE_NAME = 'products' AND c.COLUMN_NAME = additions.name
    )
);
SET @product_ddl = COALESCE(CONCAT('ALTER TABLE `products` ', @product_columns), 'DO 0');
PREPARE viettel_product_stmt FROM @product_ddl;
EXECUTE viettel_product_stmt;
DEALLOCATE PREPARE viettel_product_stmt;

SET @order_columns = (
    SELECT GROUP_CONCAT(CONCAT('ADD COLUMN ', additions.definition) ORDER BY additions.position SEPARATOR ', ')
    FROM (
        SELECT 1 AS position, 'shipping_fee' AS name, '`shipping_fee` DECIMAL(12,2) NOT NULL DEFAULT 0' AS definition
        UNION ALL SELECT 2, 'shipping_service_code', '`shipping_service_code` VARCHAR(50) NULL'
    ) AS additions
    WHERE NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS c
        WHERE c.TABLE_SCHEMA = DATABASE() AND c.TABLE_NAME = 'orders' AND c.COLUMN_NAME = additions.name
    )
);
SET @order_ddl = COALESCE(CONCAT('ALTER TABLE `orders` ', @order_columns), 'DO 0');
PREPARE viettel_order_stmt FROM @order_ddl;
EXECUTE viettel_order_stmt;
DEALLOCATE PREPARE viettel_order_stmt;

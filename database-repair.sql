-- Idempotent repair script for live environments
-- Safe to run multiple times

SET @db := DATABASE();

-- categories.description
SELECT COUNT(*) INTO @has_categories_description
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'description';

SET @sql := IF(
    @has_categories_description = 0,
    'ALTER TABLE categories ADD COLUMN description TEXT NULL AFTER slug',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- products.updated_at
SELECT COUNT(*) INTO @has_products_updated_at
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'products' AND COLUMN_NAME = 'updated_at';

SET @sql := IF(
    @has_products_updated_at = 0,
    'ALTER TABLE products ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- orders.updated_at
SELECT COUNT(*) INTO @has_orders_updated_at
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'updated_at';

SET @sql := IF(
    @has_orders_updated_at = 0,
    'ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Align enum values used by API code
ALTER TABLE orders
    MODIFY COLUMN status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned')
    NOT NULL DEFAULT 'pending';

ALTER TABLE orders
    MODIFY COLUMN payment_status ENUM('pending','paid','notified','cancelled','failed','refunded')
    NOT NULL DEFAULT 'pending';

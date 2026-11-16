-- CrownCare: remove brands from the hard-coded site.
-- Drops the product_brand column (with its foreign key and index) and the brands table.
-- Safe to run more than once: each step only runs if the thing still exists.

DELIMITER $$

DROP PROCEDURE IF EXISTS migrate_remove_brands$$

CREATE PROCEDURE migrate_remove_brands()
BEGIN
    -- Drop the foreign key from products to brands.
    IF EXISTS (
        SELECT 1
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'products'
          AND CONSTRAINT_NAME = 'fk_product_brand'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE products DROP FOREIGN KEY fk_product_brand;
    END IF;

    -- Drop the index on product_brand.
    IF EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'products'
          AND INDEX_NAME = 'idx_product_brand'
    ) THEN
        ALTER TABLE products DROP INDEX idx_product_brand;
    END IF;

    -- Drop the product_brand column.
    IF EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'products'
          AND COLUMN_NAME = 'product_brand'
    ) THEN
        ALTER TABLE products DROP COLUMN product_brand;
    END IF;
END$$

DELIMITER ;

CALL migrate_remove_brands();

DROP PROCEDURE IF EXISTS migrate_remove_brands;

DROP TABLE IF EXISTS brands;

INSERT IGNORE INTO schema_migrations (migration_id)
VALUES ('003_remove_brands');

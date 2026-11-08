
USE crowncare;

CREATE TABLE IF NOT EXISTS schema_migrations (
    migration_id VARCHAR(100) NOT NULL,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (migration_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER $$

DROP PROCEDURE IF EXISTS migrate_shop_catalogue_extensions$$

CREATE PROCEDURE migrate_shop_catalogue_extensions()
BEGIN
    -- Add parent_category_id if it does not exist.
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'categories'
          AND COLUMN_NAME = 'parent_category_id'
    ) THEN
        ALTER TABLE categories
        ADD COLUMN parent_category_id INT NULL AFTER cat_name;
    END IF;

    -- Add the category hierarchy foreign key if it does not exist.
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'categories'
          AND CONSTRAINT_NAME = 'fk_category_parent'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE categories
        ADD CONSTRAINT fk_category_parent
        FOREIGN KEY (parent_category_id)
        REFERENCES categories(cat_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
    END IF;

    -- Add product creation date if it does not exist.
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'products'
          AND COLUMN_NAME = 'created_at'
    ) THEN
        ALTER TABLE products
        ADD COLUMN created_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP;
    END IF;
END$$

DELIMITER ;

CALL migrate_shop_catalogue_extensions();

DROP PROCEDURE IF EXISTS migrate_shop_catalogue_extensions;

-- Product sizes are stored separately so products can have
-- multiple sizes and sizes can be shared across products.

CREATE TABLE IF NOT EXISTS product_sizes (
    size_id INT NOT NULL AUTO_INCREMENT,
    size_name VARCHAR(50) NOT NULL,
    PRIMARY KEY (size_id),
    UNIQUE KEY unique_size_name (size_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_size_options (
    product_id INT NOT NULL,
    size_id INT NOT NULL,

    PRIMARY KEY (product_id, size_id),

    CONSTRAINT fk_product_size_product
        FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_product_size_size
        FOREIGN KEY (size_id)
        REFERENCES product_sizes(size_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial size choices. Admins can assign only relevant sizes
-- to each product when adding or editing it.

INSERT IGNORE INTO product_sizes (size_name) VALUES
('Small'),
('Medium'),
('Large'),
('Extra Large'),
('50ml'),
('100ml'),
('150ml'),
('200ml'),
('250ml'),
('300ml'),
('500ml'),
('1 Litre'),
('2 Litre'),
('50g'),
('100g'),
('150g'),
('200g'),
('250g'),
('500g'),
('1kg');

INSERT IGNORE INTO schema_migrations (migration_id)
VALUES ('001_shop_catalogue_extensions');

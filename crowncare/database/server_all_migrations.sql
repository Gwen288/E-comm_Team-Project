-- CrownCare: all database migrations (001 to 008) in one file, for the school server.
-- Paste into phpMyAdmin > your database > SQL, and click Go. Safe to run more than once.
-- Works on MySQL and MariaDB. Has no USE line, so it runs on whichever database is selected.


-- ===================== 001_shop_catalogue_extensions.sql =====================



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

-- ===================== 002_cms_product_images.sql =====================
-- CrownCare: point each product at the photo downloaded from the CMS site.
-- Run after download_crowncare_images.ps1 has filled images/products/.
-- Products are matched by title (not case-sensitive), so titles must match the CMS names.
-- Safe to run more than once.

UPDATE products SET product_image = CASE TRIM(product_title)
    WHEN 'CrownCare Scalp Care Kit' THEN 'crowncare-scalp-care-kit.jpg'
    WHEN 'CrownCare 4C Moisture Starter Kit' THEN 'crowncare-4c-moisture-starter-kit.jpg'
    WHEN 'CrownCare Sleek Edge Control' THEN 'crowncare-sleek-edge-control.jpg'
    WHEN 'CrownCare Flaxseed Styling Gel' THEN 'crowncare-flaxseed-styling-gel.jpg'
    WHEN 'CrownCare Curl Defining Custard' THEN 'crowncare-curl-defining-custard.jpg'
    WHEN 'CrownCare Castor Oil Scalp Serum' THEN 'crowncare-castor-oil-scalp-serum.jpg'
    WHEN 'CrownCare Baobab and Coconut Hair Oil' THEN 'crowncare-baobab-and-coconut-hair-oil.jpg'
    WHEN 'CrownCare Nourishing Hair Butter' THEN 'crowncare-nourishing-hair-butter.jpg'
    WHEN 'CrownCare Scalp and Strength Oil' THEN 'crowncare-scalp-and-strength-oil.jpg'
    WHEN 'CrownCare Aloe Daily Hydrating Spray' THEN 'crowncare-aloe-daily-hydrating-spray.jpg'
    WHEN 'CrownCare Whipped Butter Moisturiser' THEN 'crowncare-whipped-butter-moisturiser.jpg'
    WHEN 'CrownCare Leave-In Milk' THEN 'crowncare-leave-in-milk.jpg'
    WHEN 'CrownCare Hibiscus Conditioner' THEN 'crowncare-hibiscus-conditioner.jpg'
    WHEN 'CrownCare Protein Repair Treatment' THEN 'crowncare-protein-repair-treatment.jpg'
    WHEN 'CrownCare Deep Moisture Masque' THEN 'crowncare-deep-moisture-masque.jpg'
    WHEN 'CrownCare Detangling Conditioner' THEN 'crowncare-detangling-conditioner.jpg'
    WHEN 'CrownCare Scalp Soothing Shampoo' THEN 'crowncare-scalp-soothing-shampoo.jpg'
    WHEN 'CrownCare Black Soap Clarifying Shampoo' THEN 'crowncare-black-soap-clarifying-shampoo.jpg'
    WHEN 'CrownCare Gentle Co-Wash' THEN 'crowncare-gentle-co-wash.jpg'
    WHEN 'CrownCare Moisture Rich Cleanser' THEN 'crowncare-moisture-rich-cleanser.jpg'
    ELSE product_image
END;

-- Products that still have no CMS photo (their title differs from the CMS name):
SELECT product_id, product_title, product_image
FROM products
WHERE TRIM(product_title) NOT IN (
    'CrownCare Scalp Care Kit',
    'CrownCare 4C Moisture Starter Kit',
    'CrownCare Sleek Edge Control',
    'CrownCare Flaxseed Styling Gel',
    'CrownCare Curl Defining Custard',
    'CrownCare Castor Oil Scalp Serum',
    'CrownCare Baobab and Coconut Hair Oil',
    'CrownCare Nourishing Hair Butter',
    'CrownCare Scalp and Strength Oil',
    'CrownCare Aloe Daily Hydrating Spray',
    'CrownCare Whipped Butter Moisturiser',
    'CrownCare Leave-In Milk',
    'CrownCare Hibiscus Conditioner',
    'CrownCare Protein Repair Treatment',
    'CrownCare Deep Moisture Masque',
    'CrownCare Detangling Conditioner',
    'CrownCare Scalp Soothing Shampoo',
    'CrownCare Black Soap Clarifying Shampoo',
    'CrownCare Gentle Co-Wash',
    'CrownCare Moisture Rich Cleanser'
);

INSERT IGNORE INTO schema_migrations (migration_id)
VALUES ('002_cms_product_images');

-- ===================== 003_remove_brands.sql =====================
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

-- ===================== 004_crowncare_products.sql =====================
-- CrownCare: add the categories and the 20 CrownCare products from the CMS (Wix) site (no brand).
-- Run on the crowncare database after migrations 001 and 003.
-- Run download_crowncare_images.ps1 first so the photos exist in images/products/.
-- Safe to run more than once: rows that already exist are skipped.

-- Run 003_remove_brands.sql first: products have no brand.

-- 1. Main categories
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Shampoos', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Shampoos');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Cleansers', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Cleansers');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Conditioners', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Conditioners');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Hair Creams', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Hair Creams');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Oils', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Oils');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Styling', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Styling');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Kits and Bundles', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Kits and Bundles');

-- 2. Conditioner subcategories
SET @conditioners = (SELECT cat_id FROM categories WHERE cat_name = 'Conditioners' LIMIT 1);
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Rinse-out Conditioners', @conditioners FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Rinse-out Conditioners');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Deep Conditioners', @conditioners FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Deep Conditioners');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Leave-in Conditioners', @conditioners FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Leave-in Conditioners');

-- 3. Sizes used by the products (most already exist from migration 001)
INSERT IGNORE INTO product_sizes (size_name) VALUES ('100g'), ('100ml'), ('150ml'), ('200ml'), ('250g'), ('250ml'), ('300ml'), ('30ml'), ('500ml'), ('50g'), ('50ml');

-- 4. Products (price is the smallest size's price, as on the CMS)
INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Kits and Bundles' LIMIT 1), 'CrownCare Scalp Care Kit', 255,
       'The Scalp Soothing Shampoo (250 ml), Scalp and Strength Oil (50 ml) and Castor Oil Scalp Serum (30 ml) in one kit: a gentle scalp-care routine. Results vary; this kit is not a medical treatment. Saves GH₵45 compared with buying the three separately. Suitable for hair types 4A, 4B, 4C. Helps with: Thinning, Itchy scalp. FDA registration no.: SAMPLE-0020 (sample).',
       'crowncare-scalp-care-kit.jpg', 'kits and bundles, thinning, itchy scalp, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Scalp Care Kit');

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Kits and Bundles' LIMIT 1), 'CrownCare 4C Moisture Starter Kit', 250,
       'The Moisture Rich Cleanser (250 ml), Deep Moisture Masque (150 ml) and Whipped Butter Moisturiser (150 ml) in one kit: a complete wash-day routine for very dry, tightly coiled hair. Saves GH₵50 compared with buying the three products separately. Suitable for hair types 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0019 (sample).',
       'crowncare-4c-moisture-starter-kit.jpg', 'kits and bundles, dryness, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare 4C Moisture Starter Kit');

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Styling' LIMIT 1), 'CrownCare Sleek Edge Control', 50,
       'A firm-hold edge control made with shea butter. How to use: apply a small amount to edges and lay with a soft brush. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Frizz. FDA registration no.: SAMPLE-0018 (sample).',
       'crowncare-sleek-edge-control.jpg', 'styling, frizz, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Sleek Edge Control');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Sleek Edge Control' AND s.size_name = '50g';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Sleek Edge Control' AND s.size_name = '100g';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Styling' LIMIT 1), 'CrownCare Flaxseed Styling Gel', 65,
       'A flaxseed gel for defined, frizz-controlled styles. How to use: apply over a leave-in on wet hair, then style. Scrunch out any stiffness once dry. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Frizz. FDA registration no.: SAMPLE-0017 (sample).',
       'crowncare-flaxseed-styling-gel.jpg', 'styling, frizz, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Flaxseed Styling Gel');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Flaxseed Styling Gel' AND s.size_name = '150ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Flaxseed Styling Gel' AND s.size_name = '250ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Styling' LIMIT 1), 'CrownCare Curl Defining Custard', 90,
       'A soft-hold custard for twist-outs and wash-and-gos. How to use: apply to wet hair in sections, smooth through, and leave to air dry or diffuse. Suitable for hair types 3C, 4A, 4B. Helps with: Frizz. FDA registration no.: SAMPLE-0016 (sample).',
       'crowncare-curl-defining-custard.jpg', 'styling, frizz, 3c, 4a, 4b' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Curl Defining Custard');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Curl Defining Custard' AND s.size_name = '150ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Curl Defining Custard' AND s.size_name = '250ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Oils' LIMIT 1), 'CrownCare Castor Oil Scalp Serum', 100,
       'A dropper serum with castor oil for edges and the scalp. How to use: apply a few drops to the scalp or edges and massage in. Patch test first. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Thinning, Itchy scalp. FDA registration no.: SAMPLE-0015 (sample).',
       'crowncare-castor-oil-scalp-serum.jpg', 'oils, thinning, itchy scalp, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Castor Oil Scalp Serum');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Castor Oil Scalp Serum' AND s.size_name = '30ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Castor Oil Scalp Serum' AND s.size_name = '50ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Oils' LIMIT 1), 'CrownCare Baobab and Coconut Hair Oil', 85,
       'A light sealing oil with baobab and coconut for ends and protective styles. How to use: apply to damp hair after a leave-in, or to braids and twists to add shine. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Breakage. FDA registration no.: SAMPLE-0014 (sample).',
       'crowncare-baobab-and-coconut-hair-oil.jpg', 'oils, breakage, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Baobab and Coconut Hair Oil');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Baobab and Coconut Hair Oil' AND s.size_name = '50ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Baobab and Coconut Hair Oil' AND s.size_name = '100ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Hair Creams' LIMIT 1), 'CrownCare Nourishing Hair Butter', 45,
       'A rich butter blend made with Ghanaian shea and cocoa butter for sealing in moisture. How to use: warm a small amount between your palms and apply to ends to seal in moisture. Suitable for hair types 4A, 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0013 (sample).',
       'crowncare-nourishing-hair-butter.jpg', 'hair creams, dryness, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Nourishing Hair Butter');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Nourishing Hair Butter' AND s.size_name = '100g';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Nourishing Hair Butter' AND s.size_name = '250g';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Oils' LIMIT 1), 'CrownCare Scalp and Strength Oil', 110,
       'A blend of moringa, rosemary and jojoba oils for a regular scalp-care routine. Supports a healthy scalp routine; results vary and no growth is guaranteed. How to use: massage a few drops into the scalp two to three times a week. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Thinning. FDA registration no.: SAMPLE-0012 (sample).',
       'crowncare-scalp-and-strength-oil.jpg', 'oils, thinning, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Scalp and Strength Oil');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp and Strength Oil' AND s.size_name = '50ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp and Strength Oil' AND s.size_name = '100ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Leave-in Conditioners' LIMIT 1), 'CrownCare Aloe Daily Hydrating Spray', 55,
       'A water-based aloe vera spray to refresh hair between wash days. How to use: mist onto hair daily or as needed, then seal with an oil or butter. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0011 (sample).',
       'crowncare-aloe-daily-hydrating-spray.jpg', 'leave-in conditioners, dryness, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Aloe Daily Hydrating Spray');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Aloe Daily Hydrating Spray' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Aloe Daily Hydrating Spray' AND s.size_name = '200ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Hair Creams' LIMIT 1), 'CrownCare Whipped Butter Moisturiser', 95,
       'A thick whipped butter with shea and cocoa butter that seals in moisture on tightly coiled hair. How to use: apply a small amount to damp hair after a leave-in, focusing on the ends. Suitable for hair types 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0010 (sample).',
       'crowncare-whipped-butter-moisturiser.jpg', 'hair creams, dryness, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Whipped Butter Moisturiser');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Whipped Butter Moisturiser' AND s.size_name = '150ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Whipped Butter Moisturiser' AND s.size_name = '250ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Leave-in Conditioners' LIMIT 1), 'CrownCare Leave-In Milk', 70,
       'A lightweight leave-in that softens hair without weighing it down. How to use: spray or smooth onto damp hair, then style as usual. Suitable for hair types 3C, 4A, 4B. Helps with: Dryness. FDA registration no.: SAMPLE-0009 (sample).',
       'crowncare-leave-in-milk.jpg', 'leave-in conditioners, dryness, 3c, 4a, 4b' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Leave-In Milk');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Leave-In Milk' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Leave-In Milk' AND s.size_name = '200ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Rinse-out Conditioners' LIMIT 1), 'CrownCare Hibiscus Conditioner', 40,
       'A softening conditioner with hibiscus for looser curl patterns. How to use: apply after cleansing, leave for three minutes, rinse with cool water. Suitable for hair types 3C, 4A. Helps with: Frizz. FDA registration no.: SAMPLE-0008 (sample).',
       'crowncare-hibiscus-conditioner.jpg', 'rinse-out conditioners, frizz, 3c, 4a' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Hibiscus Conditioner');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Hibiscus Conditioner' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Hibiscus Conditioner' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Hibiscus Conditioner' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Deep Conditioners' LIMIT 1), 'CrownCare Protein Repair Treatment', 130,
       'A light protein treatment for hair that snaps or feels weak. Use once a month. How to use: apply to clean hair, leave for 10 minutes, rinse, then follow with a moisturising conditioner. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Breakage. FDA registration no.: SAMPLE-0007 (sample).',
       'crowncare-protein-repair-treatment.jpg', 'deep conditioners, breakage, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Protein Repair Treatment');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Protein Repair Treatment' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Protein Repair Treatment' AND s.size_name = '200ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Deep Conditioners' LIMIT 1), 'CrownCare Deep Moisture Masque', 120,
       'A rich weekly masque with shea butter and baobab oil for very dry hair. How to use: apply to clean, damp hair, cover with a plastic cap for 20 to 30 minutes, rinse. Suitable for hair types 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0006 (sample).',
       'crowncare-deep-moisture-masque.jpg', 'deep conditioners, dryness, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Deep Moisture Masque');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Deep Moisture Masque' AND s.size_name = '150ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Deep Moisture Masque' AND s.size_name = '300ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Rinse-out Conditioners' LIMIT 1), 'CrownCare Detangling Conditioner', 40,
       'A slippery rinse-out conditioner that makes detangling easier. How to use: apply generously after cleansing, detangle in sections with a wide-tooth comb, rinse. Suitable for hair types 4A, 4B, 4C. Helps with: Breakage, Tangles. FDA registration no.: SAMPLE-0005 (sample).',
       'crowncare-detangling-conditioner.jpg', 'rinse-out conditioners, breakage, tangles, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Detangling Conditioner');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Detangling Conditioner' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Detangling Conditioner' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Detangling Conditioner' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Shampoos' LIMIT 1), 'CrownCare Scalp Soothing Shampoo', 45,
       'A fragrance-free shampoo with moringa for sensitive, itchy scalps. How to use: massage gently into the scalp, leave for one minute, rinse. If irritation continues, stop use and see a professional. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Itchy scalp. FDA registration no.: SAMPLE-0004 (sample).',
       'crowncare-scalp-soothing-shampoo.jpg', 'shampoos, itchy scalp, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Scalp Soothing Shampoo');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp Soothing Shampoo' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp Soothing Shampoo' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp Soothing Shampoo' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Shampoos' LIMIT 1), 'CrownCare Black Soap Clarifying Shampoo', 35,
       'A clarifying shampoo made with African black soap to lift product buildup. Best used every two to four weeks. How to use: lather on the scalp, rinse thoroughly, then deep condition. Suitable for hair types 4A, 4B, 4C. Helps with: Scalp buildup. FDA registration no.: SAMPLE-0003 (sample).',
       'crowncare-black-soap-clarifying-shampoo.jpg', 'shampoos, scalp buildup, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Black Soap Clarifying Shampoo');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Black Soap Clarifying Shampoo' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Black Soap Clarifying Shampoo' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Black Soap Clarifying Shampoo' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Cleansers' LIMIT 1), 'CrownCare Gentle Co-Wash', 35,
       'A conditioning cleanser with baobab for wash days between shampoos. Cleans lightly while leaving curls soft. How to use: work through wet hair from roots to ends, detangle with fingers, rinse. Suitable for hair types 3C, 4A, 4B. Helps with: Dryness, Frizz. FDA registration no.: SAMPLE-0002 (sample).',
       'crowncare-gentle-co-wash.jpg', 'cleansers, dryness, frizz, 3c, 4a, 4b' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Gentle Co-Wash');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Gentle Co-Wash' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Gentle Co-Wash' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Gentle Co-Wash' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Cleansers' LIMIT 1), 'CrownCare Moisture Rich Cleanser', 45,
       'A gentle, low-lather cleanser with shea butter for coily hair that dries out easily. Cleanses without stripping natural oils. How to use: apply to wet hair, massage into the scalp, rinse well. Follow with a conditioner. Suitable for hair types 4A, 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0001 (sample).',
       'crowncare-moisture-rich-cleanser.jpg', 'cleansers, dryness, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Moisture Rich Cleanser');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Moisture Rich Cleanser' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Moisture Rich Cleanser' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Moisture Rich Cleanser' AND s.size_name = '500ml';

-- 5. Record the migration
INSERT IGNORE INTO schema_migrations (migration_id) VALUES ('004_crowncare_products');

-- 6. Check: should show 20 products with their photo and category
SELECT p.product_id, p.product_title, p.product_price, c.cat_name, p.product_image
FROM products p JOIN categories c ON c.cat_id = p.product_cat
ORDER BY c.cat_name, p.product_title;

-- ===================== 005_size_prices_and_cart_sizes.sql =====================
-- CrownCare: sizes with their own prices, and the chosen size in the cart and in orders.
-- Matches the CMS shop: each size has its own price and photo, and the cart shows the size.
-- Run after migrations 001, 003 and 004. Safe to run more than once.

DELIMITER $$
DROP PROCEDURE IF EXISTS migrate_size_prices$$
CREATE PROCEDURE migrate_size_prices()
BEGIN
    -- Price for each size of a product (NULL means use the product price).
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'product_size_options' AND COLUMN_NAME = 'price') THEN
        ALTER TABLE product_size_options ADD COLUMN price DOUBLE NULL AFTER size_id;
    END IF;

    -- The chosen size of each cart line (0 means the product has no sizes).
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cart' AND COLUMN_NAME = 'size_id') THEN
        ALTER TABLE cart
            ADD COLUMN size_id INT NOT NULL DEFAULT 0 AFTER p_id,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (c_id, p_id, size_id);
    END IF;

    -- The size and the price paid for each order line.
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orderdetails' AND COLUMN_NAME = 'size_id') THEN
        ALTER TABLE orderdetails
            ADD COLUMN size_id INT NOT NULL DEFAULT 0 AFTER product_id,
            ADD COLUMN unit_price DOUBLE NULL AFTER qty,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (order_id, product_id, size_id);
    END IF;
END$$
DELIMITER ;
CALL migrate_size_prices();
DROP PROCEDURE IF EXISTS migrate_size_prices;

-- Size prices from the CMS site.
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 50 WHERE p.product_title = 'CrownCare Sleek Edge Control' AND s.size_name = '50g';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 80 WHERE p.product_title = 'CrownCare Sleek Edge Control' AND s.size_name = '100g';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 65 WHERE p.product_title = 'CrownCare Flaxseed Styling Gel' AND s.size_name = '150ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 105 WHERE p.product_title = 'CrownCare Flaxseed Styling Gel' AND s.size_name = '250ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 90 WHERE p.product_title = 'CrownCare Curl Defining Custard' AND s.size_name = '150ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 145 WHERE p.product_title = 'CrownCare Curl Defining Custard' AND s.size_name = '250ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 100 WHERE p.product_title = 'CrownCare Castor Oil Scalp Serum' AND s.size_name = '30ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 160 WHERE p.product_title = 'CrownCare Castor Oil Scalp Serum' AND s.size_name = '50ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 85 WHERE p.product_title = 'CrownCare Baobab and Coconut Hair Oil' AND s.size_name = '50ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 135 WHERE p.product_title = 'CrownCare Baobab and Coconut Hair Oil' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 45 WHERE p.product_title = 'CrownCare Nourishing Hair Butter' AND s.size_name = '100g';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 70 WHERE p.product_title = 'CrownCare Nourishing Hair Butter' AND s.size_name = '250g';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 110 WHERE p.product_title = 'CrownCare Scalp and Strength Oil' AND s.size_name = '50ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 175 WHERE p.product_title = 'CrownCare Scalp and Strength Oil' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 55 WHERE p.product_title = 'CrownCare Aloe Daily Hydrating Spray' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 90 WHERE p.product_title = 'CrownCare Aloe Daily Hydrating Spray' AND s.size_name = '200ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 95 WHERE p.product_title = 'CrownCare Whipped Butter Moisturiser' AND s.size_name = '150ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 150 WHERE p.product_title = 'CrownCare Whipped Butter Moisturiser' AND s.size_name = '250ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 70 WHERE p.product_title = 'CrownCare Leave-In Milk' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 110 WHERE p.product_title = 'CrownCare Leave-In Milk' AND s.size_name = '200ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 40 WHERE p.product_title = 'CrownCare Hibiscus Conditioner' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 75 WHERE p.product_title = 'CrownCare Hibiscus Conditioner' AND s.size_name = '250ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 130 WHERE p.product_title = 'CrownCare Hibiscus Conditioner' AND s.size_name = '500ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 130 WHERE p.product_title = 'CrownCare Protein Repair Treatment' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 210 WHERE p.product_title = 'CrownCare Protein Repair Treatment' AND s.size_name = '200ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 120 WHERE p.product_title = 'CrownCare Deep Moisture Masque' AND s.size_name = '150ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 190 WHERE p.product_title = 'CrownCare Deep Moisture Masque' AND s.size_name = '300ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 40 WHERE p.product_title = 'CrownCare Detangling Conditioner' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 80 WHERE p.product_title = 'CrownCare Detangling Conditioner' AND s.size_name = '250ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 135 WHERE p.product_title = 'CrownCare Detangling Conditioner' AND s.size_name = '500ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 45 WHERE p.product_title = 'CrownCare Scalp Soothing Shampoo' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 90 WHERE p.product_title = 'CrownCare Scalp Soothing Shampoo' AND s.size_name = '250ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 155 WHERE p.product_title = 'CrownCare Scalp Soothing Shampoo' AND s.size_name = '500ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 35 WHERE p.product_title = 'CrownCare Black Soap Clarifying Shampoo' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 65 WHERE p.product_title = 'CrownCare Black Soap Clarifying Shampoo' AND s.size_name = '250ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 110 WHERE p.product_title = 'CrownCare Black Soap Clarifying Shampoo' AND s.size_name = '500ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 35 WHERE p.product_title = 'CrownCare Gentle Co-Wash' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 70 WHERE p.product_title = 'CrownCare Gentle Co-Wash' AND s.size_name = '250ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 120 WHERE p.product_title = 'CrownCare Gentle Co-Wash' AND s.size_name = '500ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 45 WHERE p.product_title = 'CrownCare Moisture Rich Cleanser' AND s.size_name = '100ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 85 WHERE p.product_title = 'CrownCare Moisture Rich Cleanser' AND s.size_name = '250ml';
UPDATE product_size_options o JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id SET o.price = 145 WHERE p.product_title = 'CrownCare Moisture Rich Cleanser' AND s.size_name = '500ml';

INSERT IGNORE INTO schema_migrations (migration_id) VALUES ('005_size_prices_and_cart_sizes');

-- Check: each size with its price
SELECT p.product_title, s.size_name, o.price FROM product_size_options o
JOIN products p ON p.product_id = o.product_id JOIN product_sizes s ON s.size_id = o.size_id
ORDER BY p.product_title, o.price;

-- ===================== 006_consultations.sql =====================
-- CrownCare: hair consultation bookings.
-- One booking per date and time slot, so a slot cannot be booked twice.
-- Guests can book too (customer_id is NULL); logged-in bookings keep the customer id.
-- Safe to run more than once.

CREATE TABLE IF NOT EXISTS consultations (
    consultation_id INT NOT NULL AUTO_INCREMENT,
    reference VARCHAR(20) NOT NULL,
    customer_id INT NULL,
    service VARCHAR(80) NOT NULL,
    consultation_format VARCHAR(20) NOT NULL,
    consultation_date DATE NOT NULL,
    consultation_time TIME NOT NULL,
    duration_minutes INT NOT NULL,
    price DOUBLE NOT NULL DEFAULT 0,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    hair_type VARCHAR(20) DEFAULT NULL,
    main_concern VARCHAR(60) DEFAULT NULL,
    notes VARCHAR(500) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'booked',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (consultation_id),
    UNIQUE KEY unique_reference (reference),
    UNIQUE KEY unique_slot (consultation_date, consultation_time),
    KEY idx_consultation_customer (customer_id),

    CONSTRAINT fk_consultation_customer
        FOREIGN KEY (customer_id)
        REFERENCES customer (customer_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO schema_migrations (migration_id)
VALUES ('006_consultations');

-- ===================== 007_remove_consultation_hair_type.sql =====================
-- CrownCare: hair type is no longer asked on the consultation booking form.
-- Removes the unused hair_type column from consultations.
-- Run once on the crowncare database, after 006_consultations.sql.
-- Works on MariaDB (XAMPP) and MySQL (school server). Safe to run more than once.

DELIMITER $$

DROP PROCEDURE IF EXISTS migrate_007$$
CREATE PROCEDURE migrate_007()
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultations' AND COLUMN_NAME = 'hair_type') THEN
        ALTER TABLE consultations DROP COLUMN hair_type;
    END IF;
END$$

DELIMITER ;

CALL migrate_007();
DROP PROCEDURE IF EXISTS migrate_007;

INSERT IGNORE INTO schema_migrations (migration_id)
VALUES ('007_remove_consultation_hair_type');

-- ===================== 008_consultant_portal.sql =====================
-- CrownCare: consultant area.
-- Moves the consultation services, prices and opening hours into the database
-- so consultants can change them, and adds what the consultant pages need.
-- Consultant accounts are customer accounts with user_role = 3.
-- Run once on the crowncare database, after 007. Safe to run more than once.

-- Consultation services and their prices (GH₵).
CREATE TABLE IF NOT EXISTS consultation_services (
    service_key VARCHAR(20) NOT NULL,
    service_name VARCHAR(80) NOT NULL,
    duration_minutes INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (service_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO consultation_services (service_key, service_name, duration_minutes, price, description, sort_order) VALUES
('assessment', 'Hair and Scalp Assessment', 45, 100.00, 'A full look at your hair and scalp: hair type, porosity, density and condition, followed by a written care plan.', 1),
('routine', 'Routine Planning', 30, 60.00, 'Build a wash-day and moisture routine around your hair, your schedule and your budget, with product recommendations.', 2),
('protective', 'Protective Style Consultation', 30, 60.00, 'Choose protective styles that suit your hair, and learn how to care for your edges and scalp while you wear them.', 3);

-- Weekly opening hours: one row per start time (weekday 1 = Monday ... 7 = Sunday).
CREATE TABLE IF NOT EXISTS consultation_hours (
    weekday TINYINT NOT NULL,
    start_time TIME NOT NULL,
    PRIMARY KEY (weekday, start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Filled with the current hours only when the table is empty, so a re-run keeps the consultant's changes.
INSERT INTO consultation_hours (weekday, start_time)
SELECT d.weekday, t.start_time
FROM (SELECT 1 AS weekday UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) d
CROSS JOIN (SELECT '09:00:00' AS start_time UNION SELECT '10:00:00' UNION SELECT '11:00:00'
            UNION SELECT '13:00:00' UNION SELECT '14:00:00' UNION SELECT '15:00:00' UNION SELECT '16:00:00') t
WHERE NOT EXISTS (SELECT 1 FROM consultation_hours)
UNION ALL
SELECT 6, s.start_time
FROM (SELECT '10:00:00' AS start_time UNION SELECT '11:00:00' UNION SELECT '12:00:00' UNION SELECT '13:00:00') s
WHERE NOT EXISTS (SELECT 1 FROM consultation_hours);

-- Specific dates that differ from the weekly hours: closed all day, or open at other times.
-- start_times holds the times as a comma-separated list such as 09:00,10:00 (empty when closed).
CREATE TABLE IF NOT EXISTS consultation_special_dates (
    special_date DATE NOT NULL,
    is_closed TINYINT(1) NOT NULL DEFAULT 1,
    start_times VARCHAR(255) NOT NULL DEFAULT '',
    note VARCHAR(150) DEFAULT NULL,
    PRIMARY KEY (special_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Other booking settings.
CREATE TABLE IF NOT EXISTS consultation_settings (
    setting_key VARCHAR(40) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO consultation_settings (setting_key, setting_value) VALUES
('days_ahead', '60');

-- Appointment history and the slot rule. Written as a procedure so it runs on
-- MariaDB (XAMPP) and MySQL (school server), and is safe to run more than once.
-- slot_key is the date and time for active bookings and NULL for cancelled ones,
-- so a cancelled booking frees its time.

DELIMITER $$

DROP PROCEDURE IF EXISTS migrate_008$$
CREATE PROCEDURE migrate_008()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultations' AND COLUMN_NAME = 'consultant_notes') THEN
        ALTER TABLE consultations ADD COLUMN consultant_notes TEXT NULL AFTER notes;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultations' AND COLUMN_NAME = 'status_updated_at') THEN
        ALTER TABLE consultations ADD COLUMN status_updated_at DATETIME NULL AFTER status;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultations' AND COLUMN_NAME = 'slot_key') THEN
        ALTER TABLE consultations ADD COLUMN slot_key VARCHAR(20)
            AS (IF(status = 'cancelled', NULL, CONCAT(consultation_date, ' ', consultation_time))) STORED;
    END IF;

    IF EXISTS (SELECT 1 FROM information_schema.STATISTICS
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultations' AND INDEX_NAME = 'unique_slot') THEN
        ALTER TABLE consultations DROP INDEX unique_slot;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'consultations' AND INDEX_NAME = 'unique_active_slot') THEN
        ALTER TABLE consultations ADD UNIQUE INDEX unique_active_slot (slot_key);
    END IF;
END$$

DELIMITER ;

CALL migrate_008();
DROP PROCEDURE IF EXISTS migrate_008;

INSERT IGNORE INTO schema_migrations (migration_id)
VALUES ('008_consultant_portal');

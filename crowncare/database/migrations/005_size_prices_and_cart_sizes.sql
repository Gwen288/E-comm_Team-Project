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

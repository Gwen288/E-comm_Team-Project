-- CrownCare migration 010: features for a real-world shop.
--
--   Sign in protection   cc_login_attempts (wait after five failed tries)
--   Password reset       cc_password_resets (one-hour links sent by email)
--   Delivery in Accra    cc_delivery_areas (areas and delivery fee per area)
--   Accounts             closed accounts keep their orders (customer_status)
--   Orders               order status updates, paid date, stock taken once
--   Stock                stock per size, and per product when it has no sizes
--   Consultations        who cancelled, reminder emails sent
--
-- Run once, after 009, on the laptop (database crowncare) and on the school
-- server (database ecommerce_2026A_petra_asima). Works on MariaDB and MySQL.
-- In phpMyAdmin: click the database, open Import, choose this file, click Go.

-- ---------------------------------------------------------------------------
-- Sign in protection
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cc_login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    INDEX cc_idx_login_email_time (email, attempted_at),
    INDEX cc_idx_login_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Password reset links (only a hash of the token is stored)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cc_password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY cc_uniq_reset_token (token_hash),
    INDEX cc_idx_reset_customer (customer_id),
    CONSTRAINT cc_fk_reset_customer FOREIGN KEY (customer_id)
        REFERENCES cc_customer (customer_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Delivery areas in Greater Accra (the admin can change fees and turn areas off)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cc_delivery_areas (
    area_id INT AUTO_INCREMENT PRIMARY KEY,
    area_name VARCHAR(30) NOT NULL,
    delivery_fee DECIMAL(8,2) NOT NULL DEFAULT 20.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    UNIQUE KEY cc_uniq_area_name (area_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO cc_delivery_areas (area_name, delivery_fee, sort_order) VALUES
('Achimota', 25.00, 10),
('Adenta', 30.00, 20),
('Airport Residential', 20.00, 30),
('Ashaiman', 35.00, 40),
('Cantonments', 20.00, 50),
('Dansoman', 25.00, 60),
('Dome', 30.00, 70),
('Dzorwulu', 20.00, 80),
('East Legon', 25.00, 90),
('Haatso', 30.00, 100),
('Kaneshie', 25.00, 110),
('Labone', 20.00, 120),
('Lapaz', 25.00, 130),
('Legon', 25.00, 140),
('Madina', 30.00, 150),
('Nungua', 30.00, 160),
('Osu', 20.00, 170),
('Spintex', 30.00, 180),
('Tema', 35.00, 190),
('Teshie', 30.00, 200),
('Weija', 35.00, 210);

-- ---------------------------------------------------------------------------
-- Accounts: closing an account removes personal details but keeps orders
-- ---------------------------------------------------------------------------
ALTER TABLE cc_customer
    ADD COLUMN customer_status VARCHAR(20) NOT NULL DEFAULT 'active',
    ADD COLUMN closed_at DATETIME NULL,
    ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Everyone is in Ghana; the city field now holds the Accra area.
UPDATE cc_customer SET customer_country = 'Ghana' WHERE customer_country <> 'Ghana';

-- ---------------------------------------------------------------------------
-- Orders: status updates, paid date, and stock taken only once per order
-- Order status values: pending, paid, processing, out_for_delivery,
-- delivered, cancelled, failed.
-- ---------------------------------------------------------------------------
ALTER TABLE cc_orders
    ADD COLUMN delivery_area_id INT NULL,
    ADD COLUMN paid_at DATETIME NULL,
    ADD COLUMN status_updated_at DATETIME NULL,
    ADD COLUMN stock_deducted TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE cc_orders SET status_updated_at = order_date WHERE status_updated_at IS NULL;

-- ---------------------------------------------------------------------------
-- Stock: per size, and per product for products without sizes.
-- Existing products start with 20 of each; change them on the product form.
-- ---------------------------------------------------------------------------
ALTER TABLE cc_product_size_options
    ADD COLUMN stock_qty INT NOT NULL DEFAULT 20;

ALTER TABLE cc_products
    ADD COLUMN product_stock INT NOT NULL DEFAULT 20;

-- ---------------------------------------------------------------------------
-- Consultations: who cancelled (client or consultant), and reminder emails
-- ---------------------------------------------------------------------------
ALTER TABLE cc_consultations
    ADD COLUMN cancelled_by VARCHAR(20) NULL,
    ADD COLUMN reminder_sent_at DATETIME NULL;

INSERT IGNORE INTO cc_schema_migrations (migration_id, applied_at)
VALUES ('010_real_world_features', NOW());

-- CrownCare migration 014: product photos can be kept in the database.
--
--   When the server does not let PHP save files into images/products (folder
--   permissions), an uploaded product photo is stored here instead and shown
--   through image.php. Photos that are already files are not affected.
--
-- Run once, after 013, on the laptop (database crowncare) and on the school
-- server (database ecommerce_2026A_petra_asima). Works on MariaDB and MySQL.
-- In phpMyAdmin: click the database, open Import, choose this file, click Go.

CREATE TABLE IF NOT EXISTS cc_image_store (
    file_name VARCHAR(100) NOT NULL,
    mime_type VARCHAR(30) NOT NULL,
    image_data MEDIUMBLOB NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (file_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

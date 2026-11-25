-- CrownCare migration 013: signed-in customers keep their recommended products.
--
--   Each customer has one saved set: the products from their latest hair quiz
--   (items, with the routine step and the reason) and the answers used. Taking
--   the quiz again replaces it. Prices, photos and stock are read live.
--
-- Run once, after 012, on the laptop (database crowncare) and on the school
-- server (database ecommerce_2026A_petra_asima). Works on MariaDB and MySQL.
-- In phpMyAdmin: click the database, open Import, choose this file, click Go.

CREATE TABLE IF NOT EXISTS cc_saved_recommendations (
    customer_id INT NOT NULL,
    answers TEXT NOT NULL,
    items TEXT NOT NULL,
    saved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (customer_id),
    CONSTRAINT fk_saved_reco_customer
        FOREIGN KEY (customer_id) REFERENCES cc_customer (customer_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CrownCare migration 011: guest checkout.
--
--   Orders and payments can be made without an account, so customer_id may
--   be empty (NULL) on cc_orders and cc_payment.
--   cc_orders.contact_email keeps the email the receipt was sent to, for
--   guests and signed-in customers alike.
--
-- Run once, after 010, on the laptop (database crowncare) and on the school
-- server (database ecommerce_2026A_petra_asima). Works on MariaDB and MySQL.
-- In phpMyAdmin: click the database, open Import, choose this file, click Go.

-- Orders: allow no account, keep the contact email.
ALTER TABLE cc_orders DROP FOREIGN KEY cc_fk_orders_customer;

ALTER TABLE cc_orders
    MODIFY customer_id INT(11) NULL,
    ADD COLUMN contact_email VARCHAR(100) NULL AFTER customer_id;

ALTER TABLE cc_orders
    ADD CONSTRAINT cc_fk_orders_customer FOREIGN KEY (customer_id)
        REFERENCES cc_customer (customer_id);

-- Payments: allow no account.
ALTER TABLE cc_payment DROP FOREIGN KEY cc_fk_payment_customer;

ALTER TABLE cc_payment MODIFY customer_id INT(11) NULL;

ALTER TABLE cc_payment
    ADD CONSTRAINT cc_fk_payment_customer FOREIGN KEY (customer_id)
        REFERENCES cc_customer (customer_id);

-- Existing orders: copy the account email so every order has one.
UPDATE cc_orders o
INNER JOIN cc_customer c ON c.customer_id = o.customer_id
SET o.contact_email = c.customer_email
WHERE o.contact_email IS NULL;

INSERT IGNORE INTO cc_schema_migrations (migration_id, applied_at)
VALUES ('011_guest_checkout', NOW());

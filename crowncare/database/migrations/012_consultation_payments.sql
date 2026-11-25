-- CrownCare migration 012: consultations are paid online when booking.
--
--   Payment    paid through Paystack when booking (payment_status, amount_paid,
--              payment_reference, paid_at). While the client is paying, the
--              booking has status 'awaiting_payment' and holds the time.
--   Refunds    cancelling 24 hours or more before: full refund; less than
--              24 hours before: 95% (5% kept); no-show: no refund; cancelled
--              by the consultant: full refund. Refunds are sent through
--              Paystack (refund_amount, refund_status, refunded_at, refund_note).
--   Cancelling every booking email has a private cancel link (cancel_token),
--              so clients without an account can cancel too.
--
-- Run once, after 011, on the laptop (database crowncare) and on the school
-- server (database ecommerce_2026A_petra_asima). Works on MariaDB and MySQL.
-- In phpMyAdmin: click the database, open Import, choose this file, click Go.

ALTER TABLE cc_consultations
    ADD COLUMN payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid',
    ADD COLUMN payment_reference VARCHAR(100) NULL,
    ADD COLUMN amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0,
    ADD COLUMN paid_at DATETIME NULL,
    ADD COLUMN refund_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    ADD COLUMN refund_status VARCHAR(20) NULL,
    ADD COLUMN refunded_at DATETIME NULL,
    ADD COLUMN refund_note VARCHAR(255) NULL,
    ADD COLUMN cancel_token CHAR(64) NULL,
    ADD UNIQUE INDEX cc_uq_consultation_payment_ref (payment_reference);

-- Bookings made before this change were paid at the appointment. Completed
-- ones are counted as paid, so the income figures stay the same.
UPDATE cc_consultations
SET payment_status = 'paid',
    amount_paid = price,
    paid_at = COALESCE(status_updated_at, created_at)
WHERE status = 'completed' AND payment_status = 'unpaid';

INSERT IGNORE INTO cc_schema_migrations (migration_id, applied_at)
VALUES ('012_consultation_payments', NOW());

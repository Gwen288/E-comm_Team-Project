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

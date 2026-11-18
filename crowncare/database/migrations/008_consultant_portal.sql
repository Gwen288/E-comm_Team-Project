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

-- Appointment history: the consultant's private notes and when the status last changed.
ALTER TABLE consultations
    ADD COLUMN IF NOT EXISTS consultant_notes TEXT NULL AFTER notes,
    ADD COLUMN IF NOT EXISTS status_updated_at DATETIME NULL AFTER status;

-- A cancelled booking frees its time, so the slot rule only applies to active bookings.
-- slot_key is the date and time for active bookings and NULL for cancelled ones.
ALTER TABLE consultations
    ADD COLUMN IF NOT EXISTS slot_key VARCHAR(20)
        AS (IF(status = 'cancelled', NULL, CONCAT(consultation_date, ' ', consultation_time))) PERSISTENT;

ALTER TABLE consultations DROP INDEX IF EXISTS unique_slot;
ALTER TABLE consultations ADD UNIQUE INDEX IF NOT EXISTS unique_active_slot (slot_key);

INSERT IGNORE INTO schema_migrations (migration_id)
VALUES ('008_consultant_portal');

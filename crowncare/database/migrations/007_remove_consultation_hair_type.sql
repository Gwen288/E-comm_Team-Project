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

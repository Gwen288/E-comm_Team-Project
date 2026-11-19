-- CrownCare: put cc_ in front of every CrownCare table name.
-- CrownCare can then share one database with another project (as on the school
-- server) without either project overwriting the other's tables.
-- Every row is kept: products, accounts, orders, bookings and settings.
-- Run once on your crowncare database, after 008. Safe to run more than once:
-- a table is only renamed if the old name exists and the new one does not.
-- Works on MariaDB (XAMPP) and MySQL (school server).
-- After this, the code only uses the cc_ names.

DELIMITER $$

DROP PROCEDURE IF EXISTS cc_rename_table$$
CREATE PROCEDURE cc_rename_table(IN old_name VARCHAR(64))
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.TABLES
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = old_name)
       AND NOT EXISTS (SELECT 1 FROM information_schema.TABLES
               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = CONCAT('cc_', old_name)) THEN
        SET @cc_sql = CONCAT('RENAME TABLE `', old_name, '` TO `cc_', old_name, '`');
        PREPARE cc_stmt FROM @cc_sql;
        EXECUTE cc_stmt;
        DEALLOCATE PREPARE cc_stmt;
    END IF;
END$$

DELIMITER ;

CALL cc_rename_table('schema_migrations');
CALL cc_rename_table('customer');
CALL cc_rename_table('categories');
CALL cc_rename_table('brands');
CALL cc_rename_table('products');
CALL cc_rename_table('product_sizes');
CALL cc_rename_table('product_size_options');
CALL cc_rename_table('cart');
CALL cc_rename_table('orders');
CALL cc_rename_table('orderdetails');
CALL cc_rename_table('payment');
CALL cc_rename_table('hair_attributes');
CALL cc_rename_table('hair_profiles');
CALL cc_rename_table('hair_profile_attributes');
CALL cc_rename_table('product_hair_attributes');
CALL cc_rename_table('consultations');
CALL cc_rename_table('consultation_services');
CALL cc_rename_table('consultation_hours');
CALL cc_rename_table('consultation_special_dates');
CALL cc_rename_table('consultation_settings');

DROP PROCEDURE IF EXISTS cc_rename_table;

CREATE TABLE IF NOT EXISTS cc_schema_migrations (
    migration_id VARCHAR(100) NOT NULL,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (migration_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO cc_schema_migrations (migration_id)
VALUES ('009_cc_table_prefix');

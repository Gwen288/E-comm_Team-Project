-- CrownCare: hair type is no longer asked on the consultation booking form.
-- Removes the unused hair_type column from consultations.
-- Run once on the crowncare database, after 006_consultations.sql.

ALTER TABLE consultations DROP COLUMN IF EXISTS hair_type;

INSERT IGNORE INTO schema_migrations (migration_id)
VALUES ('007_remove_consultation_hair_type');

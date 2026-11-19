-- ============================================================
-- CrownCare: full database for the school server.
-- Creates the CrownCare tables, all named cc_..., with the products,
-- sizes, photos, hair quiz data and consultation settings.
-- It only touches tables whose names start with cc_, so another
-- project in the same database is not affected.
-- Import it in phpMyAdmin: select your database > Import > this file > Import.
-- Running it again resets the CrownCare tables to this starting data.
-- Works on MySQL and MariaDB. No procedures, no USE line.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cc_cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_cart` (
  `c_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL DEFAULT 0,
  `qty` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`c_id`,`p_id`,`size_id`),
  KEY `idx_cart_product` (`p_id`),
  CONSTRAINT `cc_fk_cart_customer` FOREIGN KEY (`c_id`) REFERENCES `cc_customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cc_fk_cart_product` FOREIGN KEY (`p_id`) REFERENCES `cc_products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_cart` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(100) NOT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`cat_id`),
  KEY `fk_category_parent` (`parent_category_id`),
  CONSTRAINT `cc_fk_category_parent` FOREIGN KEY (`parent_category_id`) REFERENCES `cc_categories` (`cat_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_categories` DISABLE KEYS */;
INSERT INTO `cc_categories` VALUES
(1,'Shampoos',NULL),
(2,'Cleansers',NULL),
(3,'Conditioners',NULL),
(4,'Hair Creams',NULL),
(5,'Oils',NULL),
(6,'Styling',NULL),
(7,'Kits and Bundles',NULL),
(8,'Rinse-out Conditioners',3),
(9,'Deep Conditioners',3),
(10,'Leave-in Conditioners',3);
/*!40000 ALTER TABLE `cc_categories` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_consultation_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_consultation_hours` (
  `weekday` tinyint(4) NOT NULL,
  `start_time` time NOT NULL,
  PRIMARY KEY (`weekday`,`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_consultation_hours` DISABLE KEYS */;
INSERT INTO `cc_consultation_hours` VALUES
(1,'09:00:00'),
(1,'10:00:00'),
(1,'11:00:00'),
(1,'13:00:00'),
(1,'14:00:00'),
(1,'15:00:00'),
(1,'16:00:00'),
(2,'09:00:00'),
(2,'10:00:00'),
(2,'11:00:00'),
(2,'13:00:00'),
(2,'14:00:00'),
(2,'15:00:00'),
(2,'16:00:00'),
(3,'09:00:00'),
(3,'10:00:00'),
(3,'11:00:00'),
(3,'13:00:00'),
(3,'14:00:00'),
(3,'15:00:00'),
(3,'16:00:00'),
(4,'09:00:00'),
(4,'10:00:00'),
(4,'11:00:00'),
(4,'13:00:00'),
(4,'14:00:00'),
(4,'15:00:00'),
(4,'16:00:00'),
(5,'09:00:00'),
(5,'10:00:00'),
(5,'11:00:00'),
(5,'13:00:00'),
(5,'14:00:00'),
(5,'15:00:00'),
(5,'16:00:00'),
(6,'10:00:00'),
(6,'11:00:00'),
(6,'12:00:00'),
(6,'13:00:00');
/*!40000 ALTER TABLE `cc_consultation_hours` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_consultation_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_consultation_services` (
  `service_key` varchar(20) NOT NULL,
  `service_name` varchar(80) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`service_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_consultation_services` DISABLE KEYS */;
INSERT INTO `cc_consultation_services` VALUES
('assessment','Hair and Scalp Assessment',45,100.00,'A full look at your hair and scalp: hair type, porosity, density and condition, followed by a written care plan.',1,'2026-11-19 18:39:35'),
('protective','Protective Style Consultation',30,60.00,'Choose protective styles that suit your hair, and learn how to care for your edges and scalp while you wear them.',3,'2026-11-19 18:39:35'),
('routine','Routine Planning',30,60.00,'Build a wash-day and moisture routine around your hair, your schedule and your budget, with product recommendations.',2,'2026-11-19 18:39:35');
/*!40000 ALTER TABLE `cc_consultation_services` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_consultation_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_consultation_settings` (
  `setting_key` varchar(40) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_consultation_settings` DISABLE KEYS */;
INSERT INTO `cc_consultation_settings` VALUES
('days_ahead','60');
/*!40000 ALTER TABLE `cc_consultation_settings` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_consultation_special_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_consultation_special_dates` (
  `special_date` date NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 1,
  `start_times` varchar(255) NOT NULL DEFAULT '',
  `note` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`special_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_consultation_special_dates` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_consultation_special_dates` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_consultations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_consultations` (
  `consultation_id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(20) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `service` varchar(80) NOT NULL,
  `consultation_format` varchar(20) NOT NULL,
  `consultation_date` date NOT NULL,
  `consultation_time` time NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `price` double NOT NULL DEFAULT 0,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `main_concern` varchar(60) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `consultant_notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'booked',
  `status_updated_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `slot_key` varchar(20) GENERATED ALWAYS AS (if(`status` = 'cancelled',NULL,concat(`consultation_date`,' ',`consultation_time`))) STORED,
  PRIMARY KEY (`consultation_id`),
  UNIQUE KEY `unique_reference` (`reference`),
  UNIQUE KEY `unique_active_slot` (`slot_key`),
  KEY `idx_consultation_customer` (`customer_id`),
  CONSTRAINT `cc_fk_consultation_customer` FOREIGN KEY (`customer_id`) REFERENCES `cc_customer` (`customer_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_consultations` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_consultations` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_pass` varchar(150) NOT NULL,
  `customer_country` varchar(30) NOT NULL,
  `customer_city` varchar(30) NOT NULL,
  `customer_contact` varchar(15) NOT NULL,
  `customer_image` varchar(100) DEFAULT NULL,
  `user_role` int(11) NOT NULL DEFAULT 2,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `unique_customer_email` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_customer` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_customer` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_hair_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_hair_attributes` (
  `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_name` varchar(100) NOT NULL,
  `attribute_type` enum('texture','porosity','concern','goal','preference') NOT NULL,
  PRIMARY KEY (`attribute_id`),
  UNIQUE KEY `unique_hair_attribute` (`attribute_name`,`attribute_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_hair_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_hair_attributes` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_hair_profile_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_hair_profile_attributes` (
  `profile_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  PRIMARY KEY (`profile_id`,`attribute_id`),
  KEY `fk_hpa_attribute` (`attribute_id`),
  CONSTRAINT `cc_fk_hpa_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `cc_hair_attributes` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cc_fk_hpa_profile` FOREIGN KEY (`profile_id`) REFERENCES `cc_hair_profiles` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_hair_profile_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_hair_profile_attributes` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_hair_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_hair_profiles` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `unique_customer_profile` (`customer_id`),
  CONSTRAINT `cc_fk_hair_profile_customer` FOREIGN KEY (`customer_id`) REFERENCES `cc_customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_hair_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_hair_profiles` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_orderdetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_orderdetails` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL DEFAULT 0,
  `qty` int(11) NOT NULL,
  `unit_price` double DEFAULT NULL,
  PRIMARY KEY (`order_id`,`product_id`,`size_id`),
  KEY `idx_orderdetails_product` (`product_id`),
  CONSTRAINT `cc_fk_orderdetails_order` FOREIGN KEY (`order_id`) REFERENCES `cc_orders` (`order_id`),
  CONSTRAINT `cc_fk_orderdetails_product` FOREIGN KEY (`product_id`) REFERENCES `cc_products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_orderdetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_orderdetails` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `invoice_no` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `order_status` varchar(100) NOT NULL DEFAULT 'pending',
  `delivery_name` varchar(100) NOT NULL DEFAULT '',
  `delivery_phone` varchar(20) NOT NULL DEFAULT '',
  `delivery_city` varchar(100) NOT NULL DEFAULT '',
  `delivery_address` varchar(255) NOT NULL DEFAULT '',
  `delivery_notes` varchar(255) DEFAULT NULL,
  `subtotal` double NOT NULL DEFAULT 0,
  `delivery_fee` double NOT NULL DEFAULT 0,
  `total_amount` double NOT NULL DEFAULT 0,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`order_id`),
  KEY `idx_orders_customer` (`customer_id`),
  CONSTRAINT `cc_fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `cc_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_orders` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_payment` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `amt` double NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `channel` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `payment_date` date NOT NULL,
  PRIMARY KEY (`pay_id`),
  UNIQUE KEY `unique_payment_reference` (`reference`),
  KEY `idx_payment_customer` (`customer_id`),
  KEY `idx_payment_order` (`order_id`),
  CONSTRAINT `cc_fk_payment_customer` FOREIGN KEY (`customer_id`) REFERENCES `cc_customer` (`customer_id`),
  CONSTRAINT `cc_fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `cc_orders` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_payment` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_product_hair_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_product_hair_attributes` (
  `product_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`attribute_id`),
  KEY `fk_pha_attribute` (`attribute_id`),
  CONSTRAINT `cc_fk_pha_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `cc_hair_attributes` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cc_fk_pha_product` FOREIGN KEY (`product_id`) REFERENCES `cc_products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_product_hair_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `cc_product_hair_attributes` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_product_size_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_product_size_options` (
  `product_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `price` double DEFAULT NULL,
  PRIMARY KEY (`product_id`,`size_id`),
  KEY `fk_product_size_size` (`size_id`),
  CONSTRAINT `cc_fk_product_size_product` FOREIGN KEY (`product_id`) REFERENCES `cc_products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cc_fk_product_size_size` FOREIGN KEY (`size_id`) REFERENCES `cc_product_sizes` (`size_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_product_size_options` DISABLE KEYS */;
INSERT INTO `cc_product_size_options` VALUES
(3,14,50),
(3,15,80),
(4,7,65),
(4,9,105),
(5,7,90),
(5,9,145),
(6,5,160),
(6,21,100),
(7,5,85),
(7,6,135),
(8,15,45),
(8,18,70),
(9,5,110),
(9,6,175),
(10,6,55),
(10,8,90),
(11,7,95),
(11,9,150),
(12,6,70),
(12,8,110),
(13,6,40),
(13,9,75),
(13,11,130),
(14,6,130),
(14,8,210),
(15,7,120),
(15,10,190),
(16,6,40),
(16,9,80),
(16,11,135),
(17,6,45),
(17,9,90),
(17,11,155),
(18,6,35),
(18,9,65),
(18,11,110),
(19,6,35),
(19,9,70),
(19,11,120),
(20,6,45),
(20,9,85),
(20,11,145);
/*!40000 ALTER TABLE `cc_product_size_options` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_product_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_product_sizes` (
  `size_id` int(11) NOT NULL AUTO_INCREMENT,
  `size_name` varchar(50) NOT NULL,
  PRIMARY KEY (`size_id`),
  UNIQUE KEY `unique_size_name` (`size_name`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_product_sizes` DISABLE KEYS */;
INSERT INTO `cc_product_sizes` VALUES
(12,'1 Litre'),
(15,'100g'),
(6,'100ml'),
(16,'150g'),
(7,'150ml'),
(20,'1kg'),
(13,'2 Litre'),
(17,'200g'),
(8,'200ml'),
(18,'250g'),
(9,'250ml'),
(10,'300ml'),
(21,'30ml'),
(19,'500g'),
(11,'500ml'),
(14,'50g'),
(5,'50ml'),
(4,'Extra Large'),
(3,'Large'),
(2,'Medium'),
(1,'Small');
/*!40000 ALTER TABLE `cc_product_sizes` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_cat` int(11) NOT NULL,
  `product_title` varchar(200) NOT NULL,
  `product_price` double NOT NULL,
  `product_desc` varchar(500) DEFAULT NULL,
  `product_image` varchar(100) DEFAULT NULL,
  `product_keywords` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`),
  KEY `idx_product_cat` (`product_cat`),
  CONSTRAINT `cc_fk_product_category` FOREIGN KEY (`product_cat`) REFERENCES `cc_categories` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_products` DISABLE KEYS */;
INSERT INTO `cc_products` VALUES
(1,7,'CrownCare Scalp Care Kit',255,'The Scalp Soothing Shampoo (250 ml), Scalp and Strength Oil (50 ml) and Castor Oil Scalp Serum (30 ml) in one kit: a gentle scalp-care routine. Results vary; this kit is not a medical treatment. Saves GHâ‚µ45 compared with buying the three separately. Suitable for hair types 4A, 4B, 4C. Helps with: Thinning, Itchy scalp. FDA registration no.: SAMPLE-0020 (sample).','crowncare-scalp-care-kit.jpg','kits and bundles, thinning, itchy scalp, 4a, 4b, 4c','2026-11-19 18:39:35'),
(2,7,'CrownCare 4C Moisture Starter Kit',250,'The Moisture Rich Cleanser (250 ml), Deep Moisture Masque (150 ml) and Whipped Butter Moisturiser (150 ml) in one kit: a complete wash-day routine for very dry, tightly coiled hair. Saves GHâ‚µ50 compared with buying the three products separately. Suitable for hair types 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0019 (sample).','crowncare-4c-moisture-starter-kit.jpg','kits and bundles, dryness, 4b, 4c','2026-11-19 18:39:35'),
(3,6,'CrownCare Sleek Edge Control',50,'A firm-hold edge control made with shea butter. How to use: apply a small amount to edges and lay with a soft brush. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Frizz. FDA registration no.: SAMPLE-0018 (sample).','crowncare-sleek-edge-control.jpg','styling, frizz, 3c, 4a, 4b, 4c','2026-11-19 18:39:35'),
(4,6,'CrownCare Flaxseed Styling Gel',65,'A flaxseed gel for defined, frizz-controlled styles. How to use: apply over a leave-in on wet hair, then style. Scrunch out any stiffness once dry. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Frizz. FDA registration no.: SAMPLE-0017 (sample).','crowncare-flaxseed-styling-gel.jpg','styling, frizz, 3c, 4a, 4b, 4c','2026-11-19 18:39:35'),
(5,6,'CrownCare Curl Defining Custard',90,'A soft-hold custard for twist-outs and wash-and-gos. How to use: apply to wet hair in sections, smooth through, and leave to air dry or diffuse. Suitable for hair types 3C, 4A, 4B. Helps with: Frizz. FDA registration no.: SAMPLE-0016 (sample).','crowncare-curl-defining-custard.jpg','styling, frizz, 3c, 4a, 4b','2026-11-19 18:39:35'),
(6,5,'CrownCare Castor Oil Scalp Serum',100,'A dropper serum with castor oil for edges and the scalp. How to use: apply a few drops to the scalp or edges and massage in. Patch test first. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Thinning, Itchy scalp. FDA registration no.: SAMPLE-0015 (sample).','crowncare-castor-oil-scalp-serum.jpg','oils, thinning, itchy scalp, 3c, 4a, 4b, 4c','2026-11-19 18:39:35'),
(7,5,'CrownCare Baobab and Coconut Hair Oil',85,'A light sealing oil with baobab and coconut for ends and protective styles. How to use: apply to damp hair after a leave-in, or to braids and twists to add shine. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Breakage. FDA registration no.: SAMPLE-0014 (sample).','crowncare-baobab-and-coconut-hair-oil.jpg','oils, breakage, 3c, 4a, 4b, 4c','2026-11-19 18:39:35'),
(8,4,'CrownCare Nourishing Hair Butter',45,'A rich butter blend made with Ghanaian shea and cocoa butter for sealing in moisture. How to use: warm a small amount between your palms and apply to ends to seal in moisture. Suitable for hair types 4A, 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0013 (sample).','crowncare-nourishing-hair-butter.jpg','hair creams, dryness, 4a, 4b, 4c','2026-11-19 18:39:35'),
(9,5,'CrownCare Scalp and Strength Oil',110,'A blend of moringa, rosemary and jojoba oils for a regular scalp-care routine. Supports a healthy scalp routine; results vary and no growth is guaranteed. How to use: massage a few drops into the scalp two to three times a week. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Thinning. FDA registration no.: SAMPLE-0012 (sample).','crowncare-scalp-and-strength-oil.jpg','oils, thinning, 3c, 4a, 4b, 4c','2026-11-19 18:39:35'),
(10,10,'CrownCare Aloe Daily Hydrating Spray',55,'A water-based aloe vera spray to refresh hair between wash days. How to use: mist onto hair daily or as needed, then seal with an oil or butter. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0011 (sample).','crowncare-aloe-daily-hydrating-spray.jpg','leave-in conditioners, dryness, 3c, 4a, 4b, 4c','2026-11-19 18:39:35'),
(11,4,'CrownCare Whipped Butter Moisturiser',95,'A thick whipped butter with shea and cocoa butter that seals in moisture on tightly coiled hair. How to use: apply a small amount to damp hair after a leave-in, focusing on the ends. Suitable for hair types 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0010 (sample).','crowncare-whipped-butter-moisturiser.jpg','hair creams, dryness, 4b, 4c','2026-11-19 18:39:35'),
(12,10,'CrownCare Leave-In Milk',70,'A lightweight leave-in that softens hair without weighing it down. How to use: spray or smooth onto damp hair, then style as usual. Suitable for hair types 3C, 4A, 4B. Helps with: Dryness. FDA registration no.: SAMPLE-0009 (sample).','crowncare-leave-in-milk.jpg','leave-in conditioners, dryness, 3c, 4a, 4b','2026-11-19 18:39:35'),
(13,8,'CrownCare Hibiscus Conditioner',40,'A softening conditioner with hibiscus for looser curl patterns. How to use: apply after cleansing, leave for three minutes, rinse with cool water. Suitable for hair types 3C, 4A. Helps with: Frizz. FDA registration no.: SAMPLE-0008 (sample).','crowncare-hibiscus-conditioner.jpg','rinse-out conditioners, frizz, 3c, 4a','2026-11-19 18:39:35'),
(14,9,'CrownCare Protein Repair Treatment',130,'A light protein treatment for hair that snaps or feels weak. Use once a month. How to use: apply to clean hair, leave for 10 minutes, rinse, then follow with a moisturising conditioner. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Breakage. FDA registration no.: SAMPLE-0007 (sample).','crowncare-protein-repair-treatment.jpg','deep conditioners, breakage, 3c, 4a, 4b, 4c','2026-11-19 18:39:35'),
(15,9,'CrownCare Deep Moisture Masque',120,'A rich weekly masque with shea butter and baobab oil for very dry hair. How to use: apply to clean, damp hair, cover with a plastic cap for 20 to 30 minutes, rinse. Suitable for hair types 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0006 (sample).','crowncare-deep-moisture-masque.jpg','deep conditioners, dryness, 4b, 4c','2026-11-19 18:39:35'),
(16,8,'CrownCare Detangling Conditioner',40,'A slippery rinse-out conditioner that makes detangling easier. How to use: apply generously after cleansing, detangle in sections with a wide-tooth comb, rinse. Suitable for hair types 4A, 4B, 4C. Helps with: Breakage, Tangles. FDA registration no.: SAMPLE-0005 (sample).','crowncare-detangling-conditioner.jpg','rinse-out conditioners, breakage, tangles, 4a, 4b, 4c','2026-11-19 18:39:35'),
(17,1,'CrownCare Scalp Soothing Shampoo',45,'A fragrance-free shampoo with moringa for sensitive, itchy scalps. How to use: massage gently into the scalp, leave for one minute, rinse. If irritation continues, stop use and see a professional. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Itchy scalp. FDA registration no.: SAMPLE-0004 (sample).','crowncare-scalp-soothing-shampoo.jpg','shampoos, itchy scalp, 3c, 4a, 4b, 4c','2026-11-19 18:39:35'),
(18,1,'CrownCare Black Soap Clarifying Shampoo',35,'A clarifying shampoo made with African black soap to lift product buildup. Best used every two to four weeks. How to use: lather on the scalp, rinse thoroughly, then deep condition. Suitable for hair types 4A, 4B, 4C. Helps with: Scalp buildup. FDA registration no.: SAMPLE-0003 (sample).','crowncare-black-soap-clarifying-shampoo.jpg','shampoos, scalp buildup, 4a, 4b, 4c','2026-11-19 18:39:35'),
(19,2,'CrownCare Gentle Co-Wash',35,'A conditioning cleanser with baobab for wash days between shampoos. Cleans lightly while leaving curls soft. How to use: work through wet hair from roots to ends, detangle with fingers, rinse. Suitable for hair types 3C, 4A, 4B. Helps with: Dryness, Frizz. FDA registration no.: SAMPLE-0002 (sample).','crowncare-gentle-co-wash.jpg','cleansers, dryness, frizz, 3c, 4a, 4b','2026-11-19 18:39:35'),
(20,2,'CrownCare Moisture Rich Cleanser',45,'A gentle, low-lather cleanser with shea butter for coily hair that dries out easily. Cleanses without stripping natural oils. How to use: apply to wet hair, massage into the scalp, rinse well. Follow with a conditioner. Suitable for hair types 4A, 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0001 (sample).','crowncare-moisture-rich-cleanser.jpg','cleansers, dryness, 4a, 4b, 4c','2026-11-19 18:39:35');
/*!40000 ALTER TABLE `cc_products` ENABLE KEYS */;
DROP TABLE IF EXISTS `cc_schema_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cc_schema_migrations` (
  `migration_id` varchar(100) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`migration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40000 ALTER TABLE `cc_schema_migrations` DISABLE KEYS */;
INSERT INTO `cc_schema_migrations` VALUES
('001_shop_catalogue_extensions','2026-11-19 18:39:35'),
('002_cms_product_images','2026-11-19 18:39:35'),
('003_remove_brands','2026-11-19 18:39:35'),
('004_crowncare_products','2026-11-19 18:39:35'),
('005_size_prices_and_cart_sizes','2026-11-19 18:39:35'),
('006_consultations','2026-11-19 18:39:35'),
('007_remove_consultation_hair_type','2026-11-19 18:39:35'),
('008_consultant_portal','2026-11-19 18:39:35'),
('009_cc_table_prefix','2026-11-19 18:39:35');
/*!40000 ALTER TABLE `cc_schema_migrations` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


SET FOREIGN_KEY_CHECKS = 1;

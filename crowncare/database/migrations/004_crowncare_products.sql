-- CrownCare: add the categories and the 20 CrownCare products from the CMS (Wix) site (no brand).
-- Run on the crowncare database after migrations 001 and 003.
-- Run download_crowncare_images.ps1 first so the photos exist in images/products/.
-- Safe to run more than once: rows that already exist are skipped.

-- Run 003_remove_brands.sql first: products have no brand.

-- 1. Main categories
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Shampoos', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Shampoos');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Cleansers', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Cleansers');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Conditioners', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Conditioners');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Hair Creams', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Hair Creams');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Oils', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Oils');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Styling', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Styling');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Kits and Bundles', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Kits and Bundles');

-- 2. Conditioner subcategories
SET @conditioners = (SELECT cat_id FROM categories WHERE cat_name = 'Conditioners' LIMIT 1);
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Rinse-out Conditioners', @conditioners FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Rinse-out Conditioners');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Deep Conditioners', @conditioners FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Deep Conditioners');
INSERT INTO categories (cat_name, parent_category_id)
SELECT 'Leave-in Conditioners', @conditioners FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE cat_name = 'Leave-in Conditioners');

-- 3. Sizes used by the products (most already exist from migration 001)
INSERT IGNORE INTO product_sizes (size_name) VALUES ('100g'), ('100ml'), ('150ml'), ('200ml'), ('250g'), ('250ml'), ('300ml'), ('30ml'), ('500ml'), ('50g'), ('50ml');

-- 4. Products (price is the smallest size's price, as on the CMS)
INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Kits and Bundles' LIMIT 1), 'CrownCare Scalp Care Kit', 255,
       'The Scalp Soothing Shampoo (250 ml), Scalp and Strength Oil (50 ml) and Castor Oil Scalp Serum (30 ml) in one kit: a gentle scalp-care routine. Results vary; this kit is not a medical treatment. Saves GH₵45 compared with buying the three separately. Suitable for hair types 4A, 4B, 4C. Helps with: Thinning, Itchy scalp. FDA registration no.: SAMPLE-0020 (sample).',
       'crowncare-scalp-care-kit.jpg', 'kits and bundles, thinning, itchy scalp, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Scalp Care Kit');

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Kits and Bundles' LIMIT 1), 'CrownCare 4C Moisture Starter Kit', 250,
       'The Moisture Rich Cleanser (250 ml), Deep Moisture Masque (150 ml) and Whipped Butter Moisturiser (150 ml) in one kit: a complete wash-day routine for very dry, tightly coiled hair. Saves GH₵50 compared with buying the three products separately. Suitable for hair types 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0019 (sample).',
       'crowncare-4c-moisture-starter-kit.jpg', 'kits and bundles, dryness, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare 4C Moisture Starter Kit');

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Styling' LIMIT 1), 'CrownCare Sleek Edge Control', 50,
       'A firm-hold edge control made with shea butter. How to use: apply a small amount to edges and lay with a soft brush. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Frizz. FDA registration no.: SAMPLE-0018 (sample).',
       'crowncare-sleek-edge-control.jpg', 'styling, frizz, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Sleek Edge Control');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Sleek Edge Control' AND s.size_name = '50g';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Sleek Edge Control' AND s.size_name = '100g';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Styling' LIMIT 1), 'CrownCare Flaxseed Styling Gel', 65,
       'A flaxseed gel for defined, frizz-controlled styles. How to use: apply over a leave-in on wet hair, then style. Scrunch out any stiffness once dry. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Frizz. FDA registration no.: SAMPLE-0017 (sample).',
       'crowncare-flaxseed-styling-gel.jpg', 'styling, frizz, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Flaxseed Styling Gel');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Flaxseed Styling Gel' AND s.size_name = '150ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Flaxseed Styling Gel' AND s.size_name = '250ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Styling' LIMIT 1), 'CrownCare Curl Defining Custard', 90,
       'A soft-hold custard for twist-outs and wash-and-gos. How to use: apply to wet hair in sections, smooth through, and leave to air dry or diffuse. Suitable for hair types 3C, 4A, 4B. Helps with: Frizz. FDA registration no.: SAMPLE-0016 (sample).',
       'crowncare-curl-defining-custard.jpg', 'styling, frizz, 3c, 4a, 4b' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Curl Defining Custard');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Curl Defining Custard' AND s.size_name = '150ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Curl Defining Custard' AND s.size_name = '250ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Oils' LIMIT 1), 'CrownCare Castor Oil Scalp Serum', 100,
       'A dropper serum with castor oil for edges and the scalp. How to use: apply a few drops to the scalp or edges and massage in. Patch test first. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Thinning, Itchy scalp. FDA registration no.: SAMPLE-0015 (sample).',
       'crowncare-castor-oil-scalp-serum.jpg', 'oils, thinning, itchy scalp, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Castor Oil Scalp Serum');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Castor Oil Scalp Serum' AND s.size_name = '30ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Castor Oil Scalp Serum' AND s.size_name = '50ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Oils' LIMIT 1), 'CrownCare Baobab and Coconut Hair Oil', 85,
       'A light sealing oil with baobab and coconut for ends and protective styles. How to use: apply to damp hair after a leave-in, or to braids and twists to add shine. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Breakage. FDA registration no.: SAMPLE-0014 (sample).',
       'crowncare-baobab-and-coconut-hair-oil.jpg', 'oils, breakage, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Baobab and Coconut Hair Oil');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Baobab and Coconut Hair Oil' AND s.size_name = '50ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Baobab and Coconut Hair Oil' AND s.size_name = '100ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Hair Creams' LIMIT 1), 'CrownCare Nourishing Hair Butter', 45,
       'A rich butter blend made with Ghanaian shea and cocoa butter for sealing in moisture. How to use: warm a small amount between your palms and apply to ends to seal in moisture. Suitable for hair types 4A, 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0013 (sample).',
       'crowncare-nourishing-hair-butter.jpg', 'hair creams, dryness, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Nourishing Hair Butter');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Nourishing Hair Butter' AND s.size_name = '100g';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Nourishing Hair Butter' AND s.size_name = '250g';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Oils' LIMIT 1), 'CrownCare Scalp and Strength Oil', 110,
       'A blend of moringa, rosemary and jojoba oils for a regular scalp-care routine. Supports a healthy scalp routine; results vary and no growth is guaranteed. How to use: massage a few drops into the scalp two to three times a week. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Thinning. FDA registration no.: SAMPLE-0012 (sample).',
       'crowncare-scalp-and-strength-oil.jpg', 'oils, thinning, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Scalp and Strength Oil');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp and Strength Oil' AND s.size_name = '50ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp and Strength Oil' AND s.size_name = '100ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Leave-in Conditioners' LIMIT 1), 'CrownCare Aloe Daily Hydrating Spray', 55,
       'A water-based aloe vera spray to refresh hair between wash days. How to use: mist onto hair daily or as needed, then seal with an oil or butter. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0011 (sample).',
       'crowncare-aloe-daily-hydrating-spray.jpg', 'leave-in conditioners, dryness, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Aloe Daily Hydrating Spray');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Aloe Daily Hydrating Spray' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Aloe Daily Hydrating Spray' AND s.size_name = '200ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Hair Creams' LIMIT 1), 'CrownCare Whipped Butter Moisturiser', 95,
       'A thick whipped butter with shea and cocoa butter that seals in moisture on tightly coiled hair. How to use: apply a small amount to damp hair after a leave-in, focusing on the ends. Suitable for hair types 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0010 (sample).',
       'crowncare-whipped-butter-moisturiser.jpg', 'hair creams, dryness, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Whipped Butter Moisturiser');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Whipped Butter Moisturiser' AND s.size_name = '150ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Whipped Butter Moisturiser' AND s.size_name = '250ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Leave-in Conditioners' LIMIT 1), 'CrownCare Leave-In Milk', 70,
       'A lightweight leave-in that softens hair without weighing it down. How to use: spray or smooth onto damp hair, then style as usual. Suitable for hair types 3C, 4A, 4B. Helps with: Dryness. FDA registration no.: SAMPLE-0009 (sample).',
       'crowncare-leave-in-milk.jpg', 'leave-in conditioners, dryness, 3c, 4a, 4b' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Leave-In Milk');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Leave-In Milk' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Leave-In Milk' AND s.size_name = '200ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Rinse-out Conditioners' LIMIT 1), 'CrownCare Hibiscus Conditioner', 40,
       'A softening conditioner with hibiscus for looser curl patterns. How to use: apply after cleansing, leave for three minutes, rinse with cool water. Suitable for hair types 3C, 4A. Helps with: Frizz. FDA registration no.: SAMPLE-0008 (sample).',
       'crowncare-hibiscus-conditioner.jpg', 'rinse-out conditioners, frizz, 3c, 4a' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Hibiscus Conditioner');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Hibiscus Conditioner' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Hibiscus Conditioner' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Hibiscus Conditioner' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Deep Conditioners' LIMIT 1), 'CrownCare Protein Repair Treatment', 130,
       'A light protein treatment for hair that snaps or feels weak. Use once a month. How to use: apply to clean hair, leave for 10 minutes, rinse, then follow with a moisturising conditioner. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Breakage. FDA registration no.: SAMPLE-0007 (sample).',
       'crowncare-protein-repair-treatment.jpg', 'deep conditioners, breakage, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Protein Repair Treatment');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Protein Repair Treatment' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Protein Repair Treatment' AND s.size_name = '200ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Deep Conditioners' LIMIT 1), 'CrownCare Deep Moisture Masque', 120,
       'A rich weekly masque with shea butter and baobab oil for very dry hair. How to use: apply to clean, damp hair, cover with a plastic cap for 20 to 30 minutes, rinse. Suitable for hair types 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0006 (sample).',
       'crowncare-deep-moisture-masque.jpg', 'deep conditioners, dryness, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Deep Moisture Masque');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Deep Moisture Masque' AND s.size_name = '150ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Deep Moisture Masque' AND s.size_name = '300ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Rinse-out Conditioners' LIMIT 1), 'CrownCare Detangling Conditioner', 40,
       'A slippery rinse-out conditioner that makes detangling easier. How to use: apply generously after cleansing, detangle in sections with a wide-tooth comb, rinse. Suitable for hair types 4A, 4B, 4C. Helps with: Breakage, Tangles. FDA registration no.: SAMPLE-0005 (sample).',
       'crowncare-detangling-conditioner.jpg', 'rinse-out conditioners, breakage, tangles, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Detangling Conditioner');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Detangling Conditioner' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Detangling Conditioner' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Detangling Conditioner' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Shampoos' LIMIT 1), 'CrownCare Scalp Soothing Shampoo', 45,
       'A fragrance-free shampoo with moringa for sensitive, itchy scalps. How to use: massage gently into the scalp, leave for one minute, rinse. If irritation continues, stop use and see a professional. Suitable for hair types 3C, 4A, 4B, 4C. Helps with: Itchy scalp. FDA registration no.: SAMPLE-0004 (sample).',
       'crowncare-scalp-soothing-shampoo.jpg', 'shampoos, itchy scalp, 3c, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Scalp Soothing Shampoo');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp Soothing Shampoo' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp Soothing Shampoo' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Scalp Soothing Shampoo' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Shampoos' LIMIT 1), 'CrownCare Black Soap Clarifying Shampoo', 35,
       'A clarifying shampoo made with African black soap to lift product buildup. Best used every two to four weeks. How to use: lather on the scalp, rinse thoroughly, then deep condition. Suitable for hair types 4A, 4B, 4C. Helps with: Scalp buildup. FDA registration no.: SAMPLE-0003 (sample).',
       'crowncare-black-soap-clarifying-shampoo.jpg', 'shampoos, scalp buildup, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Black Soap Clarifying Shampoo');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Black Soap Clarifying Shampoo' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Black Soap Clarifying Shampoo' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Black Soap Clarifying Shampoo' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Cleansers' LIMIT 1), 'CrownCare Gentle Co-Wash', 35,
       'A conditioning cleanser with baobab for wash days between shampoos. Cleans lightly while leaving curls soft. How to use: work through wet hair from roots to ends, detangle with fingers, rinse. Suitable for hair types 3C, 4A, 4B. Helps with: Dryness, Frizz. FDA registration no.: SAMPLE-0002 (sample).',
       'crowncare-gentle-co-wash.jpg', 'cleansers, dryness, frizz, 3c, 4a, 4b' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Gentle Co-Wash');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Gentle Co-Wash' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Gentle Co-Wash' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Gentle Co-Wash' AND s.size_name = '500ml';

INSERT INTO products (product_cat, product_title, product_price, product_desc, product_image, product_keywords)
SELECT (SELECT cat_id FROM categories WHERE cat_name = 'Cleansers' LIMIT 1), 'CrownCare Moisture Rich Cleanser', 45,
       'A gentle, low-lather cleanser with shea butter for coily hair that dries out easily. Cleanses without stripping natural oils. How to use: apply to wet hair, massage into the scalp, rinse well. Follow with a conditioner. Suitable for hair types 4A, 4B, 4C. Helps with: Dryness. FDA registration no.: SAMPLE-0001 (sample).',
       'crowncare-moisture-rich-cleanser.jpg', 'cleansers, dryness, 4a, 4b, 4c' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM products WHERE product_title = 'CrownCare Moisture Rich Cleanser');
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Moisture Rich Cleanser' AND s.size_name = '100ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Moisture Rich Cleanser' AND s.size_name = '250ml';
INSERT IGNORE INTO product_size_options (product_id, size_id)
SELECT p.product_id, s.size_id FROM products p, product_sizes s
WHERE p.product_title = 'CrownCare Moisture Rich Cleanser' AND s.size_name = '500ml';

-- 5. Record the migration
INSERT IGNORE INTO schema_migrations (migration_id) VALUES ('004_crowncare_products');

-- 6. Check: should show 20 products with their photo and category
SELECT p.product_id, p.product_title, p.product_price, c.cat_name, p.product_image
FROM products p JOIN categories c ON c.cat_id = p.product_cat
ORDER BY c.cat_name, p.product_title;

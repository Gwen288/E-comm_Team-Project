-- CrownCare: point each product at the photo downloaded from the CMS site.
-- Run after download_crowncare_images.ps1 has filled images/products/.
-- Products are matched by title (not case-sensitive), so titles must match the CMS names.
-- Safe to run more than once.

UPDATE products SET product_image = CASE TRIM(product_title)
    WHEN 'CrownCare Scalp Care Kit' THEN 'crowncare-scalp-care-kit.jpg'
    WHEN 'CrownCare 4C Moisture Starter Kit' THEN 'crowncare-4c-moisture-starter-kit.jpg'
    WHEN 'CrownCare Sleek Edge Control' THEN 'crowncare-sleek-edge-control.jpg'
    WHEN 'CrownCare Flaxseed Styling Gel' THEN 'crowncare-flaxseed-styling-gel.jpg'
    WHEN 'CrownCare Curl Defining Custard' THEN 'crowncare-curl-defining-custard.jpg'
    WHEN 'CrownCare Castor Oil Scalp Serum' THEN 'crowncare-castor-oil-scalp-serum.jpg'
    WHEN 'CrownCare Baobab and Coconut Hair Oil' THEN 'crowncare-baobab-and-coconut-hair-oil.jpg'
    WHEN 'CrownCare Nourishing Hair Butter' THEN 'crowncare-nourishing-hair-butter.jpg'
    WHEN 'CrownCare Scalp and Strength Oil' THEN 'crowncare-scalp-and-strength-oil.jpg'
    WHEN 'CrownCare Aloe Daily Hydrating Spray' THEN 'crowncare-aloe-daily-hydrating-spray.jpg'
    WHEN 'CrownCare Whipped Butter Moisturiser' THEN 'crowncare-whipped-butter-moisturiser.jpg'
    WHEN 'CrownCare Leave-In Milk' THEN 'crowncare-leave-in-milk.jpg'
    WHEN 'CrownCare Hibiscus Conditioner' THEN 'crowncare-hibiscus-conditioner.jpg'
    WHEN 'CrownCare Protein Repair Treatment' THEN 'crowncare-protein-repair-treatment.jpg'
    WHEN 'CrownCare Deep Moisture Masque' THEN 'crowncare-deep-moisture-masque.jpg'
    WHEN 'CrownCare Detangling Conditioner' THEN 'crowncare-detangling-conditioner.jpg'
    WHEN 'CrownCare Scalp Soothing Shampoo' THEN 'crowncare-scalp-soothing-shampoo.jpg'
    WHEN 'CrownCare Black Soap Clarifying Shampoo' THEN 'crowncare-black-soap-clarifying-shampoo.jpg'
    WHEN 'CrownCare Gentle Co-Wash' THEN 'crowncare-gentle-co-wash.jpg'
    WHEN 'CrownCare Moisture Rich Cleanser' THEN 'crowncare-moisture-rich-cleanser.jpg'
    ELSE product_image
END;

-- Products that still have no CMS photo (their title differs from the CMS name):
SELECT product_id, product_title, product_image
FROM products
WHERE TRIM(product_title) NOT IN (
    'CrownCare Scalp Care Kit',
    'CrownCare 4C Moisture Starter Kit',
    'CrownCare Sleek Edge Control',
    'CrownCare Flaxseed Styling Gel',
    'CrownCare Curl Defining Custard',
    'CrownCare Castor Oil Scalp Serum',
    'CrownCare Baobab and Coconut Hair Oil',
    'CrownCare Nourishing Hair Butter',
    'CrownCare Scalp and Strength Oil',
    'CrownCare Aloe Daily Hydrating Spray',
    'CrownCare Whipped Butter Moisturiser',
    'CrownCare Leave-In Milk',
    'CrownCare Hibiscus Conditioner',
    'CrownCare Protein Repair Treatment',
    'CrownCare Deep Moisture Masque',
    'CrownCare Detangling Conditioner',
    'CrownCare Scalp Soothing Shampoo',
    'CrownCare Black Soap Clarifying Shampoo',
    'CrownCare Gentle Co-Wash',
    'CrownCare Moisture Rich Cleanser'
);

INSERT IGNORE INTO schema_migrations (migration_id)
VALUES ('002_cms_product_images');

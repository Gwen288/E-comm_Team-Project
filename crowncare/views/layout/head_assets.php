<?php
/*
 * Shared <head> items for every layout (store, admin, consultant):
 * form token, favicon, page description and the shared stylesheet.
 * Included by layout/header.php, layout/admin_header.php and
 * layout/consultant_header.php. Pages may set $pageTitle and
 * $pageDescription before the layout is included.
 */
?>
    <?php /* js/app.js reads this token for AJAX POSTs. */ ?>
    <meta name="csrf-token" content="<?php echo e(cc_csrf_token()); ?>">
    <meta name="description" content="<?php echo e($pageDescription ?? 'CrownCare: natural hair care products, hair guides and consultations for African and textured hair, delivered in Accra.'); ?>">
    <link rel="icon" href="<?php echo BASE_URL; ?>images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/app.css?v=20261122-5">

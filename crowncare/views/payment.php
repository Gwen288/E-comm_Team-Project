<?php
require_once __DIR__ . '/../core/core.php';

// Payment is started from the checkout page; this view is kept as a safe
// fallback for any old links that still point to views/payment.php.
redirect(BASE_URL . 'views/checkout.php');

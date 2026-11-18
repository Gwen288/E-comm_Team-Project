<?php

/**
 * Paystack configuration.
 *
 * Copy this file to paystack_config.php and replace the placeholder values
 * with your own Paystack test keys.
 *
 * Dashboard: https://dashboard.paystack.com/#/settings/developers
 *
 * paystack_config.php is ignored by Git so keys are not committed.
 */

define('PAYSTACK_MODE', 'test');

define('PAYSTACK_PUBLIC_KEY', 'pk_test_8114a5a477d21b087612049ca7b0899e93fdf022');
define('PAYSTACK_SECRET_KEY', 'sk_test_438364dbcf54d4f792ff2fa7fbc9f26e48b2e229');

define('PAYSTACK_CURRENCY', 'GHS');

/* Flat delivery fee in Ghana cedis. */
define('DELIVERY_FEE', 20.00);

/* Orders at or above this subtotal get free delivery. */
define('FREE_DELIVERY_THRESHOLD', 300.00);

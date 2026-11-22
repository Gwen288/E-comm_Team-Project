<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

/*
 * Removes one cart line from views/cart.php (POST line=<product id>:<size id>).
 * Guests and customers can use it; staff are sent to their dashboard.
 * Always back to views/cart.php.
 */

block_admin_purchase();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/cart.php');
}

// The id and size travel together: one product can have a separate line per size.
$line = (string) ($_POST['line'] ?? '');

if (preg_match('/^(\d+):(\d+)$/', $line, $m)) {
    (new CartController())->removeFromCart((int) $m[1], get_customer_id(), (int) $m[2]);
    $_SESSION['success'] = 'Item removed from your cart.';
} else {
    $_SESSION['error'] = 'We could not remove that item. Please try again.';
}

redirect(BASE_URL . 'views/cart.php');

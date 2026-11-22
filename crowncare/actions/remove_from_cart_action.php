<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

/*
 * Removes one cart line.
 * POST line=<product id>:<size id>
 */

block_admin_purchase();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/cart.php');
}

$line = (string) ($_POST['line'] ?? '');

if (preg_match('/^(\d+):(\d+)$/', $line, $m)) {
    (new CartController())->removeFromCart((int) $m[1], get_customer_id(), (int) $m[2]);
    $_SESSION['success'] = 'Item removed from your cart.';
} else {
    $_SESSION['error'] = 'We could not remove that item. Please try again.';
}

redirect(BASE_URL . 'views/cart.php');

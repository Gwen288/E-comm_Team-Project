<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

/*
 * Changes the quantity of one cart line from views/cart.php.
 * Guests and customers can use it; staff are sent to their dashboard.
 * POST line=<product id>:<size id>, qty=<new quantity>. A quantity of 0 removes the line.
 * Always back to views/cart.php.
 */

block_admin_purchase();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/cart.php');
}

$line = (string) ($_POST['line'] ?? '');
$qty = filter_var($_POST['qty'] ?? null, FILTER_VALIDATE_INT);

if (!preg_match('/^(\d+):(\d+)$/', $line, $m) || $qty === false || $qty === null || $qty < 0) {
    $_SESSION['error'] = 'We could not change that quantity. Please try again.';
    redirect(BASE_URL . 'views/cart.php');
}

$productId = (int) $m[1];
$sizeId = (int) $m[2];
$controller = new CartController();
$customerId = get_customer_id();

if ($qty === 0) {
    $controller->removeFromCart($productId, $customerId, $sizeId);
    $_SESSION['success'] = 'Item removed from your cart.';
} else {
    // Same 99 cap as Add to Cart.
    $controller->updateQty($productId, min(99, $qty), $customerId, $sizeId);
    if ($controller->lastMessage !== '') {
        $_SESSION['info'] = $controller->lastMessage;
    }
}

redirect(BASE_URL . 'views/cart.php');

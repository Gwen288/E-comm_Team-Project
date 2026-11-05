<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

block_admin_purchase();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/cart.php');
}

$removeItems = $_POST['remove'] ?? [];

if (!is_array($removeItems)) {
    $_SESSION['cart_message'] = 'Invalid removal request.';
    redirect(BASE_URL . 'views/cart.php');
}

$controller = new CartController();
$customerId = get_customer_id();
$removed = 0;

foreach ($removeItems as $productId) {
    $productId = filter_var($productId, FILTER_VALIDATE_INT);

    if ($productId !== false && $productId > 0) {
        if ($controller->removeFromCart($productId, $customerId)) {
            $removed++;
        }
    }
}

if ($removed > 0) {
    $_SESSION['cart_message'] = 'Selected items have been removed from your cart.';
} else {
    $_SESSION['cart_message'] = 'Select at least one item to remove.';
}

redirect(BASE_URL . 'views/cart.php');

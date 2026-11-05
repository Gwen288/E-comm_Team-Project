<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

if (!isset($_GET['add_cart']) || !ctype_digit((string) $_GET['add_cart'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$product_id = (int) $_GET['add_cart'];

if ($product_id <= 0) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

/* Admins are redirected quietly — no purchase messaging. */
$referer = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'index.php');
block_admin_purchase($referer);

$controller = new CartController();
$customerId = get_customer_id();

if ($controller->isInCart($product_id, $customerId)) {
    $_SESSION['cart_message'] =
        'This item is already in your cart. Open cart to update quantity.';
} else {
    $success = $controller->addToCart($product_id, 1, $customerId);

    if ($success) {
        $_SESSION['cart_message'] = 'Item added to your cart.';
    } else {
        $_SESSION['cart_message'] = 'Unable to add this item to your cart.';
    }
}

/* Stay where the shopper was — they open cart when they want. */
$referer = $_SERVER['HTTP_REFERER'] ?? '';

if ($referer !== '') {
    $refererHost = parse_url($referer, PHP_URL_HOST);
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';

    if ($refererHost !== null && strcasecmp($refererHost, $currentHost) === 0) {
        header('Location: ' . $referer);
        exit;
    }
}

header('Location: ' . BASE_URL . 'views/shop.php');
exit;

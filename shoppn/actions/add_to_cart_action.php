
<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['add_cart']) || !ctype_digit((string) $_GET['add_cart'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$product_id = (int) $_GET['add_cart'];

if ($product_id <= 0) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$ip = get_ip();
$controller = new CartController();

$customer_id = null;

if (is_logged_in() && isset($_SESSION['customer_id'])) {
    $customer_id = (int) $_SESSION['customer_id'];
}

// Prevent duplicate products in the same IP-based cart.
if ($controller->isInCart($product_id, $ip)) {
    $_SESSION['cart_message'] =
        'This item is already in your cart. Go to cart to update quantity.';
} else {
    $success = $controller->addToCart(
        $product_id,
        1,
        $ip,
        $customer_id
    );

    if ($success) {
        $_SESSION['cart_message'] = 'Item added to your cart successfully.';
    } else {
        $_SESSION['cart_message'] = 'Unable to add this item to your cart.';
    }
}

// Return the customer to the page they came from.
$referer = $_SERVER['HTTP_REFERER'] ?? '';

if ($referer !== '') {
    $refererHost = parse_url($referer, PHP_URL_HOST);
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';

    if ($refererHost !== null && strcasecmp($refererHost, $currentHost) === 0) {
        header('Location: ' . $referer);
        exit;
    }
}

header('Location: ' . BASE_URL . 'index.php');
exit;

<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

/*
 * Adds a product to the cart (POST from views/single_product.php):
 * product_id, size_id, qty, and buy_now=1 for the Buy Now button.
 * Guests and customers can use it; staff are sent to their dashboard.
 * Buy Now goes to checkout, everything else back to the shopper's previous page.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/shop.php');
}

$productId = (int) ($_POST['product_id'] ?? 0);
$sizeId = (int) ($_POST['size_id'] ?? 0);
// Ignore impossible quantities from a hand-made request.
$qty = max(1, min(99, (int) ($_POST['qty'] ?? 1)));
$buyNow = !empty($_POST['buy_now']);

$productUrl = BASE_URL . 'views/single_product.php?pro_id=' . $productId;

if ($productId <= 0) {
    redirect(BASE_URL . 'views/shop.php');
}

// Admins and consultants can't buy.
block_admin_purchase();

$controller = new CartController();
$customerId = get_customer_id();

// Sized products need a size, or the stock check would be for the wrong line.
if ($sizeId === 0 && $controller->productHasSizes($productId)) {
    $_SESSION['size_error'] = true;
    redirect($productUrl);
}

if (!$controller->addToCart($productId, $qty, $customerId, $sizeId)) {
    $_SESSION['error'] = $controller->lastMessage ?: 'We could not add this item to your cart.';
    redirect($productUrl . ($sizeId ? '&size=' . $sizeId : ''));
}

if ($buyNow) {
    redirect(BASE_URL . 'views/checkout.php');
}

if ($controller->lastMessage !== '') {
    $_SESSION['info'] = $controller->lastMessage;
} else {
    $_SESSION['success'] = 'Added to your cart.';
}

// Stay on the page the shopper was on, keeping the chosen size selected.
$back = cc_back_url($productUrl);
if ($sizeId && strpos($back, 'single_product.php') !== false) {
    $back = preg_replace('/([?&])size=\d+/', '$1size=' . $sizeId, $back, 1, $replaced);
    if (!$replaced) {
        $back .= (strpos($back, '?') === false ? '?' : '&') . 'size=' . $sizeId;
    }
}

redirect($back);

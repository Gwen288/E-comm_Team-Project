<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

/*
 * Adds a product to the cart.
 * POST (product page): product_id, size_id, qty, and buy_now=1 for the Buy Now button.
 * GET  (older links):  ?add_cart=<product id>. A product sold in sizes opens its
 *                      product page instead, so the shopper can choose a size.
 */

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$productId = (int) ($isPost ? ($_POST['product_id'] ?? 0) : ($_GET['add_cart'] ?? 0));
$sizeId = (int) ($isPost ? ($_POST['size_id'] ?? 0) : 0);
$qty = (int) ($isPost ? ($_POST['qty'] ?? 1) : 1);
$buyNow = $isPost && !empty($_POST['buy_now']);

$productUrl = BASE_URL . 'views/single_product.php?pro_id=' . $productId;

if ($productId <= 0) {
    redirect(BASE_URL . 'views/shop.php');
}

/* Admins are redirected quietly, with no purchase messaging. */
block_admin_purchase($_SERVER['HTTP_REFERER'] ?? $productUrl);

$qty = max(1, min(99, $qty));
$controller = new CartController();
$customerId = get_customer_id();

if ($sizeId === 0 && $controller->productHasSizes($productId)) {
    $_SESSION['size_error'] = true;
    redirect($productUrl);
}

if (!$controller->addToCart($productId, $qty, $customerId, $sizeId)) {
    $_SESSION['cart_message'] = 'Unable to add this item to your cart.';
    redirect($productUrl . ($sizeId ? '&size=' . $sizeId : ''));
}

if ($buyNow) {
    redirect(BASE_URL . 'views/checkout.php');
}

$_SESSION['cart_message'] = 'Added to your cart.';

/* Stay on the page the shopper was on, as on the CMS. */
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$refererHost = $referer !== '' ? parse_url($referer, PHP_URL_HOST) : null;

if ($refererHost !== null && strcasecmp($refererHost, $_SERVER['HTTP_HOST'] ?? '') === 0) {
    // Keep the chosen size selected when returning to the product page.
    if ($sizeId && strpos($referer, 'single_product.php') !== false) {
        $referer = preg_replace('/([?&])size=\d+/', '$1size=' . $sizeId, $referer, 1, $replaced);
        if (!$replaced) {
            $referer .= (strpos($referer, '?') === false ? '?' : '&') . 'size=' . $sizeId;
        }
    }
    redirect($referer);
}

redirect($productUrl);

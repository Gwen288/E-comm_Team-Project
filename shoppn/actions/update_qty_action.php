
<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Accept POST requests only.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'views/cart.php');
    exit;
}

// Get the submitted quantity and product ID arrays.
$quantities = $_POST['qty'] ?? [];
$product_ids = $_POST['update'] ?? [];

// Ensure both submissions are arrays.
if (!is_array($quantities) || !is_array($product_ids)) {
    $_SESSION['cart_message'] = 'Invalid quantity update request.';
    header('Location: ' . BASE_URL . 'views/cart.php');
    exit;
}

// Both arrays must contain matching entries.
if (count($quantities) !== count($product_ids)) {
    $_SESSION['cart_message'] = 'Invalid quantity update request.';
    header('Location: ' . BASE_URL . 'views/cart.php');
    exit;
}

$cartController = new CartController();
$ip = get_ip();
$updated = 0;
$invalid = false;

// Process each submitted product.
foreach ($product_ids as $key => $product_id) {
    $product_id = filter_var($product_id, FILTER_VALIDATE_INT);
    $new_qty = filter_var(
        $quantities[$key] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        $product_id === false || $product_id === null || $product_id < 1 ||
        $new_qty === false || $new_qty === null || $new_qty < 1
    ) {
        $invalid = true;
        continue;
    }

    if ($cartController->updateQty($product_id, $new_qty, $ip)) {
        $updated++;
    }
}

// Display feedback.
if ($invalid) {
    $_SESSION['cart_message'] = 'Some quantities were invalid. Valid quantities were processed.';
} elseif ($updated > 0) {
    $_SESSION['cart_message'] = 'Cart quantities updated successfully.';
} else {
    $_SESSION['cart_message'] = 'No quantities were updated.';
}

// Return to the cart page.
header('Location: ' . BASE_URL . 'views/cart.php');
exit;

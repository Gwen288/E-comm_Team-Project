
<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

/*
 * Deletes a product from the admin list at views/admin/product.php. Admin only.
 * Always back to that list; a product tied to other records gets a friendlier message.
 */

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/admin/product.php');
}

// Missing field gives null, non-numeric gives false; neither is a product to delete.
$productId = filter_input(
    INPUT_POST,
    'product_id',
    FILTER_VALIDATE_INT
);

if ($productId === false || $productId === null || $productId < 1) {
    $_SESSION['error'] = 'Invalid product selected.';
    redirect(BASE_URL . 'views/admin/product.php');
}

$controller = new ProductController();

try {
    $deleted = $controller->deleteProduct($productId);

    if ($deleted) {
        $_SESSION['success'] = 'Product deleted successfully.';
    } else {
        $_SESSION['error'] = 'The product could not be deleted.';
    }
} catch (mysqli_sql_exception $exception) {
    // An order line may still point at this product; explain that instead of showing the query error.
    $_SESSION['error'] =
        'This product could not be deleted because it may be linked to other records.';
}

redirect(BASE_URL . 'views/admin/product.php');

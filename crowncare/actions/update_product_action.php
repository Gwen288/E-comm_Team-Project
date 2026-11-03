
<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

$redirectUrl = BASE_URL . 'views/admin/product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($redirectUrl);
}

$controller = new ProductController();

$id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT);

if ($id === false || $id <= 0) {
    $_SESSION['error'] = 'Invalid product ID.';
    redirect($redirectUrl);
}

$existingProduct = $controller->getProductById($id);

if ($existingProduct === false) {
    $_SESSION['error'] = 'Product not found.';
    redirect($redirectUrl);
}

$cat = filter_var($_POST['product_cat'] ?? '', FILTER_VALIDATE_INT);
$brand = filter_var($_POST['product_brand'] ?? '', FILTER_VALIDATE_INT);
$title = trim(strip_tags($_POST['product_title'] ?? ''));
$priceInput = trim($_POST['product_price'] ?? '');
$desc = trim(strip_tags($_POST['product_desc'] ?? ''));
$keywords = trim(strip_tags($_POST['product_keywords'] ?? ''));

if (
    $cat === false || $cat <= 0 ||
    $brand === false || $brand <= 0 ||
    $title === '' ||
    !is_numeric($priceInput) ||
    (float)$priceInput < 0 ||
    $desc === '' ||
    $keywords === ''
) {
    $_SESSION['error'] = 'Please complete all product fields correctly.';
    redirect($redirectUrl . '?edit_id=' . $id);
}

$price = (float)$priceInput;

if (
    $controller->getCategoryById($cat) === false ||
    $controller->getBrandById($brand) === false
) {
    $_SESSION['error'] = 'Please select a valid brand and category.';
    redirect($redirectUrl . '?edit_id=' . $id);
}

// Default to the image already saved in the database.
$filename = $existingProduct['product_image'];
$newImagePath = null;

if (
    isset($_FILES['product_image']) &&
    $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE
) {
    $file = $_FILES['product_image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'The new image could not be uploaded.';
        redirect($redirectUrl . '?edit_id=' . $id);
    }

    $maxSize = 5 * 1024 * 1024;

    if ($file['size'] <= 0 || $file['size'] > $maxSize) {
        $_SESSION['error'] = 'The image must be smaller than 5 MB.';
        redirect($redirectUrl . '?edit_id=' . $id);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp'
    ];

    if (!isset($allowedTypes[$mimeType])) {
        $_SESSION['error'] = 'Only JPEG, PNG, GIF, and WebP images are allowed.';
        redirect($redirectUrl . '?edit_id=' . $id);
    }

    $filename = uniqid('product_', true) . '.' . $allowedTypes[$mimeType];

    $uploadDirectory = __DIR__ . '/../images/products/';

    if (
        !is_dir($uploadDirectory) &&
        !mkdir($uploadDirectory, 0755, true)
    ) {
        $_SESSION['error'] = 'Could not create the product image directory.';
        redirect($redirectUrl . '?edit_id=' . $id);
    }

    $newImagePath = $uploadDirectory . $filename;

    if (!move_uploaded_file($file['tmp_name'], $newImagePath)) {
        $_SESSION['error'] = 'Could not save the new image.';
        redirect($redirectUrl . '?edit_id=' . $id);
    }
}

$result = $controller->updateProduct(
    $id,
    $cat,
    $brand,
    $title,
    $price,
    $desc,
    $filename,
    $keywords
);

if ($result) {
    // Delete the previous image only after a successful database update.
    if (
        $newImagePath !== null &&
        !empty($existingProduct['product_image'])
    ) {
        $oldImagePath = __DIR__ . '/../images/products/'
            . basename($existingProduct['product_image']);

        if (
            is_file($oldImagePath) &&
            realpath($oldImagePath) !== realpath($newImagePath)
        ) {
            unlink($oldImagePath);
        }
    }

    $_SESSION['success'] = 'Product updated successfully.';
    redirect($redirectUrl);
}

// If the update failed, retain the previous image.
if ($newImagePath !== null && is_file($newImagePath)) {
    unlink($newImagePath);
}

$_SESSION['error'] = 'Failed to update the product.';
redirect($redirectUrl . '?edit_id=' . $id);

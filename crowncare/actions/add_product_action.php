
<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

$redirectUrl = BASE_URL . 'views/admin/product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($redirectUrl);
}

$controller = new ProductController();

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
    redirect($redirectUrl);
}

$price = (float)$priceInput;

if (
    $controller->getCategoryById($cat) === false ||
    $controller->getBrandById($brand) === false
) {
    $_SESSION['error'] = 'Please select a valid brand and category.';
    redirect($redirectUrl);
}

if (
    !isset($_FILES['product_image']) ||
    $_FILES['product_image']['error'] !== UPLOAD_ERR_OK
) {
    $_SESSION['error'] = 'Please select a product image.';
    redirect($redirectUrl);
}

$file = $_FILES['product_image'];
$maxSize = 5 * 1024 * 1024;

if ($file['size'] <= 0 || $file['size'] > $maxSize) {
    $_SESSION['error'] = 'The image must be smaller than 5 MB.';
    redirect($redirectUrl);
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
    redirect($redirectUrl);
}

$filename = uniqid('product_', true) . '.' . $allowedTypes[$mimeType];

$uploadDirectory = __DIR__ . '/../images/products/';

if (
    !is_dir($uploadDirectory) &&
    !mkdir($uploadDirectory, 0755, true)
) {
    $_SESSION['error'] = 'Could not create the product image directory.';
    redirect($redirectUrl);
}

$destination = $uploadDirectory . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    $_SESSION['error'] = 'Could not save the uploaded image.';
    redirect($redirectUrl);
}

$result = $controller->addProduct(
    $cat,
    $brand,
    $title,
    $price,
    $desc,
    $filename,
    $keywords
);

if ($result) {
    $_SESSION['success'] = 'Product added successfully.';
} else {
    if (is_file($destination)) {
        unlink($destination);
    }

    $_SESSION['error'] = 'Failed to add the product.';
}

redirect($redirectUrl);

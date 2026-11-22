
<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

/*
 * Handles the Add Product form in views/admin/product_form.php. Admin only.
 * Both successes and failures end up on views/admin/product.php.
 */

require_admin();

$redirectUrl = BASE_URL . 'views/admin/product.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($redirectUrl);
}

$controller = new ProductController();

$cat = filter_var($_POST['product_cat'] ?? '', FILTER_VALIDATE_INT);
$title = trim(strip_tags($_POST['product_title'] ?? ''));
$priceInput = trim($_POST['product_price'] ?? '');
$desc = trim(strip_tags($_POST['product_desc'] ?? ''));
$keywords = trim(strip_tags($_POST['product_keywords'] ?? ''));

if (
    $cat === false || $cat <= 0 ||
    $title === '' ||
    !is_numeric($priceInput) ||
    (float) $priceInput < 0 ||
    $desc === '' ||
    $keywords === ''
) {
    $_SESSION['error'] = 'Please complete all product fields correctly.';
    redirect($redirectUrl);
}

$price = (float) $priceInput;

// A stale or hand-made category id should not create an orphan product.
if ($controller->getCategoryById($cat) === false) {
    $_SESSION['error'] = 'Please select a valid category.';
    redirect($redirectUrl);
}

// Read and validate selected product sizes.
$submittedSizes = $_POST['product_sizes'] ?? [];

if (!is_array($submittedSizes)) {
    $_SESSION['error'] = 'Invalid product size selection.';
    redirect($redirectUrl);
}

$availableSizes = $controller->getAllSizes();
$validSizeIds = array_map(
    'intval',
    array_column($availableSizes, 'size_id')
);

$productSizes = [];

foreach ($submittedSizes as $sizeId) {
    $validatedSizeId = filter_var($sizeId, FILTER_VALIDATE_INT);

    if (
        $validatedSizeId === false ||
        $validatedSizeId <= 0 ||
        !in_array($validatedSizeId, $validSizeIds, true)
    ) {
        $_SESSION['error'] = 'Please select valid product sizes.';
        redirect($redirectUrl);
    }

    $productSizes[] = $validatedSizeId;
}

$productSizes = array_values(array_unique($productSizes));

// Validate the uploaded image.
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

// Check the file itself, not the name the browser gave it.
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

// A new name for every upload so nothing gets overwritten.
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

// Save the product together with its selected sizes.
$result = $controller->addProduct(
    $cat,
    $title,
    $price,
    $desc,
    $filename,
    $keywords,
    $productSizes
);

if ($result) {
    // Stock levels come from the size fields on the same form.
    $controller->saveStockFromForm((int) $result, $productSizes, $_POST);
    $_SESSION['success'] = 'Product added successfully.';
} else {
    // Don't leave an image behind when the product wasn't saved.
    if (is_file($destination)) {
        unlink($destination);
    }

    $_SESSION['error'] = 'Failed to add the product.';
}

redirect($redirectUrl);


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
if (!isset($_FILES['product_image'])) {
    $_SESSION['error'] = 'Please select a product image.';
    redirect($redirectUrl);
}

if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = cc_upload_error_message($_FILES['product_image']['error']);
    redirect($redirectUrl);
}

$file = $_FILES['product_image'];
$maxSize = cc_upload_limit();

if ($file['size'] <= 0 || $file['size'] > $maxSize) {
    $_SESSION['error'] = 'The image must be smaller than ' . cc_upload_limit_label() . '.';
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

// Saved as a file in images/products, or in the database when the server does
// not allow writing to that folder (core/image_store.php).
if (!cc_save_product_image($file['tmp_name'], $filename, $mimeType)) {
    $_SESSION['error'] = cc_upload_folder_message();
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
    $controller->saveStockFromForm((int) $result, $productSizes, $_POST);
    $_SESSION['success'] = 'Product added successfully.';
} else {
    cc_delete_product_image($filename);

    $_SESSION['error'] = 'Failed to add the product.';
}

redirect($redirectUrl);

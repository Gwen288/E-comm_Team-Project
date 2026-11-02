<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/admin/brand.php');
}

$brandId = $_POST['brand_id'] ?? '';
$name = trim(strip_tags($_POST['brand_name'] ?? ''));

if (
    !filter_var($brandId, FILTER_VALIDATE_INT) ||
    (int)$brandId <= 0
) {
    $_SESSION['error'] = 'Invalid brand ID.';
    redirect(BASE_URL . 'views/admin/brand.php');
}

if ($name === '') {
    $_SESSION['error'] = 'Brand name is required.';
    redirect(
        BASE_URL . 'views/admin/brand.php?edit_id=' . (int)$brandId
    );
}

$brandId = (int)$brandId;

$controller = new ProductController();

$result = $controller->updateBrand($brandId, $name);

if ($result) {
    $_SESSION['success'] = 'Brand updated.';
} else {
    $_SESSION['error'] = 'Failed to update brand.';
}

redirect(BASE_URL . 'views/admin/brand.php');
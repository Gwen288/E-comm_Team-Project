<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/admin/brand.php');
}

$name = trim(strip_tags($_POST['brand_name'] ?? ''));

if ($name === '') {
    $_SESSION['error'] = 'Brand name is required.';
    redirect(BASE_URL . 'views/admin/brand.php');
}

$controller = new ProductController();

if ($controller->brandExists($name)) {
    $_SESSION['error'] = 'This brand already exists.';
    redirect(BASE_URL . 'views/admin/brand.php');
}

$result = $controller->addBrand($name);

if ($result) {
    $_SESSION['success'] = 'Brand added.';
} else {
    $_SESSION['error'] = 'Failed to add brand.';
}

redirect(BASE_URL . 'views/admin/brand.php');
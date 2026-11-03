<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/admin/category.php');
}

$name = trim(strip_tags($_POST['cat_name'] ?? ''));

if ($name === '') {
    $_SESSION['error'] = 'Category name is required.';
    redirect(BASE_URL . 'views/admin/category.php');
}

$controller = new ProductController();

if ($controller->categoryExists($name)) {
    $_SESSION['error'] = 'This category already exists.';
    redirect(BASE_URL . 'views/admin/category.php');
}

$result = $controller->addCategory($name);

if ($result) {
    $_SESSION['success'] = 'Category added.';
} else {
    $_SESSION['error'] = 'Failed to add category.';
}

redirect(BASE_URL . 'views/admin/category.php');

<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

/*
 * Adds a category from views/admin/category_form.php. Admin only.
 * Bad input goes back to the form, a saved one to views/admin/category.php.
 */

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/admin/category.php');
}

// Strip tags so markup doesn't end up stored as a category name.
$name = trim(strip_tags($_POST['cat_name'] ?? ''));
$parentIdInput = $_POST['parent_category_id'] ?? '';

if ($name === '') {
    $_SESSION['error'] = 'Category name is required.';
    redirect(BASE_URL . 'views/admin/category_form.php');
}

$controller = new ProductController();

// Prevent duplicate category names.
if ($controller->categoryExists($name)) {
    $_SESSION['error'] = 'This category already exists.';
    redirect(BASE_URL . 'views/admin/category_form.php');
}

// An empty parent means this is a main category.
$parentId = null;

if ($parentIdInput !== '') {

    if (
        !filter_var($parentIdInput, FILTER_VALIDATE_INT) ||
        (int) $parentIdInput <= 0
    ) {
        $_SESSION['error'] = 'Invalid parent category.';
        redirect(BASE_URL . 'views/admin/category_form.php');
    }

    $parentId = (int) $parentIdInput;

    // Only existing main categories can be selected as parents.
    $mainCategories = $controller->getMainCategories();
    $validParent = false;

    foreach ($mainCategories as $mainCategory) {
        if ((int) $mainCategory['cat_id'] === $parentId) {
            $validParent = true;
            break;
        }
    }

    if (!$validParent) {
        $_SESSION['error'] = 'The selected parent category is invalid.';
        redirect(BASE_URL . 'views/admin/category_form.php');
    }
}

$result = $controller->addCategory($name, $parentId);

if ($result) {
    $_SESSION['success'] = 'Category added.';
} else {
    $_SESSION['error'] = 'Failed to add category.';
}

redirect(BASE_URL . 'views/admin/category.php');

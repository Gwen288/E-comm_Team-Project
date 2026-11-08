
<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/admin/category.php');
}

$categoryId = $_POST['cat_id'] ?? '';
$name = trim(strip_tags($_POST['cat_name'] ?? ''));
$parentIdInput = $_POST['parent_category_id'] ?? '';

if (
    !filter_var($categoryId, FILTER_VALIDATE_INT) ||
    (int) $categoryId <= 0
) {
    $_SESSION['error'] = 'Invalid category ID.';
    redirect(BASE_URL . 'views/admin/category.php');
}

$categoryId = (int) $categoryId;

$formUrl = BASE_URL . 'views/admin/category_form.php?edit_id=' . $categoryId;

if ($name === '') {
    $_SESSION['error'] = 'Category name is required.';
    redirect($formUrl);
}

$controller = new ProductController();

// Confirm that the category being edited exists.
$existingCategory = $controller->getCategoryById($categoryId);

if ($existingCategory === false) {
    $_SESSION['error'] = 'Category not found.';
    redirect(BASE_URL . 'views/admin/category.php');
}

// Process the parent category.
$parentId = null;

if ($parentIdInput !== '') {

    if (
        !filter_var($parentIdInput, FILTER_VALIDATE_INT) ||
        (int) $parentIdInput <= 0
    ) {
        $_SESSION['error'] = 'Invalid parent category.';
        redirect($formUrl);
    }

    $parentId = (int) $parentIdInput;

    // A category cannot be its own parent.
    if ($parentId === $categoryId) {
        $_SESSION['error'] = 'A category cannot be its own parent.';
        redirect($formUrl);
    }

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
        redirect($formUrl);
    }
}

$result = $controller->updateCategory(
    $categoryId,
    $name,
    $parentId
);

if ($result) {
    $_SESSION['success'] = 'Category updated.';
} else {
    $_SESSION['error'] = 'Failed to update category.';
}

redirect(BASE_URL . 'views/admin/category.php');


<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();

$editId = $_GET['edit_id'] ?? null;
$editCategory = false;

if ($editId !== null) {
    if (!filter_var($editId, FILTER_VALIDATE_INT) || (int) $editId <= 0) {
        $_SESSION['error'] = 'Invalid category ID.';
        redirect(BASE_URL . 'views/admin/category.php');
    }

    $editId = (int) $editId;
    $editCategory = $controller->getCategoryById($editId);

    if ($editCategory === false) {
        $_SESSION['error'] = 'Category not found.';
        redirect(BASE_URL . 'views/admin/category.php');
    }
}

// Retrieve main categories for the parent-category dropdown.
$mainCategories = $controller->getMainCategories();

$currentParentId = $editCategory !== false
    ? ($editCategory['parent_category_id'] ?? '')
    : '';

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1><?php echo $editCategory !== false ? 'Edit category' : 'Add category'; ?></h1>
        <p>
            <?php
            echo $editCategory !== false
                ? 'Update the category name or its parent category.'
                : 'Add a main category or create a subcategory.';
            ?>
        </p>
    </div>

    <a class="admin-btn admin-btn-secondary"
       href="<?php echo BASE_URL; ?>views/admin/category.php">
        All categories
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="admin-message admin-success">
        <?php
        echo htmlspecialchars($_SESSION['success']);
        unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="admin-message admin-error">
        <?php
        echo htmlspecialchars($_SESSION['error']);
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<section class="admin-card">

    <form
        action="<?php
            echo BASE_URL . (
                $editCategory !== false
                    ? 'actions/update_category_action.php'
                    : 'actions/add_category_action.php'
            );
        ?>"
        method="POST"
        class="admin-form"
    >

        <?php if ($editCategory !== false): ?>
            <input
                type="hidden"
                name="cat_id"
                value="<?php echo (int) $editCategory['cat_id']; ?>"
            >
        <?php endif; ?>

        <div class="form-group">
            <label for="cat_name">Category Name</label>

            <input
                type="text"
                id="cat_name"
                name="cat_name"
                maxlength="100"
                placeholder="e.g. Hair Care"
                value="<?php
                    echo htmlspecialchars(
                        $editCategory['cat_name'] ?? ''
                    );
                ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="parent_category_id">Parent Category</label>

            <select
                id="parent_category_id"
                name="parent_category_id"
            >
                <option value="">None (Main Category)</option>

                <?php foreach ($mainCategories as $mainCategory): ?>

                    <?php
                    $mainCategoryId = (int) $mainCategory['cat_id'];

                    // A category cannot be its own parent.
                    if (
                        $editCategory !== false &&
                        $mainCategoryId === (int) $editCategory['cat_id']
                    ) {
                        continue;
                    }
                    ?>

                    <option
                        value="<?php echo $mainCategoryId; ?>"
                        <?php
                        echo (
                            $currentParentId !== '' &&
                            (int) $currentParentId === $mainCategoryId
                        ) ? 'selected' : '';
                        ?>
                    >
                        <?php echo htmlspecialchars($mainCategory['cat_name']); ?>
                    </option>

                <?php endforeach; ?>
            </select>

            <small>
                Select a main category to make this a subcategory.
                Leave it as None to create a main category.
            </small>
        </div>

        <div class="form-actions">
            <button type="submit" class="admin-btn admin-btn-primary">
                <?php echo $editCategory !== false ? 'Update category' : 'Add category'; ?>
            </button>

            <a
                href="<?php echo BASE_URL; ?>views/admin/category.php"
                class="admin-btn admin-btn-secondary"
            >
                Cancel
            </a>
        </div>

    </form>

</section>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

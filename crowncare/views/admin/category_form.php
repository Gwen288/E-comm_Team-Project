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

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1><?php echo $editCategory !== false ? 'Edit category' : 'Add category'; ?></h1>
        <p><?php echo $editCategory !== false ? 'Update the selected category.' : 'Add a new product category.'; ?></p>
    </div>
    <a class="admin-btn admin-btn-secondary" href="<?php echo BASE_URL; ?>views/admin/category.php">
        All categories
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="admin-message admin-success">
        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="admin-message admin-error">
        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<section class="admin-card">
    <?php if ($editCategory !== false): ?>
        <form action="<?php echo BASE_URL; ?>actions/update_category_action.php" method="POST" class="admin-form">
            <input type="hidden" name="cat_id" value="<?php echo (int) $editCategory['cat_id']; ?>">
            <div class="form-group">
                <label for="cat_name">Category Name</label>
                <input
                    type="text"
                    id="cat_name"
                    name="cat_name"
                    value="<?php echo htmlspecialchars($editCategory['cat_name']); ?>"
                    required
                >
            </div>
            <div class="form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Update category</button>
                <a href="<?php echo BASE_URL; ?>views/admin/category.php" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <form action="<?php echo BASE_URL; ?>actions/add_category_action.php" method="POST" class="admin-form">
            <div class="form-group">
                <label for="cat_name">Category Name</label>
                <input
                    type="text"
                    id="cat_name"
                    name="cat_name"
                    placeholder="Enter category name"
                    required
                >
            </div>
            <div class="form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Add category</button>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();
$editId = $_GET['edit_id'] ?? null;
$editBrand = false;

if ($editId !== null) {
    if (!filter_var($editId, FILTER_VALIDATE_INT) || (int) $editId <= 0) {
        $_SESSION['error'] = 'Invalid brand ID.';
        redirect(BASE_URL . 'views/admin/brand.php');
    }

    $editId = (int) $editId;
    $editBrand = $controller->getBrandById($editId);

    if ($editBrand === false) {
        $_SESSION['error'] = 'Brand not found.';
        redirect(BASE_URL . 'views/admin/brand.php');
    }
}

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1><?php echo $editBrand !== false ? 'Edit brand' : 'Add brand'; ?></h1>
        <p><?php echo $editBrand !== false ? 'Update the selected brand.' : 'Add a new product brand.'; ?></p>
    </div>
    <a class="admin-btn admin-btn-secondary" href="<?php echo BASE_URL; ?>views/admin/brand.php">
        All brands
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
    <?php if ($editBrand !== false): ?>
        <form action="<?php echo BASE_URL; ?>actions/update_brand_action.php" method="POST" class="admin-form">
            <input type="hidden" name="brand_id" value="<?php echo (int) $editBrand['brand_id']; ?>">
            <div class="form-group">
                <label for="brand_name">Brand Name</label>
                <input
                    type="text"
                    id="brand_name"
                    name="brand_name"
                    value="<?php echo htmlspecialchars($editBrand['brand_name']); ?>"
                    required
                >
            </div>
            <div class="form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Update brand</button>
                <a href="<?php echo BASE_URL; ?>views/admin/brand.php" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <form action="<?php echo BASE_URL; ?>actions/add_brand_action.php" method="POST" class="admin-form">
            <div class="form-group">
                <label for="brand_name">Brand Name</label>
                <input
                    type="text"
                    id="brand_name"
                    name="brand_name"
                    placeholder="Enter brand name"
                    required
                >
            </div>
            <div class="form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Add brand</button>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

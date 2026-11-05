<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();
$brands = $controller->getAllBrands();

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>All brands</h1>
        <p>Manage the brands currently in the system.</p>
    </div>
    <a class="admin-btn admin-btn-primary" href="<?php echo BASE_URL; ?>views/admin/brand_form.php">
        Add brand
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
    <?php if (count($brands) > 0): ?>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($brands as $brand): ?>
                        <tr>
                            <td><?php echo (int) $brand['brand_id']; ?></td>
                            <td><?php echo htmlspecialchars($brand['brand_name']); ?></td>
                            <td>
                                <div class="admin-actions admin-actions-links">
                                    <a href="<?php echo BASE_URL; ?>views/admin/brand_form.php?edit_id=<?php echo (int) $brand['brand_id']; ?>">
                                        Edit
                                    </a>
                                    <form
                                        action="<?php echo BASE_URL; ?>actions/delete_brand_action.php"
                                        method="POST"
                                        onsubmit="return confirm('Delete this brand?');"
                                    >
                                        <input type="hidden" name="brand_id" value="<?php echo (int) $brand['brand_id']; ?>">
                                        <button type="submit" class="admin-link-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="admin-empty">
            <p>No brands have been added yet.</p>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

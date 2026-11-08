
<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();
$categories = $controller->getAllCategories();

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>All categories</h1>
        <p>Manage main categories and their subcategories.</p>
    </div>

    <a
        class="admin-btn admin-btn-primary"
        href="<?php echo BASE_URL; ?>views/admin/category_form.php"
    >
        Add category
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

    <?php if (count($categories) > 0): ?>

        <div class="admin-table-container">
            <table class="admin-table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Category Type</th>
                        <th>Parent Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($categories as $category): ?>

                        <?php
                        $isSubcategory = !empty($category['parent_category_id']);
                        ?>

                        <tr>
                            <td>
                                <?php echo (int) $category['cat_id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($category['cat_name']); ?>
                            </td>

                            <td>
                                <?php echo $isSubcategory ? 'Subcategory' : 'Main Category'; ?>
                            </td>

                            <td>
                                <?php
                                echo $isSubcategory
                                    ? htmlspecialchars($category['parent_category_name'] ?? 'Unknown')
                                    : '—';
                                ?>
                            </td>

                            <td>
                                <div class="admin-actions admin-actions-links">

                                    <a href="<?php echo BASE_URL; ?>views/admin/category_form.php?edit_id=<?php echo (int) $category['cat_id']; ?>">
                                        Edit
                                    </a>

                                    <form
                                        action="<?php echo BASE_URL; ?>actions/delete_category_action.php"
                                        method="POST"
                                        onsubmit="return confirm('Delete this category?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="cat_id"
                                            value="<?php echo (int) $category['cat_id']; ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="admin-link-delete"
                                        >
                                            Delete
                                        </button>
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
            <p>No categories have been added yet.</p>
        </div>

    <?php endif; ?>

</section>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

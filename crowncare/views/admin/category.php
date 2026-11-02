<?php

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();

$editId = $_GET['edit_id'] ?? null;
$editCategory = false;

if ($editId !== null) {

    if (
        !filter_var($editId, FILTER_VALIDATE_INT) ||
        (int)$editId <= 0
    ) {
        $_SESSION['error'] = 'Invalid category ID.';
        redirect(BASE_URL . 'views/admin/category.php');
    }

    $editId = (int)$editId;

    $editCategory = $controller->getCategoryById($editId);

    if ($editCategory === false) {
        $_SESSION['error'] = 'Category not found.';
        redirect(BASE_URL . 'views/admin/category.php');
    }
}

$categories = $controller->getAllCategories();

require_once __DIR__ . '/../layout/header.php';
?>

<main class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1>Manage Categories</h1>
            <p>View, add, edit, and delete product categories.</p>
        </div>
    </div>


    <?php if (isset($_SESSION['success'])): ?>

        <div class="admin-message admin-success">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>


    <?php if (isset($_SESSION['error'])): ?>

        <div class="admin-message admin-error">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <section class="admin-card">

        <?php if ($editCategory !== false): ?>

            <div class="admin-card-header">
                <h2>Edit Category</h2>
                <p>Update the selected category.</p>
            </div>

            <form
                action="<?php echo BASE_URL; ?>actions/update_category_action.php"
                method="POST"
                class="admin-form"
            >

                <input
                    type="hidden"
                    name="cat_id"
                    value="<?php echo (int)$editCategory['cat_id']; ?>"
                >

                <div class="form-group">

                    <label for="cat_name">
                        Category Name
                    </label>

                    <input
                        type="text"
                        id="cat_name"
                        name="cat_name"
                        value="<?php echo htmlspecialchars($editCategory['cat_name']); ?>"
                        required
                    >

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                    >
                        Update Category
                    </button>

                    <a
                        href="<?php echo BASE_URL; ?>views/admin/category.php"
                        class="admin-btn admin-btn-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        <?php else: ?>

            <div class="admin-card-header">
                <h2>Add Category</h2>
                <p>Add a new product category.</p>
            </div>

            <form
                action="<?php echo BASE_URL; ?>actions/add_category_action.php"
                method="POST"
                class="admin-form"
            >

                <div class="form-group">

                    <label for="cat_name">
                        Category Name
                    </label>

                    <input
                        type="text"
                        id="cat_name"
                        name="cat_name"
                        placeholder="Enter category name"
                        required
                    >

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                    >
                        Add Category
                    </button>

                </div>

            </form>

        <?php endif; ?>

    </section>


    <section class="admin-card">

        <div class="admin-card-header">
            <h2>Existing Categories</h2>
            <p>Manage the categories currently in the system.</p>
        </div>

        <?php if (count($categories) > 0): ?>

            <div class="admin-table-container">

                <table class="admin-table">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($categories as $category): ?>

                            <tr>

                                <td>
                                    <?php echo (int)$category['cat_id']; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($category['cat_name']); ?>
                                </td>

                                <td>

                                    <div class="admin-actions">

                                        <a
                                            href="<?php echo BASE_URL; ?>views/admin/category.php?edit_id=<?php echo (int)$category['cat_id']; ?>"
                                            class="admin-btn admin-btn-edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="<?php echo BASE_URL; ?>actions/delete_category_action.php"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this category?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="cat_id"
                                                value="<?php echo (int)$category['cat_id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="admin-btn admin-btn-danger"
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

</main>
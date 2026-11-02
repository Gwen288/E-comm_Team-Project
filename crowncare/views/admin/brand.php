<?php

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();

$editId = $_GET['edit_id'] ?? null;
$editBrand = false;

if ($editId !== null) {

    if (
        !filter_var($editId, FILTER_VALIDATE_INT) ||
        (int)$editId <= 0
    ) {
        $_SESSION['error'] = 'Invalid brand ID.';
        redirect(BASE_URL . 'views/admin/brand.php');
    }

    $editId = (int)$editId;

    $editBrand = $controller->getBrandById($editId);

    if ($editBrand === false) {
        $_SESSION['error'] = 'Brand not found.';
        redirect(BASE_URL . 'views/admin/brand.php');
    }
}

$brands = $controller->getAllBrands();

require_once __DIR__ . '/../layout/header.php';
?>

<main class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1>Manage Brands</h1>
            <p>View, add, edit, and delete product brands.</p>
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

        <?php if ($editBrand !== false): ?>

            <div class="admin-card-header">
                <h2>Edit Brand</h2>
                <p>Update the selected brand.</p>
            </div>

            <form
                action="<?php echo BASE_URL; ?>actions/update_brand_action.php"
                method="POST"
                class="admin-form"
            >

                <input
                    type="hidden"
                    name="brand_id"
                    value="<?php echo (int)$editBrand['brand_id']; ?>"
                >

                <div class="form-group">

                    <label for="brand_name">
                        Brand Name
                    </label>

                    <input
                        type="text"
                        id="brand_name"
                        name="brand_name"
                        value="<?php echo htmlspecialchars($editBrand['brand_name']); ?>"
                        required
                    >

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                    >
                        Update Brand
                    </button>

                    <a
                        href="<?php echo BASE_URL; ?>views/admin/brand.php"
                        class="admin-btn admin-btn-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        <?php else: ?>

            <div class="admin-card-header">
                <h2>Add Brand</h2>
                <p>Add a new product brand.</p>
            </div>

            <form
                action="<?php echo BASE_URL; ?>actions/add_brand_action.php"
                method="POST"
                class="admin-form"
            >

                <div class="form-group">

                    <label for="brand_name">
                        Brand Name
                    </label>

                    <input
                        type="text"
                        id="brand_name"
                        name="brand_name"
                        placeholder="Enter brand name"
                        required
                    >

                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                    >
                        Add Brand
                    </button>

                </div>

            </form>

        <?php endif; ?>

    </section>


    <section class="admin-card">

        <div class="admin-card-header">
            <h2>Existing Brands</h2>
            <p>Manage the brands currently in the system.</p>
        </div>

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

                                <td>
                                    <?php echo (int)$brand['brand_id']; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </td>

                                <td>

                                    <div class="admin-actions">

                                        <a
                                            href="<?php echo BASE_URL; ?>views/admin/brand.php?edit_id=<?php echo (int)$brand['brand_id']; ?>"
                                            class="admin-btn admin-btn-edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="<?php echo BASE_URL; ?>actions/delete_brand_action.php"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this brand?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="brand_id"
                                                value="<?php echo (int)$brand['brand_id']; ?>"
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
                <p>No brands have been added yet.</p>
            </div>

        <?php endif; ?>

    </section>

</main>
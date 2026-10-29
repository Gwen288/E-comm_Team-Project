
<?php

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();

$brands = $controller->getAllBrands();
$categories = $controller->getAllCategories();
$products = $controller->getAllProducts();

$editId = filter_var($_GET['edit_id'] ?? '', FILTER_VALIDATE_INT);

$isEditing = $editId !== false && $editId !== null && $editId > 0;
$editingProduct = false;

if ($isEditing) {
    $editingProduct = $controller->getProductById($editId);

    if ($editingProduct === false) {
        $_SESSION['error'] = 'Product not found.';
        redirect(BASE_URL . 'views/admin/product.php');
    }
}

require_once __DIR__ . '/../layout/header.php';
?>

<main class="admin-page">

    <div class="admin-page-header">
        <h1>Product Management</h1>
        <p>Add products and manage existing product information.</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="admin-message admin-success">
            <?php
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="admin-message admin-error">
            <?php
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <section class="admin-card">

        <div class="admin-card-header">
            <h2><?php echo $isEditing ? 'Edit Product' : 'Add Product'; ?></h2>
            <p>Complete the fields below and select a brand and category.</p>
        </div>

        <form
            class="admin-form"
            id="productForm"
            action="<?php
                echo $isEditing
                    ? BASE_URL . 'actions/update_product_action.php'
                    : BASE_URL . 'actions/add_product_action.php';
            ?>"
            method="POST"
            enctype="multipart/form-data"
        >

            <?php if ($isEditing): ?>
                <input
                    type="hidden"
                    name="product_id"
                    value="<?php echo (int)$editingProduct['product_id']; ?>"
                >
            <?php endif; ?>

            <div class="form-group">
                <label for="product_title">Product Title</label>
                <input
                    type="text"
                    id="product_title"
                    name="product_title"
                    maxlength="255"
                    required
                    value="<?php
                        echo htmlspecialchars(
                            $editingProduct['product_title'] ?? ''
                        );
                    ?>"
                >
            </div>

            <div class="form-group">
                <label for="product_price">Price</label>
                <input
                    type="number"
                    id="product_price"
                    name="product_price"
                    min="0"
                    step="0.01"
                    required
                    value="<?php
                        echo htmlspecialchars(
                            (string)($editingProduct['product_price'] ?? '')
                        );
                    ?>"
                >
            </div>

            <div class="form-group">
                <label for="product_desc">Description</label>
                <textarea
                    id="product_desc"
                    name="product_desc"
                    rows="4"
                    required
                ><?php
                    echo htmlspecialchars(
                        $editingProduct['product_desc'] ?? ''
                    );
                ?></textarea>
            </div>

            <div class="form-group">
                <label for="product_keywords">Keywords</label>
                <input
                    type="text"
                    id="product_keywords"
                    name="product_keywords"
                    required
                    placeholder="e.g. shoes, sports, footwear"
                    value="<?php
                        echo htmlspecialchars(
                            $editingProduct['product_keywords'] ?? ''
                        );
                    ?>"
                >
            </div>

            <div class="form-group">
                <label for="product_cat">Category</label>
                <select id="product_cat" name="product_cat" required>
                    <option value="">Select a category</option>

                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?php echo (int)$category['cat_id']; ?>"
                            <?php
                            echo (
                                isset($editingProduct['product_cat']) &&
                                (int)$editingProduct['product_cat']
                                    === (int)$category['cat_id']
                            ) ? 'selected' : '';
                            ?>
                        >
                            <?php echo htmlspecialchars($category['cat_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product_brand">Brand</label>
                <select id="product_brand" name="product_brand" required>
                    <option value="">Select a brand</option>

                    <?php foreach ($brands as $brand): ?>
                        <option
                            value="<?php echo (int)$brand['brand_id']; ?>"
                            <?php
                            echo (
                                isset($editingProduct['product_brand']) &&
                                (int)$editingProduct['product_brand']
                                    === (int)$brand['brand_id']
                            ) ? 'selected' : '';
                            ?>
                        >
                            <?php echo htmlspecialchars($brand['brand_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product_image">
                    Product Image
                    <?php echo $isEditing ? '(Optional when editing)' : ''; ?>
                </label>

                <?php if (
                    $isEditing &&
                    !empty($editingProduct['product_image'])
                ): ?>
                    <div class="product-image-preview">
                        <p>Current image:</p>

                        <img
                            src="<?php
                                echo BASE_URL . 'images/products/'
                                    . rawurlencode(
                                        basename(
                                            $editingProduct['product_image']
                                        )
                                    );
                            ?>"
                            alt="Current product image"
                            width="150"
                        >
                    </div>
                <?php endif; ?>

                <input
                    type="file"
                    id="product_image"
                    name="product_image"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    <?php echo $isEditing ? '' : 'required'; ?>
                >

                <small>JPEG, PNG, GIF, or WebP. Maximum size: 5 MB.</small>
                <p id="imageError" class="admin-error-text" hidden></p>
            </div>

            <div class="form-actions">
                <button
                    type="submit"
                    class="admin-btn admin-btn-primary"
                >
                    <?php echo $isEditing ? 'Update Product' : 'Add Product'; ?>
                </button>

                <?php if ($isEditing): ?>
                    <a
                        href="<?php echo BASE_URL; ?>views/admin/product.php"
                        class="admin-btn admin-btn-secondary"
                    >
                        Cancel
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </section>


    <section class="admin-card">

        <div class="admin-card-header">
            <h2>Existing Products</h2>
            <p>Products currently stored in the database.</p>
        </div>

        <?php if (empty($products)): ?>

            <p class="admin-empty">No products have been added yet.</p>

        <?php else: ?>

            <div class="admin-table-container">
                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['product_image'])): ?>
                                        <img
                                            class="admin-product-thumbnail"
                                            src="<?php
                                                echo BASE_URL . 'images/products/'
                                                    . rawurlencode(
                                                        basename(
                                                            $product['product_image']
                                                        )
                                                    );
                                            ?>"
                                            alt="<?php
                                                echo htmlspecialchars(
                                                    $product['product_title']
                                                );
                                            ?>"
                                        >
                                    <?php else: ?>
                                        No image
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $product['product_title']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $product['brand_name'] ?? 'Unknown'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $product['cat_name'] ?? 'Unknown'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo number_format(
                                        (float)$product['product_price'],
                                        2
                                    );
                                    ?>
                                </td>

                                <
<td>
    <div class="admin-actions">
        <a
            class="admin-btn admin-btn-edit"
            href="<?php echo BASE_URL; ?>views/admin/product.php?edit_id=<?php echo (int)$product['product_id']; ?>"
        >
            Edit
        </a>

        <form
            action="<?php echo BASE_URL; ?>actions/delete_product_action.php"
            method="POST"
            onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');"
        >
            <input
                type="hidden"
                name="product_id"
                value="<?php echo (int)$product['product_id']; ?>"
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

        <?php endif; ?>

    </section>

</main>

<script>
const productForm = document.getElementById('productForm');
const imageInput = document.getElementById('product_image');
const imageError = document.getElementById('imageError');

const allowedImageTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp'
];

const maxImageSize = 5 * 1024 * 1024;

function validateProductImage() {
    imageError.hidden = true;
    imageError.textContent = '';

    const file = imageInput.files[0];

    if (!file) {
        if (imageInput.required) {
            imageError.textContent = 'Please select a product image.';
            imageError.hidden = false;
            return false;
        }

        return true;
    }

    if (!allowedImageTypes.includes(file.type)) {
        imageError.textContent =
            'Choose a JPEG, PNG, GIF, or WebP image.';
        imageError.hidden = false;
        return false;
    }

    if (file.size <= 0 || file.size > maxImageSize) {
        imageError.textContent =
            'The image must be smaller than 5 MB.';
        imageError.hidden = false;
        return false;
    }

    return true;
}

imageInput.addEventListener('change', validateProductImage);

productForm.addEventListener('submit', function (event) {
    if (!validateProductImage()) {
        event.preventDefault();
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

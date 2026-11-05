<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();
$brands = $controller->getAllBrands();
$categories = $controller->getAllCategories();

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

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1><?php echo $isEditing ? 'Edit product' : 'Add product'; ?></h1>
        <p>Complete the fields below and select a brand and category.</p>
    </div>
    <a class="admin-btn admin-btn-secondary" href="<?php echo BASE_URL; ?>views/admin/product.php">
        All products
    </a>
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
            <input type="hidden" name="product_id" value="<?php echo (int) $editingProduct['product_id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="product_title">Product Title</label>
            <input
                type="text"
                id="product_title"
                name="product_title"
                maxlength="255"
                required
                value="<?php echo htmlspecialchars($editingProduct['product_title'] ?? ''); ?>"
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
                value="<?php echo htmlspecialchars((string) ($editingProduct['product_price'] ?? '')); ?>"
            >
        </div>

        <div class="form-group">
            <label for="product_desc">Description</label>
            <textarea id="product_desc" name="product_desc" rows="4" required><?php
                echo htmlspecialchars($editingProduct['product_desc'] ?? '');
            ?></textarea>
        </div>

        <div class="form-group">
            <label for="product_keywords">Keywords</label>
            <input
                type="text"
                id="product_keywords"
                name="product_keywords"
                required
                placeholder="e.g. shea, moisturizer, curl"
                value="<?php echo htmlspecialchars($editingProduct['product_keywords'] ?? ''); ?>"
            >
        </div>

        <div class="form-group">
            <label for="product_cat">Category</label>
            <select id="product_cat" name="product_cat" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?php echo (int) $category['cat_id']; ?>"
                        <?php
                        echo (
                            isset($editingProduct['product_cat']) &&
                            (int) $editingProduct['product_cat'] === (int) $category['cat_id']
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
                        value="<?php echo (int) $brand['brand_id']; ?>"
                        <?php
                        echo (
                            isset($editingProduct['product_brand']) &&
                            (int) $editingProduct['product_brand'] === (int) $brand['brand_id']
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
                <?php echo $isEditing ? '(optional when editing)' : ''; ?>
            </label>

            <?php if ($isEditing && !empty($editingProduct['product_image'])): ?>
                <div class="product-image-preview">
                    <p>Current image:</p>
                    <img
                        src="<?php echo BASE_URL . 'images/products/' . rawurlencode(basename($editingProduct['product_image'])); ?>"
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
            <button type="submit" class="admin-btn admin-btn-primary">
                <?php echo $isEditing ? 'Update product' : 'Add product'; ?>
            </button>
            <a href="<?php echo BASE_URL; ?>views/admin/product.php" class="admin-btn admin-btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</section>

<script>
const productForm = document.getElementById('productForm');
const imageInput = document.getElementById('product_image');
const imageError = document.getElementById('imageError');
const allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
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
        imageError.textContent = 'Choose a JPEG, PNG, GIF, or WebP image.';
        imageError.hidden = false;
        return false;
    }

    if (file.size <= 0 || file.size > maxImageSize) {
        imageError.textContent = 'The image must be smaller than 5 MB.';
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

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

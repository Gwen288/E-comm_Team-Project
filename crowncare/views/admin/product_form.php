
<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();
$categories = $controller->getAllCategories();
$sizes = $controller->getAllSizes();

$editId = filter_var($_GET['edit_id'] ?? '', FILTER_VALIDATE_INT);
$isEditing = $editId !== false && $editId !== null && $editId > 0;
$editingProduct = false;
$editingSizeIds = [];

if ($isEditing) {
    $editingProduct = $controller->getProductById($editId);

    if ($editingProduct === false) {
        $_SESSION['error'] = 'Product not found.';
        redirect(BASE_URL . 'views/admin/product.php');
    }

    $editingSizeIds = $controller->getProductSizeIds($editId);
}

$editingSizeIds = array_map('intval', $editingSizeIds);
$sizeDetails = $isEditing ? $controller->getSizeDetails($editId) : [];
$productStock = $isEditing ? (int) ($editingProduct['product_stock'] ?? 0) : 0;
$inventoryUrl = BASE_URL . 'views/admin/inventory.php';

$pageTitle = $isEditing ? 'Edit product' : 'Add product';
require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1><?php echo $isEditing ? 'Edit product' : 'Add product'; ?></h1>
        <p>Complete the fields below and select a category and the available sizes.</p>
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

        <!-- PRODUCT SIZES -->
        <div class="form-group">
            <label>Available Product Sizes</label>
            <?php if ($isEditing): ?>
                <small>Tick each size this product is sold in, with its price. Leave the price empty to use the main price. To add stock, use the <a href="<?php echo $inventoryUrl; ?>">Inventory</a> page. A newly ticked size starts with 0 in stock.</small>
            <?php else: ?>
                <small>Tick each size this product is sold in, with its price and the number in stock. Leave the price empty to use the main price.</small>
            <?php endif; ?>

            <?php if (!empty($sizes)): ?>
                <div class="product-size-options product-size-table">
                    <?php foreach ($sizes as $size): ?>
                        <?php
                        $sizeId = (int) $size['size_id'];
                        $isChecked = in_array($sizeId, $editingSizeIds, true);
                        $detail = $sizeDetails[$sizeId] ?? ['price' => null, 'stock' => 0];
                        $hasStockRow = isset($sizeDetails[$sizeId]);
                        ?>

                        <div class="product-size-row" data-size-row>
                            <label class="product-size-option">
                                <input
                                    type="checkbox"
                                    name="product_sizes[]"
                                    value="<?php echo $sizeId; ?>"
                                    <?php echo $isChecked ? 'checked' : ''; ?>
                                >
                                <span><?php echo e($size['size_name']); ?></span>
                            </label>
                            <label class="product-size-extra">
                                <span>Price (GH₵)</span>
                                <input type="number" name="size_price[<?php echo $sizeId; ?>]" min="0" step="0.01" value="<?php echo $detail['price'] !== null ? e(number_format((float) $detail['price'], 2, '.', '')) : ''; ?>">
                            </label>
                            <?php if ($isEditing): ?>
                                <div class="product-size-extra product-size-stock">
                                    <span>In stock</span>
                                    <strong><?php echo $hasStockRow ? (int) $detail['stock'] : 0; ?></strong>
                                </div>
                            <?php else: ?>
                                <label class="product-size-extra">
                                    <span>In stock</span>
                                    <input type="number" name="size_stock[<?php echo $sizeId; ?>]" min="0" max="100000" step="1" value="<?php echo (int) $detail['stock']; ?>">
                                </label>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="admin-error-text">
                    No sizes have been added yet. Add sizes before assigning them to products.
                </p>
            <?php endif; ?>
        </div>

        <div class="form-group" data-product-stock>
            <?php if ($isEditing): ?>
                <span class="admin-label">In stock</span>
                <p class="product-stock-readonly"><strong><?php echo $productStock; ?></strong> units. To add stock, use the <a href="<?php echo $inventoryUrl; ?>">Inventory</a> page.</p>
            <?php else: ?>
                <label for="product_stock">In stock</label>
                <input type="number" id="product_stock" name="product_stock" min="0" max="100000" step="1" value="<?php echo $productStock; ?>">
                <small>For a product without sizes. Products with sizes keep stock per size above.</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="product_image">
                Product Image
                <?php echo $isEditing ? '(optional when editing)' : ''; ?>
            </label>

            <?php $currentImageUrl = $isEditing ? cc_product_image_url($editingProduct['product_image'] ?? '') : null; ?>
            <?php if ($currentImageUrl): ?>
                <div class="product-image-preview">
                    <p>Current image:</p>
                    <img
                        src="<?php echo e($currentImageUrl); ?>"
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
            <small>JPEG, PNG, GIF, or WebP. Large photos are made smaller automatically before uploading (this server accepts up to <?php echo e(cc_upload_limit_label()); ?>).</small>
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
// The largest file this server accepts (its PHP upload limit, at most 5 MB).
const maxImageSize = <?php echo (int) cc_upload_limit(); ?>;
const maxImageLabel = <?php echo json_encode(cc_upload_limit_label()); ?>;
let imageBusy = false;

function showImageError(text) {
    imageError.textContent = text;
    imageError.hidden = !text;
}

/*
 * Phone photos are often larger than the server allows. Redraw a large photo at
 * a smaller size as a JPEG, trying smaller sizes until it fits the limit.
 */
async function shrinkImage(file) {
    if (!window.createImageBitmap || !window.DataTransfer) return null;
    const bitmap = await createImageBitmap(file);
    const steps = [[2000, 0.85], [1600, 0.82], [1400, 0.78], [1200, 0.75], [1000, 0.72]];
    for (const [side, quality] of steps) {
        const scale = Math.min(1, side / Math.max(bitmap.width, bitmap.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bitmap.width * scale);
        canvas.height = Math.round(bitmap.height * scale);
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff'; // transparent areas become white
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        const blob = await new Promise(function (done) { canvas.toBlob(done, 'image/jpeg', quality); });
        if (blob && blob.size <= maxImageSize) {
            const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            return new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
        }
    }
    return null;
}

function validateProductImage() {
    showImageError('');
    const file = imageInput.files[0];

    if (!file) {
        if (imageInput.required) {
            showImageError('Please select a product image.');
            return false;
        }
        return true;
    }
    if (!allowedImageTypes.includes(file.type)) {
        showImageError('Choose a JPEG, PNG, GIF, or WebP image.');
        return false;
    }
    if (file.size <= 0 || file.size > maxImageSize) {
        showImageError('The image must be smaller than ' + maxImageLabel + '.');
        return false;
    }
    return true;
}

imageInput.addEventListener('change', async function () {
    const file = imageInput.files[0];
    showImageError('');
    if (!file || !allowedImageTypes.includes(file.type) || file.size <= maxImageSize || file.type === 'image/gif') {
        validateProductImage();
        return;
    }
    imageBusy = true;
    showImageError('Making the photo smaller for upload...');
    try {
        const smaller = await shrinkImage(file);
        if (smaller) {
            const list = new DataTransfer();
            list.items.add(smaller);
            imageInput.files = list.files;
        }
    } catch (e) {
        // Leave the original file; validation below explains the limit.
    }
    imageBusy = false;
    validateProductImage();
});

productForm.addEventListener('submit', function (event) {
    if (imageBusy) {
        event.preventDefault();
        showImageError('Please wait, the photo is still being prepared.');
        return;
    }
    if (!validateProductImage()) {
        event.preventDefault();
    }
});
</script>

<script>
// Price and stock boxes only for ticked sizes; the single stock box only when no size is ticked.
(function () {
    var rows = document.querySelectorAll('[data-size-row]');
    var productStock = document.querySelector('[data-product-stock]');

    function update() {
        var anyChecked = false;
        rows.forEach(function (row) {
            var checked = row.querySelector('input[type="checkbox"]').checked;
            anyChecked = anyChecked || checked;
            row.classList.toggle('is-on', checked);
            row.querySelectorAll('.product-size-extra input').forEach(function (input) {
                input.disabled = !checked;
            });
        });
        if (productStock) productStock.hidden = anyChecked;
    }

    rows.forEach(function (row) {
        row.querySelector('input[type="checkbox"]').addEventListener('change', update);
    });
    update();
})();
</script>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

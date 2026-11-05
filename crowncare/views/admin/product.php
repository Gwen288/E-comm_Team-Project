<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();
$products = $controller->getAllProducts();

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>All products</h1>
        <p>Products currently stored in the catalogue.</p>
    </div>
    <a class="admin-btn admin-btn-primary" href="<?php echo BASE_URL; ?>views/admin/product_form.php">
        Add product
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
    <?php if (empty($products)): ?>
        <p class="admin-empty">No products have been added yet.</p>
        <a class="admin-btn admin-btn-primary" href="<?php echo BASE_URL; ?>views/admin/product_form.php">
            Add product
        </a>
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
                                <?php
                                $image = trim($product['product_image'] ?? '');
                                $imagePath = __DIR__ . '/../../images/products/' . basename($image);
                                ?>
                                <?php if ($image !== '' && is_file($imagePath)): ?>
                                    <img
                                        class="admin-product-thumbnail"
                                        src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($image)); ?>"
                                        alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                    >
                                <?php else: ?>
                                    <span class="admin-thumb-empty">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['product_title']); ?></td>
                            <td><?php echo htmlspecialchars($product['brand_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($product['cat_name'] ?? 'Unknown'); ?></td>
                            <td>GH₵<?php echo number_format((float) $product['product_price'], 2); ?></td>
                            <td>
                                <div class="admin-actions admin-actions-links">
                                    <a href="<?php echo BASE_URL; ?>views/admin/product_form.php?edit_id=<?php echo (int) $product['product_id']; ?>">
                                        Edit
                                    </a>
                                    <form
                                        action="<?php echo BASE_URL; ?>actions/delete_product_action.php"
                                        method="POST"
                                        onsubmit="return confirm('Delete this product? This cannot be undone.');"
                                    >
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>">
                                        <button type="submit" class="admin-link-delete">Delete</button>
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

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

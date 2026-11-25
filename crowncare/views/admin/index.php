<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../controllers/OrderController.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';
require_once __DIR__ . '/../../classes/StockClass.php';
require_once __DIR__ . '/../../classes/ConsultationClass.php';

require_admin();

$productController = new ProductController();
$orderController = new OrderController();
$customerController = new CustomerController();

$products = $productController->getAllProducts();
$categories = $productController->getAllCategories();

$productCount = count($products);
$categoryCount = count($categories);
$orderCount = $orderController->getOrderCount();
$paidOrderCount = $orderController->getPaidOrderCount();
$customerCount = $customerController->getCustomerCount();
$productSales = $orderController->getPaidRevenue();
$consultationIncome = (new ConsultationClass())->totalIncome();

$recentProducts = array_slice($products, 0, 6);
$recentOrders = array_slice($orderController->getAllOrders(), 0, 5);
$lowStock = (new StockClass())->getLowStock();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Catalogue, customers, and purchases at a glance.</p>
    </div>
</div>

<div class="admin-stat-grid">
    <a class="admin-stat-card" href="<?php echo BASE_URL; ?>views/admin/analytics.php">
        <span>Total sales</span>
        <strong><?php echo e(cc_money($productSales + $consultationIncome)); ?></strong>
        <small>Products <?php echo e(cc_money($productSales)); ?>, consultations <?php echo e(cc_money($consultationIncome)); ?></small>
    </a>
    <a class="admin-stat-card" href="<?php echo BASE_URL; ?>views/admin/product.php">
        <span>Products</span>
        <strong><?php echo (int) $productCount; ?></strong>
    </a>
    <a class="admin-stat-card" href="<?php echo BASE_URL; ?>views/admin/orders.php">
        <span>Orders</span>
        <strong><?php echo (int) $orderCount; ?></strong>
    </a>
    <a class="admin-stat-card" href="<?php echo BASE_URL; ?>views/admin/orders.php">
        <span>Paid orders</span>
        <strong><?php echo (int) $paidOrderCount; ?></strong>
    </a>
    <a class="admin-stat-card" href="<?php echo BASE_URL; ?>views/admin/customers.php">
        <span>Customers</span>
        <strong><?php echo (int) $customerCount; ?></strong>
    </a>
    <a class="admin-stat-card" href="<?php echo BASE_URL; ?>views/admin/category.php">
        <span>Categories</span>
        <strong><?php echo (int) $categoryCount; ?></strong>
    </a>
</div>

<?php if ($lowStock): ?>
    <section class="admin-card">
        <div class="admin-card-header">
            <h2>Low stock</h2>
            <p><?php echo count($lowStock); ?> item<?php echo count($lowStock) === 1 ? '' : 's'; ?> with <?php echo (int) CC_LOW_STOCK; ?> or fewer left.</p>
        </div>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>In stock</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($lowStock, 0, 8) as $row): ?>
                        <tr>
                            <td><?php echo e($row['product_title']); ?></td>
                            <td><?php echo e($row['size_name'] ?? ''); ?></td>
                            <td><span class="<?php echo (int) $row['stock_qty'] <= 0 ? 'cc-status cc-status--bad' : 'cc-status cc-status--wait'; ?>"><?php echo (int) $row['stock_qty'] <= 0 ? 'Out of stock' : (int) $row['stock_qty'] . ' left'; ?></span></td>
                            <td><a href="<?php echo BASE_URL; ?>views/admin/inventory.php#stock-<?php echo (int) $row['product_id']; ?>-<?php echo (int) ($row['size_id'] ?? 0); ?>">Add stock</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<section class="admin-card">
    <div class="admin-card-header">
        <h2>Recent orders</h2>
        <a href="<?php echo BASE_URL; ?>views/admin/orders.php">View all</a>
    </div>

    <?php if (empty($recentOrders)): ?>
        <p class="admin-empty">No purchases yet.</p>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <?php $paymentStatus = strtolower((string) ($order['payment_status'] ?? 'pending')); ?>
                        <tr>
                            <td><?php echo e(cc_order_number($order)); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name'] ?? ''); ?></td>
                            <td>GH₵<?php echo number_format((float) $order['total_amount'], 2); ?></td>
                            <td><span class="<?php echo cc_order_status_class($order['order_status'] ?? ''); ?>"><?php echo e(cc_order_status_label($order['order_status'] ?? '')); ?></span></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>views/admin/order_view.php?id=<?php echo (int) $order['order_id']; ?>">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="admin-card">
    <div class="admin-card-header">
        <h2>Recent products</h2>
        <p>Quick look at catalogue images and prices.</p>
    </div>

    <?php if (empty($recentProducts)): ?>
        <p class="admin-empty">No products yet. Add your first product to get started.</p>
        <a class="admin-btn admin-btn-primary" href="<?php echo BASE_URL; ?>views/admin/product_form.php">
            Add product
        </a>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentProducts as $product): ?>
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
                                        alt=""
                                    >
                                <?php else: ?>
                                    <span class="admin-thumb-empty">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['product_title']); ?></td>
                            <td><?php echo htmlspecialchars($product['cat_name'] ?? '—'); ?></td>
                            <td>GH₵<?php echo number_format((float) $product['product_price'], 2); ?></td>
                            <td>
                                <div class="admin-actions admin-actions-links">
                                    <a href="<?php echo BASE_URL; ?>views/admin/product_form.php?edit_id=<?php echo (int) $product['product_id']; ?>">
                                        Edit
                                    </a>
                                    <form
                                        action="<?php echo BASE_URL; ?>actions/delete_product_action.php"
                                        method="POST"
                                        data-confirm="Delete this product?" data-confirm-text="This cannot be undone." data-confirm-button="Delete product"
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

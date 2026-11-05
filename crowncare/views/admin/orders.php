<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

require_admin();

$controller = new OrderController();
$orders = $controller->getAllOrders();

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Orders</h1>
        <p>Purchases placed on CrownCare.</p>
    </div>
</div>

<section class="admin-card">
    <?php if (empty($orders)): ?>
        <p class="admin-empty">No orders yet. They’ll show up here after customers pay.</p>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $paymentStatus = strtolower((string) ($order['payment_status'] ?? 'pending'));
                        $badgeClass = 'admin-status-pending';
                        if ($paymentStatus === 'paid') {
                            $badgeClass = 'admin-status-paid';
                        } elseif ($paymentStatus === 'failed') {
                            $badgeClass = 'admin-status-failed';
                        }
                        ?>
                        <tr>
                            <td>#<?php echo (int) $order['invoice_no']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($order['customer_name'] ?? ''); ?></strong>
                                <br>
                                <span class="admin-muted">
                                    <?php echo htmlspecialchars($order['customer_email'] ?? ''); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($order['order_date'] ?? ''); ?></td>
                            <td>GH₵<?php echo number_format((float) $order['total_amount'], 2); ?></td>
                            <td>
                                <span class="admin-status <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($paymentStatus); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($order['order_status'] ?? ''); ?></td>
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

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

require_admin();

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$controller = new OrderController();

$order = ($orderId !== false && $orderId !== null && $orderId > 0)
    ? $controller->getOrderWithCustomer($orderId)
    : false;

$items = $order ? $controller->getOrderItems((int) $order['order_id']) : [];

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>
            <?php
            echo $order
                ? 'Order #' . (int) $order['invoice_no']
                : 'Order not found';
            ?>
        </h1>
        <p>Purchase detail for fulfilment and support.</p>
    </div>
    <a class="admin-btn" href="<?php echo BASE_URL; ?>views/admin/orders.php">
        Back to orders
    </a>
</div>

<?php if ($order === false): ?>
    <section class="admin-card">
        <p class="admin-empty">This order does not exist.</p>
    </section>
<?php else: ?>
    <?php
    $paymentStatus = strtolower((string) ($order['payment_status'] ?? 'pending'));
    $badgeClass = 'admin-status-pending';
    if ($paymentStatus === 'paid') {
        $badgeClass = 'admin-status-paid';
    } elseif ($paymentStatus === 'failed') {
        $badgeClass = 'admin-status-failed';
    }
    ?>

    <div class="admin-detail-grid">
        <section class="admin-card">
            <div class="admin-card-header">
                <h2>Summary</h2>
            </div>

            <dl class="admin-dl">
                <div>
                    <dt>Payment</dt>
                    <dd>
                        <span class="admin-status <?php echo $badgeClass; ?>">
                            <?php echo htmlspecialchars($paymentStatus); ?>
                        </span>
                    </dd>
                </div>
                <div>
                    <dt>Order status</dt>
                    <dd><?php echo htmlspecialchars($order['order_status'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt>Date</dt>
                    <dd><?php echo htmlspecialchars($order['order_date'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt>Reference</dt>
                    <dd><?php echo htmlspecialchars($order['payment_reference'] ?: '—'); ?></dd>
                </div>
                <div>
                    <dt>Subtotal</dt>
                    <dd>GH₵<?php echo number_format((float) $order['subtotal'], 2); ?></dd>
                </div>
                <div>
                    <dt>Delivery</dt>
                    <dd>GH₵<?php echo number_format((float) $order['delivery_fee'], 2); ?></dd>
                </div>
                <div>
                    <dt>Total</dt>
                    <dd><strong>GH₵<?php echo number_format((float) $order['total_amount'], 2); ?></strong></dd>
                </div>
            </dl>
        </section>

        <section class="admin-card">
            <div class="admin-card-header">
                <h2>Customer & delivery</h2>
            </div>

            <dl class="admin-dl">
                <div>
                    <dt>Name</dt>
                    <dd><?php echo htmlspecialchars($order['customer_name'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd><?php echo htmlspecialchars($order['customer_email'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt>Phone (account)</dt>
                    <dd><?php echo htmlspecialchars($order['customer_contact'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt>Deliver to</dt>
                    <dd><?php echo htmlspecialchars($order['delivery_name'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt>Delivery phone</dt>
                    <dd><?php echo htmlspecialchars($order['delivery_phone'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt>City</dt>
                    <dd><?php echo htmlspecialchars($order['delivery_city'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt>Address</dt>
                    <dd><?php echo nl2br(htmlspecialchars($order['delivery_address'] ?? '')); ?></dd>
                </div>
                <div>
                    <dt>Notes</dt>
                    <dd><?php echo htmlspecialchars($order['delivery_notes'] ?: '—'); ?></dd>
                </div>
            </dl>
        </section>
    </div>

    <section class="admin-card">
        <div class="admin-card-header">
            <h2>Items</h2>
        </div>

        <?php if (empty($items)): ?>
            <p class="admin-empty">No line items on this order.</p>
        <?php else: ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit price</th>
                            <th>Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $lineTotal = (int) $item['qty'] * (float) $item['product_price'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_title']); ?></td>
                                <td><?php echo (int) $item['qty']; ?></td>
                                <td>GH₵<?php echo number_format((float) $item['product_price'], 2); ?></td>
                                <td>GH₵<?php echo number_format($lineTotal, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

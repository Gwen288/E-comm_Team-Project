<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/OrderController.php';

$orderId = (int) ($_GET['order_id'] ?? 0);

$orderController = new OrderController();

// The customer's own order, or a guest order placed in this browser session.
$order = $orderController->getOrderForViewer($orderId);
$isGuestOrder = $order && empty($order['customer_id']);

if (!$order) {
    $_SESSION['error'] = 'We could not find that order.';
    redirect(BASE_URL . 'views/cart.php');
}

if ($order['payment_status'] !== 'paid') {
    redirect(
        BASE_URL . 'views/payment_failed.php?order_id=' . $orderId
        . '&reference=' . urlencode($order['payment_reference'] ?? '')
    );
}

$orderItems = $orderController->getOrderItems($orderId);
$invoiceNumber = cc_order_number($order);

$pageTitle = 'Order confirmed';
require_once __DIR__ . '/layout/header.php';
?>

<main class="payment-result payment-success">

    <div class="cc-page-nav">
        <a class="cc-back" href="<?php echo BASE_URL; ?>index.php">Back to home</a>
        <?php if (!$isGuestOrder): ?>
            <div class="cc-page-nav-links">
                <a href="<?php echo BASE_URL; ?>views/account/order.php?id=<?php echo (int) $orderId; ?>">View order</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="payment-icon" aria-hidden="true">&#10003;</div>

    <h1>Payment confirmed</h1>

    <p class="payment-lead">
        Thank you, <?php echo htmlspecialchars($order['delivery_name']); ?>.
        Your CrownCare order has been placed. We have emailed your receipt<?php echo $isGuestOrder ? ' to ' . e($order['contact_email']) : ''; ?>, and we will email you again when it is out for delivery.
    </p>

    <?php if ($isGuestOrder): ?>
        <p class="payment-help">
            Keep your order number. To see all your orders in one place next time,
            <a href="<?php echo BASE_URL; ?>views/register.php">create an account</a>.
        </p>
    <?php endif; ?>

    <div class="payment-details">
        <p>
            <span>Order number</span>
            <strong><?php echo htmlspecialchars($invoiceNumber); ?></strong>
        </p>

        <p>
            <span>Order date</span>
            <strong><?php echo e(date('j F Y', strtotime($order['order_date']))); ?></strong>
        </p>

        <p>
            <span>Payment reference</span>
            <strong><?php echo htmlspecialchars((string) $order['payment_reference']); ?></strong>
        </p>

        <p>
            <span>Amount paid</span>
            <strong>GH₵<?php echo number_format((float) $order['total_amount'], 2); ?></strong>
        </p>
    </div>

    <div class="payment-details">
        <p>
            <span>Deliver to</span>
            <strong><?php echo htmlspecialchars($order['delivery_name']); ?></strong>
        </p>

        <p>
            <span>Phone</span>
            <strong><?php echo htmlspecialchars($order['delivery_phone']); ?></strong>
        </p>

        <p>
            <span>Address</span>
            <strong>
                <?php
                echo htmlspecialchars(
                    trim($order['delivery_address'] . ', ' . $order['delivery_city'], ', ')
                );
                ?>
            </strong>
        </p>
    </div>

    <div class="payment-items">
        <h2>Items</h2>

        <ul>
            <?php foreach ($orderItems as $item): ?>
                <li>
                    <span>
                        <?php echo htmlspecialchars($item['product_title']); ?>
                        <?php if (!empty($item['size_name'])): ?>(<?php echo htmlspecialchars(cc_size_label($item['size_name'])); ?>)<?php endif; ?>
                        &times; <?php echo (int) $item['qty']; ?>
                    </span>

                    <strong>
                        GH₵<?php
                        echo number_format(
                            (int) $item['qty'] * (float) $item['product_price'],
                            2
                        );
                        ?>
                    </strong>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="payment-actions">
        <a class="payment-btn" href="<?php echo BASE_URL; ?>views/shop.php">
            Continue shopping
        </a>

        <?php if (!$isGuestOrder): ?>
            <a class="payment-btn payment-btn-outline" href="<?php echo BASE_URL; ?>views/account/order.php?id=<?php echo (int) $orderId; ?>">
                Track your order
            </a>
        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

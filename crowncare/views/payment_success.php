<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/OrderController.php';

if (!is_logged_in()) {
    $_SESSION['error'] = 'Please log in to view your order.';
    redirect(BASE_URL . 'views/login.php');
}

$orderId = (int) ($_GET['order_id'] ?? 0);
$customerId = get_customer_id();

$orderController = new OrderController();
$order = $orderController->getOrderForCustomer($orderId, $customerId);

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
$invoiceNumber = 'CC-' . str_pad((string) $order['invoice_no'], 6, '0', STR_PAD_LEFT);

require_once __DIR__ . '/layout/header.php';
?>

<main class="payment-result payment-success">

    <div class="payment-icon" aria-hidden="true">&#10003;</div>

    <h1>Payment confirmed</h1>

    <p class="payment-lead">
        Thank you, <?php echo htmlspecialchars($order['delivery_name']); ?>.
        Your CrownCare order has been placed successfully.
    </p>

    <div class="payment-details">
        <p>
            <span>Order number</span>
            <strong><?php echo htmlspecialchars($invoiceNumber); ?></strong>
        </p>

        <p>
            <span>Order date</span>
            <strong><?php echo htmlspecialchars($order['order_date']); ?></strong>
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
                    $order['delivery_address'] . ', ' . $order['delivery_city']
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
            Continue Shopping
        </a>

        <a class="payment-btn payment-btn-outline" href="<?php echo BASE_URL; ?>views/account/my_account.php">
            My Account
        </a>
    </div>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

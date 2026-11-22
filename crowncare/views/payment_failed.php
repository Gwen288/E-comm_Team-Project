<?php
// Shown to a signed-in customer when Paystack didn't confirm the payment.

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/OrderController.php';

// Guests can't be shown someone else's order details.
if (!is_logged_in()) {
    $_SESSION['error'] = 'Please log in to view your order.';
    redirect(BASE_URL . 'views/login.php');
}

$orderId = (int) ($_GET['order_id'] ?? 0);
$reference = trim($_GET['reference'] ?? '');
$customerId = get_customer_id();

$orderController = new OrderController();
// The customer ID goes into the lookup too, so ?order_id= can't be tampered with.
$order = $orderId > 0
    ? $orderController->getOrderForCustomer($orderId, $customerId)
    : false;

// The webhook may have arrived just after the browser came back; show the
// receipt in that case instead of a failure page.
if ($order && $order['payment_status'] === 'paid') {
    redirect(BASE_URL . 'views/payment_success.php?order_id=' . $orderId);
}

// Paystack's callback doesn't always send the reference; use the saved one.
if ($reference === '' && $order) {
    $reference = (string) ($order['payment_reference'] ?? '');
}

// A failed attempt can leave a specific reason in the session.
$message = $_SESSION['error']
    ?? 'Your payment could not be confirmed. No successful charge was recorded for this order.';

unset($_SESSION['error']);

$pageTitle = 'Payment not completed';
require_once __DIR__ . '/layout/header.php';
?>

<main class="payment-result payment-failed">

    <div class="cc-page-nav">
        <a class="cc-back" href="<?php echo BASE_URL; ?>views/cart.php">Back to cart</a>
        <div class="cc-page-nav-links">
            <a href="<?php echo BASE_URL; ?>index.php">Back to home</a>
        </div>
    </div>

    <div class="payment-icon" aria-hidden="true">&#10005;</div>

    <h1>Payment not completed</h1>

    <p class="payment-lead">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <?php if ($reference !== ''): ?>
        <div class="payment-details">
            <p>
                <span>Payment reference</span>
                <strong><?php echo htmlspecialchars($reference); ?></strong>
            </p>

            <?php if ($order): ?>
                <p>
                    <span>Order number</span>
                    <?php /* Built from invoice_no, the same number cc_order_number() gives elsewhere. */ ?>
                    <strong>
                        CC-<?php
                        echo str_pad(
                            (string) $order['invoice_no'],
                            6,
                            '0',
                            STR_PAD_LEFT
                        );
                        ?>
                    </strong>
                </p>

                <p>
                    <span>Order total</span>
                    <strong>GH₵<?php echo number_format((float) $order['total_amount'], 2); ?></strong>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <p class="payment-help">
        If you were charged but see this page, contact CrownCare support with
        the payment reference above.
    </p>

    <div class="payment-actions">
        <a class="payment-btn" href="<?php echo BASE_URL; ?>views/checkout.php">
            Try Payment Again
        </a>

        <a class="payment-btn payment-btn-outline" href="<?php echo BASE_URL; ?>views/cart.php">
            Back to Cart
        </a>
    </div>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

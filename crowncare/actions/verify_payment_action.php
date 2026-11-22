<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/OrderController.php';

/*
 * Where Paystack sends the browser after checkout (reference or trxref in the URL).
 * Signed-in customers only, and the order must belong to them.
 * Success goes to views/payment_success.php, problems to views/payment_failed.php or the cart.
 */

if (!is_logged_in()) {
    $_SESSION['error'] = 'Please sign in to confirm your payment.';
    redirect(BASE_URL . 'views/login.php');
}

$reference = trim($_GET['reference'] ?? $_GET['trxref'] ?? '');

if ($reference === '') {
    $_SESSION['error'] = 'The payment reference is missing.';
    redirect(BASE_URL . 'views/cart.php');
}

$customerId = get_customer_id();

$cartController = new CartController();
$orderController = new OrderController();

$order = $orderController->getOrderByReference($reference);

if (
    !$order ||
    (int) $order['customer_id'] !== $customerId
) {
    $_SESSION['error'] = 'We could not find an order for this payment reference.';
    redirect(BASE_URL . 'views/cart.php');
}

$orderId = (int) $order['order_id'];

// A refreshed callback should not charge or record the payment twice.
if ($order['payment_status'] === 'paid') {
    redirect(BASE_URL . 'views/payment_success.php?order_id=' . $orderId);
}

// Ask Paystack directly; what the browser reports isn't enough to trust a payment.
$response = paystack_verify($reference);

if (
    empty($response['status']) ||
    empty($response['data'])
) {
    $_SESSION['error'] = 'We could not verify this payment yet. If you were charged, contact support with reference ' . $reference . '.';
    redirect(
        BASE_URL . 'views/payment_failed.php?order_id=' . $orderId
        . '&reference=' . urlencode($reference)
    );
}

$transaction = $response['data'];

$expectedAmount = paystack_amount($order['total_amount']);
$paidAmount = (int) ($transaction['amount'] ?? 0);
$currency = strtoupper((string) ($transaction['currency'] ?? ''));

// Record the failure so the order isn't left waiting for ever.
if ((string) ($transaction['status'] ?? '') !== 'success') {
    $orderController->markFailed($orderId, $reference);

    $_SESSION['error'] = 'Your payment was not successful. You can try again from your cart.';
    redirect(
        BASE_URL . 'views/payment_failed.php?order_id=' . $orderId
        . '&reference=' . urlencode($reference)
    );
}

// Compare against the order total, not just the success flag.
if ($paidAmount !== $expectedAmount || $currency !== strtoupper(PAYSTACK_CURRENCY)) {
    $orderController->markFailed($orderId, $reference);

    $_SESSION['error'] = 'The payment amount did not match your order total. Please contact support with reference ' . $reference . '.';
    redirect(
        BASE_URL . 'views/payment_failed.php?order_id=' . $orderId
        . '&reference=' . urlencode($reference)
    );
}

$paidAt = date('Y-m-d H:i:s');

if (!empty($transaction['paid_at'])) {
    $timestamp = strtotime($transaction['paid_at']);

    if ($timestamp !== false) {
        $paidAt = date('Y-m-d H:i:s', $timestamp);
    }
}

// Records the payment, takes the items out of stock, removes them from the
// cart and emails the receipt (only once, even if the webhook also arrives).
$recorded = $orderController->completePayment(
    $order,
    $reference,
    $transaction['channel'] ?? null,
    $paidAt
);

if (!$recorded) {
    $_SESSION['error'] = 'Your payment succeeded, but we could not update the order. Please contact support with reference ' . $reference . '.';
    redirect(
        BASE_URL . 'views/payment_failed.php?order_id=' . $orderId
        . '&reference=' . urlencode($reference)
    );
}

redirect(BASE_URL . 'views/payment_success.php?order_id=' . $orderId);

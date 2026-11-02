<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/OrderController.php';

if (!is_logged_in()) {
    $_SESSION['error'] = 'Please log in to confirm your payment.';
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

if ((string) ($transaction['status'] ?? '') !== 'success') {
    $orderController->markFailed($orderId, $reference);

    $_SESSION['error'] = 'Your payment was not successful. You can try again from your cart.';
    redirect(
        BASE_URL . 'views/payment_failed.php?order_id=' . $orderId
        . '&reference=' . urlencode($reference)
    );
}

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

$recorded = $orderController->markPaid(
    $orderId,
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

$cartController->clearCart($customerId);

redirect(BASE_URL . 'views/payment_success.php?order_id=' . $orderId);

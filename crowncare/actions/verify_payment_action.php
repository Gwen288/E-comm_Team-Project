<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/OrderController.php';

$reference = trim($_GET['reference'] ?? $_GET['trxref'] ?? '');

if ($reference === '') {
    $_SESSION['error'] = 'The payment reference is missing.';
    redirect(BASE_URL . 'views/cart.php');
}

$orderController = new OrderController();

$order = $orderController->getOrderByReference($reference);

if (!$order) {
    $_SESSION['error'] = 'We could not find an order for this payment reference.';
    redirect(BASE_URL . 'views/cart.php');
}

$orderId = (int) $order['order_id'];

/*
 * Checking a payment with Paystack is safe for anyone who has the reference,
 * so the payment is confirmed even if the person's session has ended.
 * Only someone allowed to see the order is shown it afterwards.
 */
$canView = (bool) $orderController->getOrderForViewer($orderId);

$finish = function ($paid) use ($orderController, $order, $orderId, $canView) {
    if ($paid && empty($order['customer_id']) && $canView) {
        $orderController->removeGuestOrderFromCart($orderId);
    }

    if ($canView) {
        redirect(BASE_URL . 'views/payment_success.php?order_id=' . $orderId);
    }

    if (!empty($order['customer_id'])) {
        $_SESSION['info'] = 'Your payment has been received. Sign in to see your order.';
        redirect(BASE_URL . 'views/login.php');
    }

    $_SESSION['success'] = 'Your payment has been received. Your receipt has been emailed to you.';
    redirect(BASE_URL . 'index.php');
};

// A refreshed callback should not charge or record the payment twice.
if ($order['payment_status'] === 'paid') {
    $finish(true);
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

$finish(true);

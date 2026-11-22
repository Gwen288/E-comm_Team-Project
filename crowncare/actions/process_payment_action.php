<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../core/validation.php';

if (!is_logged_in()) {
    $_SESSION['error'] = 'Please log in to checkout.';
    redirect(BASE_URL . 'views/login.php');
}

block_admin_purchase();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/checkout.php');
}

if (!paystack_is_configured()) {
    $_SESSION['error'] = 'Paystack test keys are not configured. Add them to core/paystack_config.php.';
    redirect(BASE_URL . 'views/checkout.php');
}

$customerId = get_customer_id();

$cartController = new CartController();
$customerController = new CustomerController();
$orderController = new OrderController();

$cartItems = $cartController->getCartItems($customerId);
$customer = $customerController->getCustomer($customerId);

if (empty($cartItems)) {
    $_SESSION['error'] = 'Your cart is empty. Add a product before checking out.';
    redirect(BASE_URL . 'views/cart.php');
}

if (!$customer) {
    cc_force_sign_out('We could not find your account. Please sign in again.');
}

$checkoutPage = BASE_URL . 'views/checkout.php';

$deliveryName = trim(preg_replace('/\s+/', ' ', (string) ($_POST['delivery_name'] ?? '')));
$deliveryPhone = (string) ($_POST['delivery_phone'] ?? '');
$deliveryAddress = trim((string) ($_POST['delivery_address'] ?? ''));
$deliveryNotes = trim((string) ($_POST['delivery_notes'] ?? ''));

$problems = array_filter([
    cc_check_name($deliveryName),
    cc_check_phone($deliveryPhone),
    $deliveryAddress === '' ? 'Enter your delivery address.' : '',
    mb_strlen($deliveryAddress) > 255 || mb_strlen($deliveryNotes) > 255 ? 'The address and notes must be 255 characters or fewer.' : '',
]);

if ($problems) {
    cc_form_error(reset($problems), [], $checkoutPage);
}

// Stock may have changed since the cart was opened.
if ($cartController->stockProblems($customerId)) {
    $_SESSION['error'] = 'Some items in your cart are no longer available in that quantity. Please update your cart.';
    redirect(BASE_URL . 'views/cart.php');
}

$subtotal = 0.0;

foreach ($cartItems as $item) {
    $subtotal += (int) $item['qty'] * (float) $item['product_price'];
}

// Delivery is arranged with a third-party delivery service, so no fee is charged here.
$deliveryFee = 0.0;
$total = $subtotal;

$orderId = $orderController->createOrder(
    $customerId,
    $cartItems,
    [
        'name' => $deliveryName,
        'phone' => cc_normalise_phone($deliveryPhone),
        'city' => '',
        'address' => $deliveryAddress,
        'notes' => $deliveryNotes
    ],
    $subtotal,
    $deliveryFee,
    $total
);

if (!$orderId) {
    cc_form_error('We could not create your order. Please try again.', [], $checkoutPage);
}

$reference = paystack_reference($orderId);

$orderController->setPaymentReference($orderId, $reference);

$response = paystack_initialize(
    $customer['customer_email'],
    paystack_amount($total),
    $reference,
    absolute_url(BASE_URL . 'actions/verify_payment_action.php'),
    [
        'order_id' => $orderId,
        'customer_id' => $customerId,
        'delivery_name' => $deliveryName,
        'delivery_phone' => cc_normalise_phone($deliveryPhone)
    ]
);

if (
    empty($response['status']) ||
    empty($response['data']['authorization_url'])
) {
    $message = $response['message'] ?? 'Paystack did not return a checkout URL.';

    $orderController->markFailed($orderId, $reference);

    cc_form_error('Payment could not be started: ' . $message, [], $checkoutPage);
}

redirect($response['data']['authorization_url']);

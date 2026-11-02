<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../controllers/OrderController.php';

if (!is_logged_in()) {
    $_SESSION['error'] = 'Please log in to checkout.';
    redirect(BASE_URL . 'views/login.php');
}

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
    $_SESSION['error'] = 'We could not find your account. Please log in again.';
    redirect(BASE_URL . 'logout.php');
}

$deliveryName = trim($_POST['delivery_name'] ?? '');
$deliveryPhone = trim($_POST['delivery_phone'] ?? '');
$deliveryCity = trim($_POST['delivery_city'] ?? '');
$deliveryAddress = trim($_POST['delivery_address'] ?? '');
$deliveryNotes = trim($_POST['delivery_notes'] ?? '');

if (
    $deliveryName === '' ||
    $deliveryPhone === '' ||
    $deliveryCity === '' ||
    $deliveryAddress === ''
) {
    $_SESSION['error'] = 'Please complete all required delivery fields.';
    redirect(BASE_URL . 'views/checkout.php');
}

$subtotal = 0.0;

foreach ($cartItems as $item) {
    $subtotal += (int) $item['qty'] * (float) $item['product_price'];
}

$deliveryFee = $subtotal >= FREE_DELIVERY_THRESHOLD
    ? 0.0
    : (float) DELIVERY_FEE;

$total = $subtotal + $deliveryFee;

$orderId = $orderController->createOrder(
    $customerId,
    $cartItems,
    [
        'name' => $deliveryName,
        'phone' => $deliveryPhone,
        'city' => $deliveryCity,
        'address' => $deliveryAddress,
        'notes' => $deliveryNotes
    ],
    $subtotal,
    $deliveryFee,
    $total
);

if (!$orderId) {
    $_SESSION['error'] = 'We could not create your order. Please try again.';
    redirect(BASE_URL . 'views/checkout.php');
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
        'delivery_phone' => $deliveryPhone,
        'delivery_city' => $deliveryCity
    ]
);

if (
    empty($response['status']) ||
    empty($response['data']['authorization_url'])
) {
    $message = $response['message'] ?? 'Paystack did not return a checkout URL.';

    $orderController->markFailed($orderId, $reference);

    $_SESSION['error'] = 'Payment could not be started: ' . $message;
    redirect(BASE_URL . 'views/checkout.php');
}

redirect($response['data']['authorization_url']);

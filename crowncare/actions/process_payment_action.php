<?php
/*
 * Checkout: creates the order and sends the customer to Paystack.
 * Works for signed-in customers and for guests. A guest's order has no
 * account (customer_id is NULL); it is remembered in the browser session so
 * the guest can see the confirmation page after paying.
 */
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../core/validation.php';

block_admin_purchase();

$checkoutPage = BASE_URL . 'views/checkout.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($checkoutPage);
}

if (!paystack_is_configured()) {
    $_SESSION['error'] = 'Paystack test keys are not configured. Add them to core/paystack_config.php.';
    redirect($checkoutPage);
}

$isSignedIn = is_logged_in();
$customerId = $isSignedIn ? get_customer_id() : 0;

$cartController = new CartController();
$orderController = new OrderController();

$cartItems = $cartController->getCartItems($customerId);

if (empty($cartItems)) {
    $_SESSION['error'] = 'Your cart is empty. Add a product before checking out.';
    redirect(BASE_URL . 'views/cart.php');
}

// Signed-in customers use their account email; guests type one in.
if ($isSignedIn) {
    $customer = (new CustomerController())->getCustomer($customerId);
    if (!$customer) {
        cc_force_sign_out('We could not find your account. Please sign in again.');
    }
    $email = strtolower($customer['customer_email']);
} else {
    $email = strtolower(trim((string) ($_POST['contact_email'] ?? '')));
}

$clean = function ($value) {
    return trim(preg_replace('/\s+/', ' ', (string) $value));
};

$firstName = $clean($_POST['first_name'] ?? '');
$lastName = $clean($_POST['last_name'] ?? '');
$deliveryPhone = (string) ($_POST['delivery_phone'] ?? '');
$deliveryAddress = trim((string) ($_POST['delivery_address'] ?? ''));
$deliveryNotes = trim((string) ($_POST['delivery_notes'] ?? ''));

$nameProblem = function ($value, $label) {
    if ($value === '') {
        return 'Enter your ' . $label . '.';
    }
    if (mb_strlen($value) > 50) {
        return 'Your ' . $label . ' must be 50 characters or fewer.';
    }
    if (!preg_match("/^[\\p{L}][\\p{L} .'\\-]*$/u", $value)) {
        return 'Use letters, spaces, hyphens and apostrophes only in your ' . $label . '.';
    }
    return '';
};

$errors = array_filter([
    'contact_email' => $isSignedIn ? '' : cc_check_email($email),
    'first_name' => $nameProblem($firstName, 'first name'),
    'last_name' => $nameProblem($lastName, 'last name'),
    'delivery_phone' => cc_check_phone($deliveryPhone),
    'delivery_address' => $deliveryAddress === '' ? 'Enter your delivery address.'
        : (mb_strlen($deliveryAddress) > 255 ? 'The address must be 255 characters or fewer.' : ''),
    'delivery_notes' => mb_strlen($deliveryNotes) > 255 ? 'The notes must be 255 characters or fewer.' : '',
]);

if ($errors) {
    cc_form_error(reset($errors), $errors, $checkoutPage);
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

$deliveryName = $firstName . ' ' . $lastName;
$phone = cc_normalise_phone($deliveryPhone);

$orderId = $orderController->createOrder(
    $isSignedIn ? $customerId : null,
    $cartItems,
    [
        'email' => $email,
        'name' => $deliveryName,
        'phone' => $phone,
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

if (!$isSignedIn) {
    $orderController->rememberGuestOrder($orderId);
}

$reference = paystack_reference($orderId);

$orderController->setPaymentReference($orderId, $reference);

$response = paystack_initialize(
    $email,
    paystack_amount($total),
    $reference,
    absolute_url(BASE_URL . 'actions/verify_payment_action.php'),
    [
        'order_id' => $orderId,
        'customer_id' => $isSignedIn ? $customerId : null,
        'guest' => !$isSignedIn,
        'delivery_name' => $deliveryName,
        'delivery_phone' => $phone
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

unset($_SESSION['old_input']);
redirect($response['data']['authorization_url']);

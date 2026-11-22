<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../controllers/CartController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/login.php');
}

$loginPage = BASE_URL . 'views/login.php';
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$pass = (string) ($_POST['pass'] ?? '');

if ($email === '' || $pass === '') {
    cc_form_error('Enter your email address and password.', [], $loginPage);
}

$controller = new CustomerController();
$ip = get_ip();

if ($controller->tooManyFailedLogins($email, $ip)) {
    cc_form_error(
        'Too many sign in attempts. Please wait 15 minutes and try again, or reset your password.',
        [],
        $loginPage
    );
}

$customer = filter_var($email, FILTER_VALIDATE_EMAIL) ? $controller->login($email, $pass) : false;

if (!$customer) {
    $controller->recordFailedLogin($email, $ip);
    // The same message whether the email or the password is wrong.
    cc_form_error('The email address or password is incorrect.', [], $loginPage);
}

$controller->clearFailedLogins($email);
cc_sign_in($customer);

if (is_customer()) {
    (new CartController())->mergeGuestCartIntoCustomer((int) $customer['customer_id']);
}

$target = cc_home_for_role();
$redirectAfterLogin = $_SESSION['redirect_after_login'] ?? '';
unset($_SESSION['redirect_after_login']);

if (is_customer() && $redirectAfterLogin !== '' && strpos($redirectAfterLogin, BASE_URL) === 0) {
    $target = $redirectAfterLogin;
}

$firstName = strtok((string) $customer['customer_name'], ' ');
cc_form_success($target, 'Welcome back, ' . $firstName . '.');

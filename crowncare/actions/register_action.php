<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/validation.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../controllers/CartController.php';

/*
 * Handles the registration form in views/register.php. Open to guests.
 * Success signs the new account in and goes to their account page, or the page
 * they were headed to; problems return to the form.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/register.php');
}

$registerPage = BASE_URL . 'views/register.php';

$name = trim(preg_replace('/\s+/', ' ', (string) ($_POST['name'] ?? '')));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$pass = (string) ($_POST['pass'] ?? '');
$passConfirm = (string) ($_POST['pass_confirm'] ?? '');
$contact = (string) ($_POST['contact'] ?? '');
$agreed = !empty($_POST['agree']);

$errors = array_filter([
    'name' => cc_check_name($name),
    'email' => cc_check_email($email),
    'pass' => cc_check_password($pass),
    'pass_confirm' => $passConfirm === '' ? 'Enter your password again.'
        : ($passConfirm !== $pass ? 'The two passwords do not match.' : ''),
    'contact' => cc_check_phone($contact),
    'agree' => $agreed ? '' : 'Please accept the terms of use and privacy policy.',
]);

if ($errors) {
    cc_form_error('Please check the highlighted fields.', $errors, $registerPage);
}

$result = (new CustomerController())->register([
    'name' => $name,
    'email' => $email,
    'pass' => $pass,
    'city' => '',
    'contact' => cc_normalise_phone($contact),
]);

if (!$result['success']) {
    cc_form_error($result['error'], [], $registerPage);
}

cc_sign_in($result['customer']);
// Carry over anything added to the cart before signing up.
(new CartController())->mergeGuestCartIntoCustomer((int) $result['customer']['customer_id']);

$target = BASE_URL . 'views/account/my_account.php';
$redirectAfterLogin = $_SESSION['redirect_after_login'] ?? '';
unset($_SESSION['redirect_after_login']);

// Only an address on our own site is safe to follow.
if ($redirectAfterLogin !== '' && strpos($redirectAfterLogin, BASE_URL) === 0) {
    $target = $redirectAfterLogin;
}

cc_form_success($target, 'Your account has been created. Welcome to CrownCare.');

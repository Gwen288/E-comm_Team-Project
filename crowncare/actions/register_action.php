<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/validation.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../classes/DeliveryClass.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/register.php');
}

$registerPage = BASE_URL . 'views/register.php';

$name = trim(preg_replace('/\s+/', ' ', (string) ($_POST['name'] ?? '')));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$pass = (string) ($_POST['pass'] ?? '');
$passConfirm = (string) ($_POST['pass_confirm'] ?? '');
$contact = (string) ($_POST['contact'] ?? '');
$areaName = trim((string) ($_POST['city'] ?? ''));
$agreed = !empty($_POST['agree']);

$area = $areaName !== '' ? (new DeliveryClass())->getActiveAreaByName($areaName) : false;

$errors = array_filter([
    'name' => cc_check_name($name),
    'email' => cc_check_email($email),
    'pass' => cc_check_password($pass),
    'pass_confirm' => $passConfirm === '' ? 'Enter your password again.'
        : ($passConfirm !== $pass ? 'The two passwords do not match.' : ''),
    'contact' => cc_check_phone($contact),
    'city' => $area ? '' : 'Choose your area in Accra.',
    'agree' => $agreed ? '' : 'Please accept the terms of use and privacy policy.',
]);

if ($errors) {
    cc_form_error('Please check the highlighted fields.', $errors, $registerPage);
}

$result = (new CustomerController())->register([
    'name' => $name,
    'email' => $email,
    'pass' => $pass,
    'city' => $area['area_name'],
    'contact' => cc_normalise_phone($contact),
]);

if (!$result['success']) {
    cc_form_error($result['error'], [], $registerPage);
}

cc_sign_in($result['customer']);
(new CartController())->mergeGuestCartIntoCustomer((int) $result['customer']['customer_id']);

$target = BASE_URL . 'views/account/my_account.php';
$redirectAfterLogin = $_SESSION['redirect_after_login'] ?? '';
unset($_SESSION['redirect_after_login']);

if ($redirectAfterLogin !== '' && strpos($redirectAfterLogin, BASE_URL) === 0) {
    $target = $redirectAfterLogin;
}

cc_form_success($target, 'Your account has been created. Welcome to CrownCare.');

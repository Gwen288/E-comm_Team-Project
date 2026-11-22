<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/validation.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../classes/DeliveryClass.php';

require_customer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/account/edit_account.php');
}

$page = BASE_URL . 'views/account/edit_account.php';
$customerId = get_customer_id();
$controller = new CustomerController();
$customer = $controller->getCustomer($customerId);

if (!$customer) {
    cc_force_sign_out('We could not find your account. Please sign in again.');
}

$name = trim(preg_replace('/\s+/', ' ', (string) ($_POST['name'] ?? '')));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$contact = (string) ($_POST['contact'] ?? '');
$areaName = trim((string) ($_POST['city'] ?? ''));
$currentPassword = (string) ($_POST['current_password'] ?? '');

$area = $areaName !== '' ? (new DeliveryClass())->getActiveAreaByName($areaName) : false;
$emailChanged = $email !== strtolower($customer['customer_email']);

$errors = array_filter([
    'name' => cc_check_name($name),
    'email' => cc_check_email($email),
    'contact' => cc_check_phone($contact),
    'city' => $area ? '' : 'Choose your area in Accra.',
]);

if (!$errors && $emailChanged) {
    if ($currentPassword === '' || !$controller->passwordMatches($customerId, $currentPassword)) {
        $errors['current_password'] = 'Enter your current password to change your email address.';
    } elseif ($controller->emailTaken($email, $customerId)) {
        $errors['email'] = 'This email address cannot be used. Please use another one.';
    }
}

if ($errors) {
    cc_form_error('Please check the highlighted fields.', $errors, $page);
}

$saved = $controller->updateAccount($customerId, [
    'name' => $name,
    'email' => $email,
    'city' => $area['area_name'],
    'contact' => cc_normalise_phone($contact),
]);

if (!$saved) {
    cc_form_error('We could not save your changes. Please try again.', [], $page);
}

$_SESSION['customer_name'] = $name;
$_SESSION['customer_email'] = $email;

cc_form_success(BASE_URL . 'views/account/my_account.php', 'Your details have been saved.');

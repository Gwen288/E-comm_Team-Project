<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/validation.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

/*
 * Handles the Change Password form in views/account/change_pass.php. Customers only.
 * Problems return to that form with the fields highlighted; success goes to views/account/my_account.php.
 */

require_customer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/account/change_pass.php');
}

$page = BASE_URL . 'views/account/change_pass.php';
$customerId = get_customer_id();
$controller = new CustomerController();

$current = (string) ($_POST['current_password'] ?? '');
$new = (string) ($_POST['new_password'] ?? '');
$confirm = (string) ($_POST['confirm_password'] ?? '');

$errors = array_filter([
    'current_password' => $current === '' ? 'Enter your current password.' : '',
    'new_password' => cc_check_password($new),
    'confirm_password' => $confirm !== $new ? 'The two passwords do not match.' : '',
]);

if (!$errors && !$controller->passwordMatches($customerId, $current)) {
    $errors['current_password'] = 'Your current password is incorrect.';
}
// Don't let someone "change" to the password they already use.
if (!$errors && hash_equals($current, $new)) {
    $errors['new_password'] = 'Choose a password that is different from your current one.';
}

if ($errors) {
    cc_form_error('Please check the highlighted fields.', $errors, $page);
}

if (!$controller->setPassword($customerId, $new)) {
    cc_form_error('We could not change your password. Please try again.', [], $page);
}

// A new session ID, so an old one can't still be used after the change.
session_regenerate_id(true);

cc_form_success(BASE_URL . 'views/account/my_account.php', 'Your password has been changed.');

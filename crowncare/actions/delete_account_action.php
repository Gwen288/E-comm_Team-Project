<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

/*
 * Closes an account from views/account/delete_account.php. Customers only.
 * The password is checked again, and an order still on the way blocks closure.
 * On success the session is cleared and the person lands on the home page.
 */

require_customer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/account/delete_account.php');
}

$page = BASE_URL . 'views/account/delete_account.php';
$customerId = get_customer_id();
$controller = new CustomerController();
$password = (string) ($_POST['current_password'] ?? '');

if ($password === '' || !$controller->passwordMatches($customerId, $password)) {
    cc_form_error('', ['current_password' => 'Your password is incorrect.'], $page);
}

// Don't let an account disappear while its order is still being delivered.
if ($controller->countOrdersInProgress($customerId) > 0) {
    cc_form_error('You have an order on the way. You can close your account once it has been delivered.', [], $page);
}

if (!$controller->closeAccount($customerId)) {
    cc_form_error('We could not close your account. Please try again or contact us.', [], $page);
}

// Clear the session so the closed account can't be used any more.
$_SESSION = [];
session_regenerate_id(true);

cc_form_success(BASE_URL . 'index.php', 'Your account has been closed.');

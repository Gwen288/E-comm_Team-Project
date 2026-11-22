<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

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

if ($controller->countOrdersInProgress($customerId) > 0) {
    cc_form_error('You have an order on the way. You can close your account once it has been delivered.', [], $page);
}

if (!$controller->closeAccount($customerId)) {
    cc_form_error('We could not close your account. Please try again or contact us.', [], $page);
}

$_SESSION = [];
session_regenerate_id(true);

cc_form_success(BASE_URL . 'index.php', 'Your account has been closed.');

<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/validation.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/forgot_password.php');
}

$token = (string) ($_POST['token'] ?? '');
$pass = (string) ($_POST['pass'] ?? '');
$passConfirm = (string) ($_POST['pass_confirm'] ?? '');
$backUrl = BASE_URL . 'views/reset_password.php?token=' . urlencode($token);

$controller = new CustomerController();
$reset = $controller->findPasswordReset($token);

if (!$reset) {
    cc_form_error('This link has expired. Ask for a new link to reset your password.', [], BASE_URL . 'views/forgot_password.php');
}

$errors = array_filter([
    'pass' => cc_check_password($pass),
    'pass_confirm' => $passConfirm !== $pass ? 'The two passwords do not match.' : '',
]);

if ($errors) {
    cc_form_error('Please check the highlighted fields.', $errors, $backUrl);
}

if (!$controller->usePasswordReset((int) $reset['reset_id'], (int) $reset['customer_id'], $pass)) {
    cc_form_error('We could not save your new password. Please try again.', [], $backUrl);
}

cc_form_success(BASE_URL . 'views/login.php', 'Your password has been changed. You can now sign in.');

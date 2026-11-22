<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/email_layout.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/forgot_password.php');
}

$email = strtolower(trim((string) ($_POST['email'] ?? '')));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    cc_form_error('Enter a valid email address.', [], BASE_URL . 'views/forgot_password.php');
}

// The same reply whether or not the email has an account.
$reply = 'If an account uses that email address, we have sent it a link to reset the password. The link works for one hour.';

// At most one email every two minutes from the same browser.
$lastSent = $_SESSION['reset_sent_at'] ?? 0;
if (time() - $lastSent < 120) {
    cc_form_success(BASE_URL . 'views/login.php', $reply);
}
$_SESSION['reset_sent_at'] = time();

$controller = new CustomerController();
$customer = $controller->getCustomerByEmail($email);

if ($customer && ($customer['customer_status'] ?? 'active') === 'active') {
    $token = $controller->createPasswordReset((int) $customer['customer_id']);
    $link = absolute_url(BASE_URL . 'views/reset_password.php?token=' . $token);
    $firstName = strtok($customer['customer_name'], ' ');

    cc_email_send($customer['customer_email'], $customer['customer_name'], 'Reset your CrownCare password', [
        'label' => 'Password reset',
        'title' => 'Choose a new password',
        'intro' => 'Hello ' . $firstName . ', we received a request to reset the password for your CrownCare account. Use the button below within one hour.',
        'button' => ['Choose a new password', $link],
        'notes' => ['If you did not ask for this, you can ignore this email. Your password will not change.'],
    ]);
}

cc_form_success(BASE_URL . 'views/login.php', $reply);

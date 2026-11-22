<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/email_layout.php';

/*
 * Handles the contact form on views/pages/contact.php. Open to everyone.
 * Problems reload that form with the fields kept; success shows a thank-you there.
 */

$page = BASE_URL . 'views/pages/contact.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($page);
}

$name = trim(preg_replace('/\s+/', ' ', (string) ($_POST['name'] ?? '')));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$subject = (string) ($_POST['subject'] ?? '');
$message = trim((string) ($_POST['message'] ?? ''));

$errors = array_filter([
    'name' => $name === '' || mb_strlen($name) > 100 ? 'Enter your name.' : '',
    'email' => !filter_var($email, FILTER_VALIDATE_EMAIL) ? 'Enter a valid email address.' : '',
    'message' => mb_strlen($message) < 10 ? 'Write a message of at least 10 characters.'
        : (mb_strlen($message) > 2000 ? 'Keep your message under 2,000 characters.' : ''),
]);

// A message with no recognised subject is still delivered, just filed under "Something else".
if (!in_array($subject, ['A product', 'My order', 'A consultation', 'Something else'], true)) {
    $subject = 'Something else';
}

if ($errors) {
    cc_form_error('Please check the highlighted fields.', $errors, $page);
}

// At most one message a minute from the same browser.
if (time() - ($_SESSION['contact_sent_at'] ?? 0) < 60) {
    cc_form_error('Please wait a minute before sending another message.', [], $page);
}

// Messages go to the shop's own inbox (the reply-to address in mail_config.php).
$inbox = defined('MAIL_REPLY_TO') && MAIL_REPLY_TO !== '' ? MAIL_REPLY_TO : CC_CONTACT_EMAIL;

$sent = cc_email_send($inbox, 'CrownCare', 'Website message: ' . $subject . ' (from ' . $name . ')', [
    'label' => 'Website message',
    'title' => $subject,
    'intro' => $message,
    'details' => [
        'From' => $name,
        'Email' => $email,
        'Signed in' => is_logged_in() ? 'Yes' : 'No',
        'Sent' => date('j F Y, g:i a'),
    ],
    'notes' => ['Reply to ' . $email . ' to answer this message.'],
]);

if (!$sent) {
    cc_form_error('We could not send your message. Please email us at ' . CC_CONTACT_EMAIL . ' instead.', [], $page);
}

$_SESSION['contact_sent_at'] = time();

cc_form_success($page, 'Thank you. Your message has been sent and we will reply within one working day.');

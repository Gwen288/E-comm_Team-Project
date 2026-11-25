<?php
/*
 * Cancels a consultation for the client and sends the refund the policy
 * allows (see ConsultationPayment). Allowed with the email link's token, for
 * a booking made in this browser session, or for the signed-in owner.
 */
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';
require_once __DIR__ . '/../classes/ConsultationPayment.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php');
}

$reference = strtoupper(trim((string) ($_POST['ref'] ?? '')));
$token = trim((string) ($_POST['token'] ?? ''));
$page = BASE_URL . 'views/cancel_booking.php?ref=' . rawurlencode($reference) . ($token !== '' ? '&token=' . rawurlencode($token) : '');

$booking = $reference !== '' ? (new ConsultationClass())->getForManaging($reference, $token) : false;

if (!$booking) {
    $_SESSION['error'] = 'We could not find that booking.';
    redirect($page);
}

$result = ConsultationPayment::cancel($booking, 'client');

if (!$result['ok']) {
    $_SESSION['error'] = $result['error'];
    redirect($page);
}

if ($result['refund'] > 0 && $result['refund_status'] === 'pending') {
    $_SESSION['success'] = 'Your consultation has been cancelled. ' . cc_money($result['refund']) . ' is being refunded and will reach you in 3 to 5 working days.';
} elseif ($result['refund'] > 0) {
    $_SESSION['success'] = 'Your consultation has been cancelled. Our team will send your refund of ' . cc_money($result['refund']) . ' within 3 to 5 working days.';
} else {
    $_SESSION['success'] = 'Your consultation has been cancelled.';
}

redirect(is_logged_in() && (int) $booking['customer_id'] === (int) get_customer_id()
    ? BASE_URL . 'views/account/bookings.php'
    : $page);

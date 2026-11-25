<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/validation.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';
require_once __DIR__ . '/../classes/ConsultationEmail.php';
require_once __DIR__ . '/../classes/ConsultationPayment.php';

/*
 * Saves a hair consultation booking from views/book_consultation.php and sends
 * the client to Paystack to pay. The time is held while they pay; the booking
 * is confirmed (and emailed) once the payment is verified.
 * On a problem it returns to the form with the answers kept.
 */

$formUrl = BASE_URL . 'views/book_consultation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($formUrl);
}

$clean = function ($key, $max) {
    return mb_substr(trim(strip_tags((string) ($_POST[$key] ?? ''))), 0, $max);
};

$data = [
    'service' => $clean('service', 20),
    'format' => $clean('format', 20),
    'date' => $clean('date', 10),
    'time' => $clean('time', 5),
    'full_name' => $clean('full_name', 100),
    'email' => $clean('email', 100),
    'phone' => $clean('phone', 20),
    'main_concern' => $clean('main_concern', 60),
    'notes' => $clean('notes', 500),
    'customer_id' => is_logged_in() ? get_customer_id() : null,
];

$concerns = ['Dryness', 'Breakage', 'Frizz', 'Tangles and knots', 'Itchy or flaky scalp', 'Thinning or weak edges', 'Product buildup', 'Something else'];

$consultations = new ConsultationClass();
$errors = [];

if (!hash_equals($_SESSION['booking_token'] ?? '', (string) ($_POST['token'] ?? ''))) {
    $errors[] = 'Your session expired. Please check your details and try again.';
}
if (!$consultations->getService($data['service'])) {
    $errors[] = 'Please choose a consultation type.';
}
if (!isset(ConsultationClass::FORMATS[$data['format']])) {
    $errors[] = 'Please choose in person or video call.';
}
if ($data['date'] === '' || $data['time'] === '') {
    $errors[] = 'Please choose a date and time.';
}
if (mb_strlen($data['full_name']) < 2) {
    $errors[] = 'Please enter your full name.';
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (cc_normalise_phone($data['phone']) === false) {
    $errors[] = cc_check_phone($data['phone']);
} else {
    $data['phone'] = cc_normalise_phone($data['phone']);
}
if ($data['main_concern'] !== '' && !in_array($data['main_concern'], $concerns, true)) {
    $errors[] = 'Please choose your main concern from the list.';
}

if (!$errors) {
    $result = $consultations->book($data);
    if (isset($result['error'])) {
        $errors[] = $result['error'];
    }
}

if ($errors) {
    $_SESSION['booking_errors'] = $errors;
    unset($data['customer_id']);
    $_SESSION['booking_old'] = $data;
    redirect($formUrl . '#booking-form');
}

// Only this visitor may view (and cancel) this booking without the email link.
$_SESSION['booking_references'][] = $result['reference'];

$booking = $consultations->getByReference($result['reference']);

if ($booking && $booking['status'] === 'awaiting_payment') {
    $payment = ConsultationPayment::start($booking);

    if (isset($payment['error'])) {
        $_SESSION['booking_errors'] = [$payment['error']];
        unset($data['customer_id']);
        $_SESSION['booking_old'] = $data;
        redirect($formUrl . '#booking-form');
    }

    unset($_SESSION['booking_old'], $_SESSION['booking_token']);
    redirect($payment['url']);
}

// A free consultation needs no payment: confirm and email it now.
$_SESSION['booking_emailed'][$result['reference']] = $booking ? ConsultationEmail::sendConfirmation($booking) : false;
unset($_SESSION['booking_old'], $_SESSION['booking_token']);

redirect(BASE_URL . 'views/booking_confirmation.php?ref=' . rawurlencode($result['reference']));

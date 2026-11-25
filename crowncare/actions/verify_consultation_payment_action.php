<?php
/*
 * Paystack sends the client here after paying for a consultation.
 * The payment is checked with Paystack before the booking is confirmed.
 */
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';
require_once __DIR__ . '/../classes/ConsultationPayment.php';

$formUrl = BASE_URL . 'views/book_consultation.php';
$reference = trim((string) ($_GET['reference'] ?? $_GET['trxref'] ?? ''));

$consultations = new ConsultationClass();
$booking = ConsultationPayment::isConsultationReference($reference)
    ? $consultations->getByPaymentReference($reference)
    : false;

if (!$booking) {
    $_SESSION['booking_errors'] = ['We could not find a booking for this payment.'];
    redirect($formUrl . '#booking-form');
}

$confirmation = BASE_URL . 'views/booking_confirmation.php?ref=' . rawurlencode($booking['reference']);

// Already confirmed (page refreshed, or the webhook arrived first).
if ($booking['payment_status'] !== 'unpaid') {
    redirect($confirmation);
}

$response = paystack_verify($reference);

if (empty($response['status']) || empty($response['data'])) {
    $_SESSION['booking_errors'] = ['We could not confirm your payment yet. If you were charged, contact us with reference ' . $booking['reference'] . '.'];
    redirect($formUrl . '#booking-form');
}

$result = ConsultationPayment::confirm($booking, $response['data']);

if ($result === 'paid') {
    $_SESSION['booking_emailed'][$booking['reference']] = true;
    redirect($confirmation);
}

if ($result === 'taken') {
    $_SESSION['booking_errors'] = ['Sorry, that time was booked by someone else while you were paying. We have refunded your payment in full; it will reach you in 3 to 5 working days. Please choose another time.'];
    redirect($formUrl . '#booking-form');
}

$_SESSION['booking_errors'] = ['Your payment was not completed, so the time has not been booked. Please try again.'];
redirect($formUrl . '#booking-form');

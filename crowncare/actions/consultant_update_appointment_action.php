<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/consultant_helpers.php';
require_once __DIR__ . '/../classes/ConsultantClass.php';
require_once __DIR__ . '/../classes/ConsultationEmail.php';
require_once __DIR__ . '/../classes/ConsultationPayment.php';

/* Saves the status and consultant notes from views/consultant/appointment.php. */

require_consultant();

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
$back = BASE_URL . 'views/consultant/appointment.php?id=' . (int) $id;
cc_require_consultant_post($back);

$status = (string) ($_POST['status'] ?? '');
$notes = mb_substr(trim(strip_tags((string) ($_POST['consultant_notes'] ?? ''))), 0, 2000);

$consultant = new ConsultantClass();
$appointment = $id ? $consultant->getAppointment($id) : false;

if (!$appointment) {
    $_SESSION['error'] = 'This appointment does not exist.';
    redirect(BASE_URL . 'views/consultant/appointments.php');
}

if (!isset(CC_APPOINTMENT_STATUSES[$status])) {
    $_SESSION['error'] = 'Please choose a status.';
    redirect($back);
}

$isFuture = strtotime($appointment['consultation_date'] . ' ' . $appointment['consultation_time']) > time();
if ($isFuture && in_array($status, ['completed', 'no_show'], true)) {
    $_SESSION['error'] = 'An appointment can only be marked as completed or no-show once its time has passed.';
    redirect($back);
}

// A refunded booking stays cancelled; the client books again instead.
if ($appointment['status'] === 'cancelled' && $status !== 'cancelled'
    && in_array($appointment['payment_status'], ['refunded', 'partly_refunded'], true)) {
    $_SESSION['error'] = 'This booking was cancelled and refunded, so it cannot be reopened. Ask the client to book again.';
    redirect($back);
}

// Cancelling a confirmed booking refunds the client in full and emails them.
$refundMessage = '';
if ($status === 'cancelled' && $appointment['status'] === 'booked') {
    $cancel = ConsultationPayment::cancel($appointment, 'consultant');
    if (!$cancel['ok']) {
        $_SESSION['error'] = $cancel['error'];
        redirect($back);
    }
    if ($cancel['refund'] > 0) {
        $refundMessage = $cancel['refund_status'] === 'pending'
            ? ' ' . cc_money($cancel['refund']) . ' has been refunded to the client through Paystack.'
            : ' The refund of ' . cc_money($cancel['refund']) . ' could not be sent automatically: refund it in the Paystack dashboard.';
    }
    $refundMessage .= ' The client has been emailed.';
}

$result = $consultant->updateAppointment($id, $status, $notes);

if ($result === true) {
    $_SESSION['success'] = 'Appointment ' . $appointment['reference'] . ' saved as ' . strtolower(cc_status_label($status)) . '.' . $refundMessage;
} else {
    $_SESSION['error'] = $result;
}

redirect($back);

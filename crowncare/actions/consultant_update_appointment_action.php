<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/consultant_helpers.php';
require_once __DIR__ . '/../classes/ConsultantClass.php';
require_once __DIR__ . '/../classes/ConsultationEmail.php';

/*
 * Saves the status and notes from views/consultant/appointment.php. Consultants only.
 * Bad input goes back to that appointment; a missing one lands on the appointments list.
 */

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

// Only an appointment whose time has passed can be completed or marked no-show.
$isFuture = strtotime($appointment['consultation_date'] . ' ' . $appointment['consultation_time']) > time();
if ($isFuture && in_array($status, ['completed', 'no_show'], true)) {
    $_SESSION['error'] = 'An appointment can only be marked as completed or no-show once its time has passed.';
    redirect($back);
}

$result = $consultant->updateAppointment($id, $status, $notes);

if ($result === true) {
    $_SESSION['success'] = 'Appointment ' . $appointment['reference'] . ' saved as ' . strtolower(cc_status_label($status)) . '.';

    // A consultant cancelling an upcoming booking lets the client know.
    if ($status === 'cancelled' && $appointment['status'] === 'booked') {
        $consultant->markCancelledByConsultant($id);
        if ($isFuture && ConsultationEmail::sendCancellation($appointment)) {
            $_SESSION['success'] .= ' The client has been emailed.';
        }
    }
} else {
    $_SESSION['error'] = $result;
}

redirect($back);

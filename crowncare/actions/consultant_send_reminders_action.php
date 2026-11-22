<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/consultant_helpers.php';
require_once __DIR__ . '/../classes/ConsultantClass.php';
require_once __DIR__ . '/../classes/ConsultationEmail.php';

/*
 * Sends tomorrow's reminders from the Reminders button on the consultant dashboard.
 * Consultants only, and back to views/consultant/index.php afterwards.
 * A booking is marked as reminded only once its email has been accepted.
 */

require_consultant();

$back = BASE_URL . 'views/consultant/index.php';
cc_require_consultant_post($back);

$consultant = new ConsultantClass();
$sent = 0;
$failed = 0;

foreach ($consultant->remindersDueTomorrow() as $booking) {
    if (ConsultationEmail::sendReminder($booking)) {
        $consultant->markReminded($booking['consultation_id']);
        $sent++;
    } else {
        // Leave it unmarked so the next click can try again.
        $failed++;
    }
}

if ($sent > 0) {
    $_SESSION['success'] = $sent === 1 ? 'One reminder has been sent.' : $sent . ' reminders have been sent.';
}
if ($failed > 0) {
    $_SESSION['error'] = $failed . ' reminder' . ($failed === 1 ? '' : 's') . ' could not be sent. Check the email settings.';
}
if ($sent === 0 && $failed === 0) {
    $_SESSION['info'] = 'There are no reminders to send.';
}

redirect($back);

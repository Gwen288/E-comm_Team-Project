<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';

/*
 * Cancels a consultation from views/account/bookings.php. Customers only.
 * The id comes from the session, so one client can't cancel another's booking.
 * Either way the person goes back to the bookings page.
 */

require_customer();

$page = BASE_URL . 'views/account/bookings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($page);
}

$result = (new ConsultationClass())->cancelByClient((int) ($_POST['consultation_id'] ?? 0), get_customer_id());

if ($result === true) {
    $_SESSION['success'] = 'Your consultation has been cancelled.';
} else {
    // On failure the class sends back the message to show the client.
    $_SESSION['error'] = $result;
}

redirect($page);

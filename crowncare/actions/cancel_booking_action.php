<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';

require_customer();

$page = BASE_URL . 'views/account/bookings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($page);
}

$result = (new ConsultationClass())->cancelByClient((int) ($_POST['consultation_id'] ?? 0), get_customer_id());

if ($result === true) {
    $_SESSION['success'] = 'Your consultation has been cancelled.';
} else {
    $_SESSION['error'] = $result;
}

redirect($page);

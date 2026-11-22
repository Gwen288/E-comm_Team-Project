<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/consultant_helpers.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';

/* Saves consultation prices and lengths from views/consultant/services.php. */

require_consultant();

$back = BASE_URL . 'views/consultant/services.php';
cc_require_consultant_post($back);

$consultations = new ConsultationClass();
$services = $consultations->services();
$posted = is_array($_POST['services'] ?? null) ? $_POST['services'] : [];

$updates = [];
// Validate the submitted value for every configured service before writing any changes.
foreach ($services as $key => $service) {
    $price = filter_var($posted[$key]['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $minutes = (int) ($posted[$key]['minutes'] ?? 0);

    if ($price === false || $price < 0 || $price > 10000) {
        $_SESSION['error'] = 'Please enter a price from GH₵0 to GH₵10,000 for ' . $service['name'] . '.';
        redirect($back);
    }
    if (!in_array($minutes, ConsultationClass::DURATION_OPTIONS, true)) {
        $_SESSION['error'] = 'Please choose a length for ' . $service['name'] . '.';
        redirect($back);
    }

    $updates[$key] = [round($price, 2), $minutes];
}

foreach ($updates as $key => [$price, $minutes]) {
    if (!$consultations->updateService($key, $price, $minutes)) {
        $_SESSION['error'] = 'The prices could not be saved. Please make sure migration 008 has been run.';
        redirect($back);
    }
}

$_SESSION['success'] = 'Prices saved. The booking page now shows the new prices.';
redirect($back);

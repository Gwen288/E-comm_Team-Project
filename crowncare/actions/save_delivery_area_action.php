<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/DeliveryClass.php';

require_admin();

$page = BASE_URL . 'views/admin/delivery_areas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($page);
}

$areaId = (int) ($_POST['area_id'] ?? 0);
$name = trim(preg_replace('/\s+/', ' ', (string) ($_POST['area_name'] ?? '')));
$fee = filter_var($_POST['delivery_fee'] ?? null, FILTER_VALIDATE_FLOAT);
$isActive = !empty($_POST['is_active']) ? 1 : 0;

$delivery = new DeliveryClass();

if ($name === '' || mb_strlen($name) > 30) {
    $_SESSION['error'] = 'Enter an area name of up to 30 characters.';
} elseif ($fee === false || $fee < 0 || $fee > 1000) {
    $_SESSION['error'] = 'Enter a delivery fee between 0 and 1,000.';
} elseif ($delivery->areaNameTaken($name, $areaId)) {
    $_SESSION['error'] = 'There is already an area called ' . $name . '.';
} else {
    $delivery->saveArea($areaId, $name, round($fee, 2), $isActive);
    $_SESSION['success'] = $areaId ? $name . ' has been saved.' : $name . ' has been added.';
}

redirect($page);

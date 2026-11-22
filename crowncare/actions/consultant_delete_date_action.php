<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/consultant_helpers.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';

/*
 * Removes special times set on dates in views/consultant/schedule_dates.php.
 * Consultants only. Back to the same month with the dates still selected;
 * those dates then use the usual weekly times again.
 */

require_consultant();

$page = BASE_URL . 'views/consultant/schedule_dates.php';
// Stay on the month the consultant was looking at.
$month = preg_match('/^\d{4}-\d{2}$/', (string) ($_POST['month'] ?? '')) ? $_POST['month'] : date('Y-m');
cc_require_consultant_post($page);

$dates = [];
// Only real Y-m-d dates count, and repeated dates are handled once.
foreach ((array) ($_POST['dates'] ?? []) as $date) {
    $date = (string) $date;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if ($d && $d->format('Y-m-d') === $date) {
        $dates[$date] = true;
    }
}
$dates = array_keys($dates);
sort($dates);

$back = $page . '?month=' . $month . ($dates ? '&select=' . implode(',', $dates) : '');

if (!$dates) {
    $_SESSION['error'] = 'Please choose at least one date on the calendar.';
    redirect($back);
}

$consultations = new ConsultationClass();
foreach ($dates as $date) {
    if (!$consultations->deleteSpecialDate($date)) {
        $_SESSION['error'] = 'The dates could not be changed. Please try again.';
        redirect($back);
    }
}

$what = count($dates) === 1 ? cc_date_label($dates[0], 'l j F') : count($dates) . ' dates';
$_SESSION['success'] = $what . ' now ' . (count($dates) === 1 ? 'uses' : 'use') . ' the standard times.';
redirect($back);

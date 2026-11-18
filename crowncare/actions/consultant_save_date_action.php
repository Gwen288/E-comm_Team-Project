<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/consultant_helpers.php';
require_once __DIR__ . '/../classes/ConsultantClass.php';

/* Saves the times for one or more dates chosen on the calendar in views/consultant/schedule_dates.php. */

require_consultant();

$page = BASE_URL . 'views/consultant/schedule_dates.php';
$month = preg_match('/^\d{4}-\d{2}$/', (string) ($_POST['month'] ?? '')) ? $_POST['month'] : date('Y-m');
cc_require_consultant_post($page);

$today = date('Y-m-d');
$dates = [];
foreach ((array) ($_POST['dates'] ?? []) as $date) {
    $date = (string) $date;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if ($d && $d->format('Y-m-d') === $date && $date >= $today) {
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

$closed = ($_POST['mode'] ?? 'open') === 'closed';
$times = array_values(array_intersect(ConsultationClass::TIME_OPTIONS, (array) ($_POST['times'] ?? [])));

if (!$closed && !$times) {
    $_SESSION['error'] = 'Please choose at least one time, or choose Closed all day.';
    redirect($back);
}

$note = mb_substr(trim(strip_tags((string) ($_POST['note'] ?? ''))), 0, 150);

$consultant = new ConsultantClass();
$affected = 0;

foreach ($dates as $date) {
    if (!$consultant->saveSpecialDate($date, $closed, $times, $note)) {
        $_SESSION['error'] = 'The times could not be saved. Please make sure migration 008 has been run.';
        redirect($back);
    }
    // Appointments already booked at times that are no longer offered.
    $booked = $consultant->bookedSlots($date, $date)[$date] ?? [];
    $affected += count($closed ? $booked : array_diff($booked, $times));
}

$what = count($dates) === 1 ? cc_date_label($dates[0], 'l j F') : count($dates) . ' dates';
$message = $closed ? $what . ' set to closed.' : $what . ' set to ' . implode(', ', array_map('cc_time_label', $times)) . '.';

if ($affected) {
    $message .= ' ' . $affected . ($affected === 1 ? ' appointment is' : ' appointments are') . ' already booked outside these times; please contact the ' . ($affected === 1 ? 'client' : 'clients') . '.';
}

$_SESSION['success'] = $message;
redirect($back);

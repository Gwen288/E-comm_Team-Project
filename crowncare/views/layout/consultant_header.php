<?php
/*
 * Layout for the consultant area. Matches views/layout/admin_header.php.
 * Each page sets $pageTitle before including this file.
 */
if (!is_logged_in() || !is_consultant()) {
    $_SESSION['error'] = 'You do not have permission to access the consultant area.';
    redirect(BASE_URL . 'views/login.php');
}

$consultantPage = basename($_SERVER['PHP_SELF'] ?? '');
$appointmentView = $_GET['view'] ?? 'upcoming';

$dashboardOpen = in_array($consultantPage, ['index.php', 'analytics.php'], true);
$scheduleOpen = in_array($consultantPage, ['schedule_dates.php', 'schedule_view.php'], true);
$appointmentsOpen = in_array($consultantPage, ['appointments.php', 'appointment.php'], true);

$navLink = function ($page, $label, $active) {
    return '<a class="' . ($active ? 'is-active' : '') . '" href="' . BASE_URL . 'views/consultant/' . $page . '">' . $label . '</a>';
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(($pageTitle ?? 'Consultant') . ' | CrownCare Consultant'); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Nunito+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=20261105-admin-accordion">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/consultant.css?v=20261118c">
</head>

<body class="admin-body consultant-body">

<div class="admin-shell">

    <aside class="admin-sidebar">
        <a class="admin-brand" href="<?php echo BASE_URL; ?>views/consultant/index.php">
            CrownCare
            <span>Consultant</span>
        </a>

        <nav class="admin-nav" aria-label="Consultant" data-admin-accordion>

            <div class="admin-nav-group <?php echo $dashboardOpen ? 'is-open' : ''; ?>">
                <button type="button" class="admin-nav-parent" aria-expanded="<?php echo $dashboardOpen ? 'true' : 'false'; ?>">
                    Dashboard
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <?php echo $navLink('index.php', 'Overview', $consultantPage === 'index.php'); ?>
                    <?php echo $navLink('analytics.php', 'Analytics', $consultantPage === 'analytics.php'); ?>
                </div>
            </div>

            <div class="admin-nav-group <?php echo $scheduleOpen ? 'is-open' : ''; ?>">
                <button type="button" class="admin-nav-parent" aria-expanded="<?php echo $scheduleOpen ? 'true' : 'false'; ?>">
                    Schedule
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <?php echo $navLink('schedule_dates.php', 'Date and time', $consultantPage === 'schedule_dates.php'); ?>
                    <?php echo $navLink('schedule_view.php', 'View schedule', $consultantPage === 'schedule_view.php'); ?>
                </div>
            </div>

            <div class="admin-nav-group <?php echo $appointmentsOpen ? 'is-open' : ''; ?>">
                <button type="button" class="admin-nav-parent" aria-expanded="<?php echo $appointmentsOpen ? 'true' : 'false'; ?>">
                    Appointments
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <?php echo $navLink('appointments.php?view=upcoming', 'Upcoming', $consultantPage === 'appointments.php' && $appointmentView === 'upcoming'); ?>
                    <?php echo $navLink('appointments.php?view=history', 'History', ($consultantPage === 'appointments.php' && $appointmentView !== 'upcoming') || $consultantPage === 'appointment.php'); ?>
                </div>
            </div>

            <?php echo $navLink('services.php', 'Services and prices', $consultantPage === 'services.php'); ?>

            <div class="admin-nav-group">
                <button type="button" class="admin-nav-parent" aria-expanded="false">
                    Store
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <a href="<?php echo BASE_URL; ?>index.php">View storefront</a>
                    <a href="<?php echo BASE_URL; ?>views/book_consultation.php">View booking page</a>
                    <a href="<?php echo BASE_URL; ?>logout.php">Sign out</a>
                </div>
            </div>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <strong>CrownCare</strong>
                <span>Manage consultations</span>
            </div>
            <div class="admin-topbar-user">
                Welcome, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Consultant'); ?>
            </div>
        </header>

        <div class="admin-content">

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="admin-message admin-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="admin-message admin-error"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

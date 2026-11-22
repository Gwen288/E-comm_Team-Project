<?php
/*
 * Layout for the consultant area: every page under views/consultant/
 * (dashboard, analytics, schedule, appointments and services).
 * Same structure and styles as views/layout/admin_header.php, with the
 * consultant menu. Each page sets $pageTitle before including this file.
 */
// Only consultants (user_role 3) get in; everyone else goes back to sign in.
if (!is_logged_in() || !is_consultant()) {
    $_SESSION['error'] = 'You do not have permission to access the consultant area.';
    redirect(BASE_URL . 'views/login.php');
}

$consultantPage = basename($_SERVER['PHP_SELF'] ?? '');
$appointmentView = $_GET['view'] ?? 'upcoming';

$dashboardOpen = in_array($consultantPage, ['index.php', 'analytics.php'], true);
$scheduleOpen = in_array($consultantPage, ['schedule_dates.php', 'schedule_view.php'], true);
$appointmentsOpen = in_array($consultantPage, ['appointments.php', 'appointment.php'], true);
$servicesOpen = $consultantPage === 'services.php';

// Small helper so all menu links get the active class the same way.
$navLink = function ($page, $label, $active) {
    return '<a class="' . ($active ? 'is-active' : '') . '" href="' . BASE_URL . 'views/consultant/' . $page . '">' . $label . '</a>';
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(($pageTitle ?? 'Dashboard') . ' | CrownCare Consultant'); ?></title>
    <meta name="robots" content="noindex">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Instrument+Sans:wght@400;500;600&family=Nunito+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=20261118-admin-filter-stable-actions">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/cms-site.css?v=20261118-admin-layout">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/consultant.css?v=20261122">
<?php require __DIR__ . '/head_assets.php'; ?>
</head>

<body class="admin-body consultant-body">
<?php require __DIR__ . '/flash.php'; ?>

<div class="admin-shell">

    <aside class="admin-sidebar" id="admin-sidebar">
        <button
            class="admin-sidebar-close"
            type="button"
            aria-label="Close navigation"
            data-admin-sidebar-close
        >
            &times;
        </button>

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

            <div class="admin-nav-group <?php echo $servicesOpen ? 'is-open' : ''; ?>">
                <button type="button" class="admin-nav-parent" aria-expanded="<?php echo $servicesOpen ? 'true' : 'false'; ?>">
                    Services
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <?php echo $navLink('services.php', 'Services and prices', $servicesOpen); ?>
                </div>
            </div>
        </nav>
    </aside>

    <div class="admin-sidebar-backdrop" aria-hidden="true" data-admin-sidebar-backdrop></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button
                class="admin-sidebar-toggle"
                type="button"
                aria-label="Open navigation"
                aria-controls="admin-sidebar"
                aria-expanded="false"
                data-admin-sidebar-toggle
            >
                <span class="admin-sidebar-toggle-bars" aria-hidden="true"></span>
            </button>

            <span class="admin-topbar-user">
                Welcome, <?php echo e($_SESSION['customer_name'] ?? 'Consultant'); ?>
            </span>
            <nav class="admin-topbar-actions" aria-label="Account">
                <?php $signoutClass = 'admin-topbar-link'; require __DIR__ . '/signout_form.php'; ?>
            </nav>
        </header>

        <div class="admin-content">

<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../core/consultant_helpers.php';
require_once __DIR__ . '/../../classes/ConsultantClass.php';

require_consultant();

$consultant = new ConsultantClass();

$today = date('Y-m-d');
$figures = $consultant->dashboardFigures();
$todayList = $consultant->appointmentsOn($today);
$comingUp = $consultant->nextAppointments(2);
$upcomingTotal = $consultant->countAppointments(['view' => 'upcoming']);
$needsUpdate = $consultant->needingUpdate(6);
$remindersDue = count($consultant->remindersDueTomorrow());

$base = BASE_URL . 'views/consultant/';
$pageTitle = 'Dashboard';

require_once __DIR__ . '/../layout/consultant_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Your consultations at a glance for <?php echo cc_date_label($today, 'l j F Y'); ?>.</p>
    </div>
    <a class="admin-btn admin-btn-secondary" href="<?php echo $base; ?>analytics.php">View analytics</a>
</div>

<?php if ($remindersDue > 0): ?>
    <section class="admin-card cc-reminder-card">
        <p>
            <?php echo $remindersDue === 1 ? 'One client is' : $remindersDue . ' clients are'; ?>
            booked for tomorrow and <?php echo $remindersDue === 1 ? 'has' : 'have'; ?> not had a reminder email.
        </p>
        <form method="POST" action="<?php echo BASE_URL; ?>actions/consultant_send_reminders_action.php">
            <?php echo cc_csrf_field(); ?>
            <button type="submit" class="admin-btn admin-btn-primary">Send reminders</button>
        </form>
    </section>
<?php endif; ?>

<?php if ($figures['refunds_to_check'] > 0): ?>
    <section class="admin-card consultant-alert">
        <p>
            <strong><?php echo (int) $figures['refunds_to_check']; ?> refund<?php echo $figures['refunds_to_check'] === 1 ? '' : 's'; ?> could not be sent automatically.</strong>
            Refund <?php echo $figures['refunds_to_check'] === 1 ? 'it' : 'them'; ?> in the Paystack dashboard.
            <a href="<?php echo $base; ?>appointments.php?view=history&amp;status=cancelled">See cancelled appointments</a>
        </p>
    </section>
<?php endif; ?>

<div class="admin-stat-grid consultant-stat-grid">
    <a class="admin-stat-card" href="#today">
        <span>Today</span>
        <strong><?php echo $figures['today']; ?></strong>
    </a>
    <a class="admin-stat-card" href="<?php echo $base; ?>appointments.php?view=upcoming">
        <span>Next 7 days</span>
        <strong><?php echo $figures['next_week']; ?></strong>
    </a>
    <a class="admin-stat-card" href="<?php echo $base; ?>appointments.php?view=history&amp;status=completed">
        <span>Completed this month</span>
        <strong><?php echo $figures['month_completed']; ?></strong>
    </a>
    <a class="admin-stat-card" href="<?php echo $base; ?>analytics.php">
        <span>Paid this month</span>
        <strong><?php echo e(cc_money($figures['month_revenue'])); ?></strong>
        <?php if ($figures['month_refunds'] > 0): ?>
            <small>After <?php echo e(cc_money($figures['month_refunds'])); ?> in refunds</small>
        <?php endif; ?>
    </a>
    <a class="admin-stat-card<?php echo $figures['needs_update'] ? ' consultant-stat-alert' : ''; ?>" href="#needs-update">
        <span>Awaiting an update</span>
        <strong><?php echo $figures['needs_update']; ?></strong>
    </a>
</div>

<section class="admin-card" id="today">
    <div class="admin-card-header">
        <h2>Today's appointments</h2>
        <a href="<?php echo $base; ?>schedule_dates.php?select=<?php echo $today; ?>">Change today's times</a>
    </div>

    <?php if (!$todayList): ?>
        <p class="admin-empty">No appointments today.</p>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Client</th>
                        <th>Consultation</th>
                        <th>Format</th>
                        <th>Main concern</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todayList as $row): ?>
                        <tr>
                            <td><strong><?php echo cc_time_label($row['consultation_time']); ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                                <span class="admin-muted"><?php echo htmlspecialchars($row['phone']); ?></span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['service']); ?><br>
                                <span class="admin-muted"><?php echo (int) $row['duration_minutes']; ?> minutes</span>
                            </td>
                            <td><?php echo cc_format_label($row['consultation_format']); ?></td>
                            <td><?php echo htmlspecialchars($row['main_concern'] ?: 'Not given'); ?></td>
                            <td><span class="<?php echo cc_status_class($row['status']); ?>"><?php echo cc_status_label($row['status']); ?></span></td>
                            <td><div class="admin-actions-links"><a href="<?php echo $base; ?>appointment.php?id=<?php echo (int) $row['consultation_id']; ?>">Open</a></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($needsUpdate): ?>
    <section class="admin-card" id="needs-update">
        <div class="admin-card-header">
            <h2>Awaiting an update</h2>
            <p>These appointments have passed. Mark each one as completed, no-show or cancelled.</p>
        </div>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Consultation</th>
                        <th>Reference</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($needsUpdate as $row): ?>
                        <tr>
                            <td><?php echo cc_date_label($row['consultation_date']); ?>, <?php echo cc_time_label($row['consultation_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['service']); ?></td>
                            <td><?php echo htmlspecialchars($row['reference']); ?></td>
                            <td><div class="admin-actions-links"><a href="<?php echo $base; ?>appointment.php?id=<?php echo (int) $row['consultation_id']; ?>">Update</a></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<section class="admin-card">
    <div class="admin-card-header">
        <h2>Coming up</h2>
        <p>The next two appointments after today.</p>
    </div>

    <?php if (!$comingUp): ?>
        <p class="admin-empty">No upcoming appointments yet.</p>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Client</th>
                        <th>Consultation</th>
                        <th>Format</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comingUp as $row): ?>
                        <tr>
                            <td><?php echo cc_date_label($row['consultation_date']); ?></td>
                            <td><?php echo cc_time_label($row['consultation_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['service']); ?></td>
                            <td><?php echo cc_format_label($row['consultation_format']); ?></td>
                            <td>GH₵<?php echo number_format((float) $row['price'], 2); ?></td>
                            <td><div class="admin-actions-links"><a href="<?php echo $base; ?>appointment.php?id=<?php echo (int) $row['consultation_id']; ?>">Open</a></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a class="consultant-more-link" href="<?php echo $base; ?>appointments.php?view=upcoming">
            View all <?php echo $upcomingTotal; ?> upcoming <?php echo $upcomingTotal === 1 ? 'appointment' : 'appointments'; ?>
            <span class="consultant-arrow consultant-arrow-right" aria-hidden="true"></span>
        </a>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layout/consultant_footer.php'; ?>

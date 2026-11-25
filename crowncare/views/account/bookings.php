<?php

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../classes/ConsultationClass.php';

require_customer();

$bookings = (new ConsultationClass())->forCustomer(get_customer_id());

$statusClasses = [
    'booked' => 'cc-status',
    'completed' => 'cc-status cc-status--good',
    'no_show' => 'cc-status cc-status--bad',
    'cancelled' => 'cc-status cc-status--bad',
];
$statusLabels = [
    'booked' => 'Booked',
    'completed' => 'Completed',
    'no_show' => 'Missed',
    'cancelled' => 'Cancelled',
];

$pageTitle = 'My consultations';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-account">
    <a class="cc-back" href="<?php echo BASE_URL; ?>views/account/my_account.php">Back to my account</a>

    <div class="cc-account-head">
        <div>
            <h1>My consultations</h1>
        </div>
        <a class="cc-button cc-button--small" href="<?php echo BASE_URL; ?>views/book_consultation.php">Book a consultation</a>
    </div>

    <section class="cc-panel">
        <?php if (!$bookings): ?>
            <p class="cc-empty">You have not booked a consultation while signed in.</p>
        <?php else: ?>
            <div class="cc-table-wrap">
                <table class="cc-table">
                    <thead>
                        <tr>
                            <th scope="col">Consultation</th>
                            <th scope="col">Date and time</th>
                            <th scope="col">Format</th>
                            <th scope="col">Paid</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <?php $format = ConsultationClass::FORMATS[$booking['consultation_format']]['name'] ?? $booking['consultation_format']; ?>
                            <tr>
                                <td>
                                    <?php echo e($booking['service']); ?>
                                    <br><small><?php echo e($booking['reference']); ?></small>
                                </td>
                                <td>
                                    <?php echo e(date('D j M Y', strtotime($booking['consultation_date']))); ?>
                                    <br><small><?php echo e(date('g:i a', strtotime($booking['consultation_time']))); ?></small>
                                </td>
                                <td><?php echo e($format); ?></td>
                                <td><?php echo $booking['payment_status'] === 'unpaid' ? 'At the appointment' : e(cc_money($booking['amount_paid'])); ?></td>
                                <td><span class="<?php echo $statusClasses[$booking['status']] ?? 'cc-status'; ?>"><?php echo e($statusLabels[$booking['status']] ?? $booking['status']); ?></span></td>
                                <td class="is-right">
                                    <?php if (ConsultationClass::clientCanCancel($booking)): ?>
                                        <a class="cc-button cc-button--small cc-button--outline" href="<?php echo BASE_URL; ?>views/cancel_booking.php?ref=<?php echo rawurlencode($booking['reference']); ?>">Cancel</a>
                                    <?php elseif ((float) $booking['refund_amount'] > 0): ?>
                                        <small>Refund <?php echo e(cc_money($booking['refund_amount'])); ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="cc-hint" style="margin-top:16px">
                You can cancel online any time before your consultation starts.
                Cancel <?php echo (int) ConsultationClass::FULL_REFUND_HOURS; ?> hours or more before for a full refund; with less notice we refund <?php echo 100 - ConsultationClass::LATE_CANCEL_FEE_PERCENT; ?>%.
                If you do not come, the payment is not refunded. Refunds take 3 to 5 working days.
            </p>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

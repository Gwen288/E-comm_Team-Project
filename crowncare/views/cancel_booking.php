<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';

/*
 * Cancel a consultation. Opened from the link in the booking emails (works
 * without an account), from My consultations, or from the confirmation page.
 * Shows the refund the client will get, then asks them to confirm.
 */

$reference = strtoupper(trim((string) ($_GET['ref'] ?? '')));
$token = trim((string) ($_GET['token'] ?? ''));

$booking = $reference !== '' ? (new ConsultationClass())->getForManaging($reference, $token) : false;
if ($booking && ($booking['status'] === 'awaiting_payment' || ($booking['cancelled_by'] ?? '') === 'unpaid')) {
    $booking = false;
}

$canCancel = $booking && ConsultationClass::clientCanCancel($booking);
$refund = $canCancel ? ConsultationClass::refundAmount($booking, 'client') : 0.0;
$percent = $canCancel ? ConsultationClass::refundPercent($booking, 'client') : 0;
$paidOnline = $booking && $booking['payment_status'] === 'paid';

$pageTitle = 'Cancel your consultation';
require_once __DIR__ . '/layout/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/consultation.css?v=20261118">

<main class="booking-page">
    <section class="landing-shell booking-confirm">

        <?php if (!$booking): ?>

            <p class="eyebrow">Hair consultation</p>
            <h1>Booking not found</h1>
            <p class="booking-lead">
                Please open the cancel link in your booking email, or sign in and go to My consultations.
                If you need help, contact us at
                <a href="mailto:<?php echo e(CC_CONTACT_EMAIL); ?>"><?php echo e(CC_CONTACT_EMAIL); ?></a>
                with your reference number.
            </p>

        <?php elseif (!$canCancel): ?>

            <p class="eyebrow">Hair consultation <?php echo e($booking['reference']); ?></p>
            <h1>This booking cannot be cancelled online</h1>
            <p class="booking-lead">
                <?php if ($booking['status'] === 'cancelled'): ?>
                    It has already been cancelled.
                    <?php if ((float) $booking['refund_amount'] > 0): ?>
                        A refund of <?php echo e(cc_money($booking['refund_amount'])); ?> has been sent and takes 3 to 5 working days to arrive.
                    <?php endif; ?>
                <?php elseif ($booking['status'] === 'booked'): ?>
                    Its start time has passed.
                <?php else: ?>
                    It has already taken place.
                <?php endif; ?>
                If you need help, contact us at
                <a href="mailto:<?php echo e(CC_CONTACT_EMAIL); ?>"><?php echo e(CC_CONTACT_EMAIL); ?></a>.
            </p>

        <?php else: ?>

            <p class="eyebrow">Cancel your consultation</p>
            <h1>Do you want to cancel this booking?</h1>

            <div class="booking-confirm-card">
                <div class="booking-confirm-ref">
                    <span>Reference</span>
                    <strong><?php echo e($booking['reference']); ?></strong>
                </div>
                <dl>
                    <div><dt>Consultation</dt><dd><?php echo e($booking['service']); ?></dd></div>
                    <div><dt>Date</dt><dd><?php echo e(date('l j F Y', strtotime($booking['consultation_date']))); ?></dd></div>
                    <div><dt>Time</dt><dd><?php echo e(date('g:i a', strtotime($booking['consultation_time']))); ?> (Ghana time)</dd></div>
                    <?php if ($paidOnline): ?>
                        <div><dt>Paid</dt><dd><?php echo e(cc_money($booking['amount_paid'])); ?></dd></div>
                        <div><dt>Refund</dt><dd><strong><?php echo e(cc_money($refund)); ?></strong></dd></div>
                    <?php endif; ?>
                </dl>
            </div>

            <p class="booking-lead">
                <?php if (!$paidOnline): ?>
                    This booking was not paid online, so there is nothing to refund.
                <?php elseif ($percent === 100): ?>
                    You are cancelling <?php echo (int) ConsultationClass::FULL_REFUND_HOURS; ?> hours or more before, so you get a full refund.
                    It will reach the card or mobile money account you paid with in 3 to 5 working days.
                <?php else: ?>
                    You are cancelling less than <?php echo (int) ConsultationClass::FULL_REFUND_HOURS; ?> hours before, so we keep
                    <?php echo (int) ConsultationClass::LATE_CANCEL_FEE_PERCENT; ?>% and refund <?php echo (int) $percent; ?>%.
                    It will reach the card or mobile money account you paid with in 3 to 5 working days.
                <?php endif; ?>
            </p>

            <form method="POST" action="<?php echo BASE_URL; ?>actions/cancel_booking_action.php" class="cc-cancel-booking-form">
                <?php echo cc_csrf_field(); ?>
                <input type="hidden" name="ref" value="<?php echo e($booking['reference']); ?>">
                <input type="hidden" name="token" value="<?php echo e($token); ?>">
                <button type="submit" class="hair-feature-button">Yes, cancel my booking</button>
                <a class="booking-text-link" href="<?php echo BASE_URL; ?>index.php">Keep my booking</a>
            </form>

        <?php endif; ?>

    </section>
</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

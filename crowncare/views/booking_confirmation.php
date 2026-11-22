<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';

/*
 * Shown after a consultation is booked, reached with ?ref=<booking reference>.
 * Only the visitor who made the booking (in this browser session) or the
 * logged-in customer it belongs to can see it.
 */

$reference = strtoupper(trim((string) ($_GET['ref'] ?? '')));
$booking = $reference !== '' ? (new ConsultationClass())->getByReference($reference) : false;

// A reference on its own isn't enough: it must be from this browser's booking
// or belong to the signed-in customer. Otherwise treat it as not found.
$mine = $booking && (
    in_array($reference, $_SESSION['booking_references'] ?? [], true)
    || (is_logged_in() && (int) $booking['customer_id'] === get_customer_id())
);

if (!$mine) {
    $booking = false;
}

// Format name and detail are looked up in ConsultationClass::FORMATS.
$format = $booking ? (ConsultationClass::FORMATS[$booking['consultation_format']] ?? null) : null;

$pageTitle = 'Booking confirmed';
require_once __DIR__ . '/layout/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/consultation.css?v=20261118">

<main class="booking-page">
    <section class="landing-shell booking-confirm">

        <?php if (!$booking): ?>

            <p class="eyebrow">Hair consultation</p>
            <h1>Booking not found</h1>
            <p class="booking-lead">We could not find this booking. If you booked a consultation, please contact us at
                <a href="mailto:<?php echo e(CC_CONTACT_EMAIL); ?>"><?php echo e(CC_CONTACT_EMAIL); ?></a> with your reference number.</p>
            <a class="hair-feature-button" href="<?php echo BASE_URL; ?>views/book_consultation.php">Book a consultation</a>

        <?php else: ?>

            <div class="booking-confirm-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="30" height="30"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <p class="eyebrow">Booking confirmed</p>
            <h1>Thank you, <?php echo htmlspecialchars(strtok($booking['full_name'], ' ')); ?>. Your consultation is booked.</h1>
            <p class="booking-lead">
                <?php /* The booking action records whether the email actually went out. */ ?>
                <?php if (!empty($_SESSION['booking_emailed'][$booking['reference']])): ?>
                    We have sent the booking details to <strong><?php echo htmlspecialchars($booking['email']); ?></strong>.
                    Please keep your reference number. If you cannot find the email, check your spam folder.
                <?php else: ?>
                    Please keep your reference number. We could not send a confirmation email this time, so you may want to save or screenshot this page.
                <?php endif; ?>
            </p>

            <div class="booking-confirm-card">
                <div class="booking-confirm-ref">
                    <span>Reference</span>
                    <strong><?php echo htmlspecialchars($booking['reference']); ?></strong>
                </div>
                <dl>
                    <div><dt>Consultation</dt><dd><?php echo htmlspecialchars($booking['service']); ?></dd></div>
                    <div><dt>Date</dt><dd><?php echo date('l j F Y', strtotime($booking['consultation_date'])); ?></dd></div>
                    <div><dt>Time</dt><dd><?php echo date('g:i a', strtotime($booking['consultation_time'])); ?> (Ghana time)</dd></div>
                    <div><dt>Duration</dt><dd><?php echo (int) $booking['duration_minutes']; ?> minutes</dd></div>
                    <div><dt>Format</dt><dd><?php echo htmlspecialchars($format['name'] ?? ''); ?> &middot; <?php echo htmlspecialchars($format['detail'] ?? ''); ?></dd></div>
                    <div><dt>Name</dt><dd><?php echo htmlspecialchars($booking['full_name']); ?></dd></div>
                    <div><dt>Contact</dt><dd><?php echo htmlspecialchars($booking['email']); ?> &middot; <?php echo htmlspecialchars($booking['phone']); ?></dd></div>
                    <div><dt>To pay at your consultation</dt><dd>GH₵<?php echo number_format((float) $booking['price'], 2); ?></dd></div>
                </dl>
            </div>

            <div class="booking-next">
                <h2>Before your consultation</h2>
                <ul>
                    <li>Come with clean, detangled hair without heavy styling products, so your specialist can see your natural texture and scalp.</li>
                    <li>Bring or note down the products you use now.</li>
                    <li>Need to change or cancel? Contact us at <a href="mailto:<?php echo e(CC_CONTACT_EMAIL); ?>"><?php echo e(CC_CONTACT_EMAIL); ?></a> or <a href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', CC_CONTACT_PHONE)); ?>"><?php echo e(CC_CONTACT_PHONE); ?></a> at least 24 hours before.</li>
                </ul>
            </div>

            <div class="booking-confirm-actions">
                <a class="hair-feature-button" href="<?php echo BASE_URL; ?>views/shop.php">Continue shopping</a>
                <a class="booking-text-link" href="<?php echo BASE_URL; ?>index.php">Back to home</a>
            </div>

        <?php endif; ?>

    </section>
</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

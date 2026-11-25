<?php

require_once __DIR__ . '/../core/mailer.php';
require_once __DIR__ . '/../core/email_layout.php';
require_once __DIR__ . '/ConsultationClass.php';

/*
 * Emails about hair consultations: confirmation, cancellation and reminder.
 * $booking is a row from the consultations table.
 */
class ConsultationEmail
{
    /* Contact details come from core/site_config.php. */
    private const CONTACT_EMAIL = CC_CONTACT_EMAIL;
    private const CONTACT_PHONE = CC_CONTACT_PHONE;

    public static function sendConfirmation(array $booking)
    {
        $firstName = strtok($booking['full_name'], ' ') ?: $booking['full_name'];

        return cc_email_send($booking['email'], $booking['full_name'], 'Your CrownCare consultation is booked (' . $booking['reference'] . ')', [
            'label' => 'Booking confirmed',
            'title' => 'Your consultation is booked',
            'intro' => 'Hello ' . $firstName . ', thank you for booking with CrownCare. We have received your payment. Your booking details are below; please keep your reference number.',
            'highlight' => ['Reference', $booking['reference']],
            'details' => self::details($booking),
            'button' => ['Cancel this booking', self::cancelUrl($booking)],
            'notes' => array_merge(self::tips(), [self::policy()]),
        ]);
    }

    /** Link to the cancel page for this booking (works without an account). */
    public static function cancelUrl(array $booking)
    {
        return absolute_url(BASE_URL . 'views/cancel_booking.php?ref=' . rawurlencode($booking['reference'])
            . '&token=' . rawurlencode((string) ($booking['cancel_token'] ?? '')));
    }

    /** The refund policy in one paragraph. */
    public static function policy()
    {
        return 'Cancellation and refunds: cancel ' . ConsultationClass::FULL_REFUND_HOURS . ' hours or more before your consultation for a full refund. '
            . 'With less notice, we refund ' . (100 - ConsultationClass::LATE_CANCEL_FEE_PERCENT) . '% (we keep ' . ConsultationClass::LATE_CANCEL_FEE_PERCENT . '%). '
            . 'If you do not come, the payment is not refunded. If we cancel, you get a full refund. Refunds reach your card or mobile money account in 3 to 5 working days.';
    }

    /** The booking details as label => value pairs. */
    private static function details(array $booking)
    {
        $format = ConsultationClass::FORMATS[$booking['consultation_format']] ?? ['name' => $booking['consultation_format'], 'detail' => ''];

        $details = [
            'Consultation' => $booking['service'],
            'Date' => date('l j F Y', strtotime($booking['consultation_date'])),
            'Time' => date('g:i a', strtotime($booking['consultation_time'])) . ' (Ghana time)',
            'Duration' => (int) $booking['duration_minutes'] . ' minutes',
            'Format' => $format['name'],
        ];
        if ($booking['consultation_format'] === 'video') {
            $details['Video call link'] = 'Sent to this email address before your consultation';
        } else {
            $details['Location'] = 'CrownCare studio, Greater Accra';
        }
        if (($booking['payment_status'] ?? 'unpaid') === 'unpaid') {
            $details['To pay at your consultation'] = 'GH₵' . number_format((float) $booking['price'], 2);
        } else {
            $details['Paid'] = 'GH₵' . number_format((float) $booking['amount_paid'], 2);
        }
        if ((float) ($booking['refund_amount'] ?? 0) > 0) {
            $details['Refund'] = 'GH₵' . number_format((float) $booking['refund_amount'], 2);
        }

        return $details;
    }

    private static function tips()
    {
        return [
            'Come with clean, detangled hair without heavy styling products, so your specialist can see your natural texture and scalp.',
            'Bring or note down the products you use now.',
            'Need to change the time? Cancel with the button in this email and book again, or contact us at ' . self::CONTACT_EMAIL . ' or ' . self::CONTACT_PHONE . '.',
        ];
    }

    /** Sent when a booking is cancelled, by the client or by CrownCare, with the refund. */
    public static function sendCancellation(array $booking, $cancelledBy = 'consultant')
    {
        if (($booking['email'] ?? '') === '') {
            return false;
        }
        $firstName = strtok($booking['full_name'], ' ') ?: $booking['full_name'];

        $intro = $cancelledBy === 'client'
            ? 'Hello ' . $firstName . ', your consultation below has been cancelled as you asked.'
            : 'Hello ' . $firstName . ', we are sorry, but we have had to cancel your consultation below. Please book another time that suits you, or contact us and we will help you find one.';

        $notes = [];
        $refund = (float) ($booking['refund_amount'] ?? 0);
        if ($refund > 0 && ($booking['refund_status'] ?? '') === 'pending') {
            $notes[] = 'We have refunded GH₵' . number_format($refund, 2) . ' to the card or mobile money account you paid with. It will reach you in 3 to 5 working days.';
        } elseif ($refund > 0) {
            $notes[] = 'Your refund of GH₵' . number_format($refund, 2) . ' is being processed by our team. It will reach you in 3 to 5 working days.';
        }
        if ($cancelledBy === 'client' && $refund > 0 && $refund < (float) $booking['amount_paid']) {
            $notes[] = 'As the booking was cancelled less than ' . ConsultationClass::FULL_REFUND_HOURS . ' hours before, ' . ConsultationClass::LATE_CANCEL_FEE_PERCENT . '% of the payment was kept.';
        }

        return cc_email_send($booking['email'], $booking['full_name'], 'Your CrownCare consultation has been cancelled (' . $booking['reference'] . ')', [
            'label' => 'Booking cancelled',
            'title' => 'Your consultation has been cancelled',
            'intro' => $intro,
            'highlight' => ['Reference', $booking['reference']],
            'details' => self::details($booking),
            'notes' => $notes,
            'button' => ['Book another time', absolute_url(BASE_URL . 'views/book_consultation.php')],
        ]);
    }

    /** Sent when a payment arrived after the held time had been booked by someone else. */
    public static function sendTimeLost(array $booking)
    {
        $firstName = strtok($booking['full_name'], ' ') ?: $booking['full_name'];

        return cc_email_send($booking['email'], $booking['full_name'], 'Your CrownCare booking could not be completed (' . $booking['reference'] . ')', [
            'label' => 'Booking not completed',
            'title' => 'That time is no longer available',
            'intro' => 'Hello ' . $firstName . ', your payment arrived after the time you chose had been booked by someone else. We have refunded the full amount, which will reach you in 3 to 5 working days. Please choose another time.',
            'highlight' => ['Reference', $booking['reference']],
            'button' => ['Choose another time', absolute_url(BASE_URL . 'views/book_consultation.php')],
        ]);
    }

    /** Reminder the day before a consultation. */
    public static function sendReminder(array $booking)
    {
        if (($booking['email'] ?? '') === '') {
            return false;
        }
        $firstName = strtok($booking['full_name'], ' ') ?: $booking['full_name'];

        return cc_email_send($booking['email'], $booking['full_name'], 'Reminder: your CrownCare consultation tomorrow (' . $booking['reference'] . ')', [
            'label' => 'Reminder',
            'title' => 'See you tomorrow',
            'intro' => 'Hello ' . $firstName . ', this is a reminder of your CrownCare consultation tomorrow.',
            'highlight' => ['Reference', $booking['reference']],
            'details' => self::details($booking),
            'notes' => array_merge(self::tips(), [self::policy()]),
            'button' => ['Cancel this booking', self::cancelUrl($booking)],
        ]);
    }
}

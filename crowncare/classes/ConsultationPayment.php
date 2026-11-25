<?php

require_once __DIR__ . '/ConsultationClass.php';
require_once __DIR__ . '/ConsultationEmail.php';

/*
 * Paying for consultations and refunding them through Paystack.
 *
 * Booking: the time is held (status awaiting_payment) while the client pays.
 * Paystack sends them back to actions/verify_consultation_payment_action.php,
 * and also calls the webhook, so the booking is confirmed either way.
 *
 * Refund policy (ConsultationClass::refundPercent):
 *   cancelled by the client 24 hours or more before   full refund
 *   cancelled by the client less than 24 hours before  95% (5% kept)
 *   cancelled by the consultant                         full refund
 *   no-show                                             no refund
 */
class ConsultationPayment
{
    /* Payment references for consultations start with this, orders with CC_. */
    public const REFERENCE_PREFIX = 'CB_';

    public static function isConsultationReference($reference)
    {
        return strpos((string) $reference, self::REFERENCE_PREFIX) === 0;
    }

    /**
     * Starts the Paystack payment for a held booking.
     * Returns ['url' => Paystack page] or ['error' => message].
     */
    public static function start(array $booking)
    {
        $consultations = new ConsultationClass();

        if (!paystack_is_configured()) {
            $consultations->releaseHold($booking['consultation_id']);
            return ['error' => 'Online payment is not set up yet. Please try again later.'];
        }

        $reference = self::REFERENCE_PREFIX . (int) $booking['consultation_id'] . '_' . time() . '_' . bin2hex(random_bytes(3));
        $consultations->setPaymentReference($booking['consultation_id'], $reference);

        $response = paystack_initialize(
            $booking['email'],
            paystack_amount($booking['price']),
            $reference,
            absolute_url(BASE_URL . 'actions/verify_consultation_payment_action.php'),
            [
                'type' => 'consultation',
                'booking_reference' => $booking['reference'],
                'consultation_id' => (int) $booking['consultation_id'],
            ]
        );

        if (empty($response['status']) || empty($response['data']['authorization_url'])) {
            $consultations->releaseHold($booking['consultation_id']);
            return ['error' => 'Payment could not be started: ' . ($response['message'] ?? 'please try again.')];
        }

        return ['url' => $response['data']['authorization_url']];
    }

    /**
     * Confirms a booking once Paystack reports a successful payment.
     * $transaction is Paystack's transaction data (status, amount, currency, paid_at).
     * Returns 'paid', 'taken' (the time was lost and the money refunded) or 'failed'.
     */
    public static function confirm(array $booking, array $transaction)
    {
        $consultations = new ConsultationClass();

        $paidAmount = (int) ($transaction['amount'] ?? 0);
        $currency = strtoupper((string) ($transaction['currency'] ?? ''));

        if (($transaction['status'] ?? '') !== 'success'
            || $paidAmount !== paystack_amount($booking['price'])
            || $currency !== strtoupper(PAYSTACK_CURRENCY)) {
            if (($booking['status'] ?? '') === 'awaiting_payment') {
                $consultations->releaseHold($booking['consultation_id']);
            }
            return 'failed';
        }

        $paidAt = !empty($transaction['paid_at']) && strtotime($transaction['paid_at']) !== false
            ? date('Y-m-d H:i:s', strtotime($transaction['paid_at']))
            : date('Y-m-d H:i:s');

        $result = $consultations->markPaid($booking['consultation_id'], $paidAmount / 100, $paidAt);
        $fresh = $consultations->getById($booking['consultation_id']);

        if ($result === 'new' && $fresh) {
            ConsultationEmail::sendConfirmation($fresh);
            return 'paid';
        }

        if ($result === 'already') {
            return $fresh && $fresh['status'] === 'booked' ? 'paid' : 'taken';
        }

        if ($result === 'taken' && $fresh) {
            // Paid after the hold ran out and the time had gone: refund it all.
            self::refund($fresh, 100, 'The time was no longer available when the payment arrived.');
            ConsultationEmail::sendTimeLost($consultations->getById($booking['consultation_id']));
            return 'taken';
        }

        return 'failed';
    }

    /**
     * Cancels a confirmed booking and refunds what the policy allows.
     * $cancelledBy is 'client' or 'consultant'.
     * Returns ['ok' => true, 'refund' => amount, 'refund_status' => pending|failed|none]
     * or ['ok' => false, 'error' => message].
     */
    public static function cancel(array $booking, $cancelledBy)
    {
        $consultations = new ConsultationClass();

        if ($cancelledBy === 'client' && !ConsultationClass::clientCanCancel($booking)) {
            return ['ok' => false, 'error' => 'This booking can no longer be cancelled online.'];
        }

        // Worked out before cancelling, from the notice given now.
        $percent = ConsultationClass::refundPercent($booking, $cancelledBy);

        if (!$consultations->markCancelled($booking['consultation_id'], $cancelledBy)) {
            return ['ok' => false, 'error' => 'This booking has already been cancelled or completed.'];
        }

        $refund = ['amount' => 0.0, 'status' => 'none'];
        if (($booking['payment_status'] ?? '') === 'paid') {
            $note = $cancelledBy === 'consultant'
                ? 'Cancelled by CrownCare: full refund.'
                : ($percent < 100
                    ? 'Cancelled less than ' . ConsultationClass::FULL_REFUND_HOURS . ' hours before: ' . ConsultationClass::LATE_CANCEL_FEE_PERCENT . '% kept.'
                    : 'Cancelled ' . ConsultationClass::FULL_REFUND_HOURS . ' hours or more before: full refund.');
            $refund = self::refund($booking, $percent, $note);
        }

        $updated = $consultations->getById($booking['consultation_id']);
        ConsultationEmail::sendCancellation($updated ?: $booking, $cancelledBy);

        return ['ok' => true, 'refund' => $refund['amount'], 'refund_status' => $refund['status']];
    }

    /** Sends a refund of $percent of the amount paid. Returns ['amount', 'status']. */
    private static function refund(array $booking, $percent, $note)
    {
        $amount = round((float) $booking['amount_paid'] * $percent / 100, 2);
        if ($amount <= 0 || empty($booking['payment_reference'])) {
            return ['amount' => 0.0, 'status' => 'none'];
        }

        $response = paystack_refund($booking['payment_reference'], paystack_amount($amount));
        $status = !empty($response['status']) ? 'pending' : 'failed';

        if ($status === 'failed') {
            $note .= ' Paystack did not accept the refund (' . ($response['message'] ?? 'no reply') . '). Refund it in the Paystack dashboard.';
            error_log('Consultation refund failed for ' . $booking['reference'] . ': ' . ($response['message'] ?? 'no reply'));
        }

        (new ConsultationClass())->recordRefund($booking['consultation_id'], $amount, $status, mb_substr($note, 0, 255));

        return ['amount' => $amount, 'status' => $status];
    }
}

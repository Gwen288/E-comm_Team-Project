<?php

require_once __DIR__ . '/../core/mailer.php';
require_once __DIR__ . '/../core/email_layout.php';
require_once __DIR__ . '/ConsultationClass.php';

/*
 * Booking emails for hair consultations: confirmation, cancellation and reminder.
 * $booking is a row from the consultations table.
 * Called by the booking action and by the consultant's update/reminder actions.
 */
class ConsultationEmail
{
    /* Contact details come from core/site_config.php. */
    private const CONTACT_EMAIL = CC_CONTACT_EMAIL;
    private const CONTACT_PHONE = CC_CONTACT_PHONE;

    /** Sent by the booking action once the consultation is saved. */
    public static function sendConfirmation(array $booking)
    {
        $details = self::details($booking);
        $firstName = strtok($booking['full_name'], ' ') ?: $booking['full_name'];
        $subject = 'Your CrownCare consultation is booked (' . $booking['reference'] . ')';

        return cc_send_mail(
            $booking['email'],
            $booking['full_name'],
            $subject,
            self::html($booking['reference'], $firstName, $details),
            self::text($booking['reference'], $firstName, $details)
        );
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
        $details['To pay at your consultation'] = 'GH₵' . number_format((float) $booking['price'], 2);

        return $details;
    }

    /** Preparation notes shared by the confirmation, reminder and cancellation emails. */
    private static function tips()
    {
        return [
            'Come with clean, detangled hair without heavy styling products, so your specialist can see your natural texture and scalp.',
            'Bring or note down the products you use now.',
            'Need to change or cancel? Contact us at ' . self::CONTACT_EMAIL . ' or ' . self::CONTACT_PHONE . ' at least 24 hours before.',
        ];
    }

    /** HTML version. Styles are inline because mail clients strip stylesheets. */
    private static function html($reference, $firstName, array $details)
    {
        $e = function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };

        $rows = '';
        foreach ($details as $label => $value) {
            $rows .= '<tr>'
                . '<td style="padding:12px 0;border-top:1px solid #E7DAC4;color:#7B6D60;font-size:14px;width:45%;">' . $e($label) . '</td>'
                . '<td style="padding:12px 0;border-top:1px solid #E7DAC4;color:#3B2A1F;font-size:14px;font-weight:bold;text-align:right;">' . $e($value) . '</td>'
                . '</tr>';
        }

        $tips = '';
        foreach (self::tips() as $tip) {
            $tips .= '<li style="margin:0 0 8px;">' . $e($tip) . '</li>';
        }

        return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . $e('Consultation ' . $reference) . '</title></head>
<body style="margin:0;padding:0;background:#FDF6E8;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FDF6E8;">
<tr><td align="center" style="padding:32px 16px;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFBF3;border:1px solid #E7DAC4;border-radius:16px;">
    <tr><td style="padding:28px 32px 8px;">
      <p style="margin:0 0 24px;font-family:Georgia,serif;font-size:24px;font-weight:bold;color:#3B2A1F;">CrownCare</p>
      <p style="margin:0 0 8px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#8C5A3C;">Booking confirmed</p>
      <h1 style="margin:0 0 14px;font-family:Georgia,serif;font-size:26px;line-height:1.25;color:#3B2A1F;">Your consultation is booked</h1>
      <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#7B6D60;">Hello ' . $e($firstName) . ', thank you for booking with CrownCare. Your booking details are below. Please keep your reference number.</p>
    </td></tr>
    <tr><td style="padding:0 32px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#4A3728;border-radius:10px;">
        <tr>
          <td style="padding:16px 20px;color:#FFFFFF;font-size:12px;letter-spacing:2px;text-transform:uppercase;">Reference</td>
          <td style="padding:16px 20px;color:#FFFFFF;font-size:20px;font-weight:bold;letter-spacing:1px;text-align:right;white-space:nowrap;">' . $e($reference) . '</td>
        </tr>
      </table>
    </td></tr>
    <tr><td style="padding:12px 32px 8px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $rows . '</table>
    </td></tr>
    <tr><td style="padding:20px 32px 28px;">
      <h2 style="margin:0 0 12px;font-family:Georgia,serif;font-size:19px;color:#3B2A1F;">Before your consultation</h2>
      <ul style="margin:0;padding-left:20px;font-size:14px;line-height:1.6;color:#7B6D60;">' . $tips . '</ul>
    </td></tr>
  </table>
  <p style="margin:20px 0 0;font-size:12px;color:#7B6D60;">CrownCare. Natural hair care for African hair.</p>
</td></tr>
</table>
</body>
</html>';
    }

    /** Plain-text version sent alongside the HTML one. */
    private static function text($reference, $firstName, array $details)
    {
        $lines = [
            'Hello ' . $firstName . ',',
            '',
            'Thank you for booking with CrownCare. Your consultation is booked.',
            '',
            'Reference: ' . $reference,
        ];
        foreach ($details as $label => $value) {
            $lines[] = $label . ': ' . $value;
        }
        $lines[] = '';
        $lines[] = 'Before your consultation:';
        foreach (self::tips() as $tip) {
            $lines[] = '- ' . $tip;
        }
        $lines[] = '';
        $lines[] = 'CrownCare';

        return implode("\n", $lines);
    }

    /** Sent when a consultant cancels a booking. */
    public static function sendCancellation(array $booking)
    {
        if (($booking['email'] ?? '') === '') {
            return false;
        }
        $firstName = strtok($booking['full_name'], ' ') ?: $booking['full_name'];

        return cc_email_send($booking['email'], $booking['full_name'], 'Your CrownCare consultation has been cancelled (' . $booking['reference'] . ')', [
            'label' => 'Booking cancelled',
            'title' => 'Your consultation has been cancelled',
            'intro' => 'Hello ' . $firstName . ', we are sorry, but we have had to cancel your consultation below. Please book another time that suits you, or contact us and we will help you find one.',
            'highlight' => ['Reference', $booking['reference']],
            'details' => self::details($booking),
            'button' => ['Book another time', absolute_url(BASE_URL . 'views/book_consultation.php')],
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
            'notes' => self::tips(),
        ]);
    }
}

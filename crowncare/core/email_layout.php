<?php

require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/site_config.php';

/*
 * One layout for every CrownCare email (same look as the booking email).
 *
 * $email = [
 *   'label'    => 'Order received',            small heading above the title
 *   'title'    => 'Thank you for your order',
 *   'intro'    => 'Hello Ama, ...',
 *   'highlight'=> ['Order', '#123'],            optional dark band
 *   'details'  => ['Label' => 'Value', ...],    optional table
 *   'lines'    => [['Product x 2', 'GH₵90.00'], ...]  optional item table
 *   'button'   => ['Reset your password', 'https://...'],  optional
 *   'notes'    => ['Paragraph', ...],           optional, under the table
 * ]
 * Returns [html, text].
 */
function cc_email_build(array $email)
{
    $e = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    $rowsHtml = function (array $pairs) use ($e) {
        $rows = '';
        foreach ($pairs as $pair) {
            [$label, $value] = $pair;
            $rows .= '<tr>'
                . '<td style="padding:12px 0;border-top:1px solid #E7DAC4;color:#7B6D60;font-size:14px;">' . $e($label) . '</td>'
                . '<td style="padding:12px 0;border-top:1px solid #E7DAC4;color:#3B2A1F;font-size:14px;font-weight:bold;text-align:right;">' . $e($value) . '</td>'
                . '</tr>';
        }
        return $rows;
    };

    $details = [];
    foreach ($email['details'] ?? [] as $label => $value) {
        $details[] = [$label, $value];
    }

    $html = '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . $e($email['title']) . '</title></head>
<body style="margin:0;padding:0;background:#FDF6E8;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FDF6E8;">
<tr><td align="center" style="padding:32px 16px;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFBF3;border:1px solid #E7DAC4;border-radius:16px;">
    <tr><td style="padding:28px 32px 8px;">
      <p style="margin:0 0 24px;font-family:Georgia,serif;font-size:24px;font-weight:bold;color:#3B2A1F;">CrownCare</p>
      <p style="margin:0 0 8px;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#8C5A3C;">' . $e($email['label'] ?? '') . '</p>
      <h1 style="margin:0 0 14px;font-family:Georgia,serif;font-size:26px;line-height:1.25;color:#3B2A1F;">' . $e($email['title']) . '</h1>
      <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#7B6D60;">' . $e($email['intro'] ?? '') . '</p>
    </td></tr>';

    if (!empty($email['highlight'])) {
        $html .= '
    <tr><td style="padding:0 32px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#4A3728;border-radius:10px;">
        <tr>
          <td style="padding:16px 20px;color:#FFFFFF;font-size:12px;letter-spacing:2px;text-transform:uppercase;">' . $e($email['highlight'][0]) . '</td>
          <td style="padding:16px 20px;color:#FFFFFF;font-size:20px;font-weight:bold;text-align:right;white-space:nowrap;">' . $e($email['highlight'][1]) . '</td>
        </tr>
      </table>
    </td></tr>';
    }

    if (!empty($email['lines'])) {
        $html .= '
    <tr><td style="padding:12px 32px 0;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $rowsHtml($email['lines']) . '</table>
    </td></tr>';
    }

    if ($details) {
        $html .= '
    <tr><td style="padding:12px 32px 8px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $rowsHtml($details) . '</table>
    </td></tr>';
    }

    if (!empty($email['button'])) {
        $html .= '
    <tr><td style="padding:20px 32px 8px;">
      <a href="' . $e($email['button'][1]) . '" style="display:inline-block;padding:13px 26px;border-radius:999px;background:#4A3728;color:#FDF6E8;font-size:15px;font-weight:bold;text-decoration:none;">' . $e($email['button'][0]) . '</a>
    </td></tr>';
    }

    $notes = $email['notes'] ?? [];
    $notes[] = 'Questions? Contact us at ' . CC_CONTACT_EMAIL . ' or ' . CC_CONTACT_PHONE . '.';
    $notesHtml = '';
    foreach ($notes as $note) {
        $notesHtml .= '<p style="margin:0 0 10px;font-size:14px;line-height:1.6;color:#7B6D60;">' . $e($note) . '</p>';
    }

    $html .= '
    <tr><td style="padding:18px 32px 26px;">' . $notesHtml . '</td></tr>
  </table>
  <p style="margin:20px 0 0;font-size:12px;color:#7B6D60;">CrownCare. Natural hair care for African hair, delivered in Accra.</p>
</td></tr>
</table>
</body>
</html>';

    // Plain-text version for email apps that don't show HTML.
    $text = [$email['title'], '', $email['intro'] ?? '', ''];
    if (!empty($email['highlight'])) {
        $text[] = $email['highlight'][0] . ': ' . $email['highlight'][1];
    }
    foreach ($email['lines'] ?? [] as [$label, $value]) {
        $text[] = $label . ': ' . $value;
    }
    foreach ($details as [$label, $value]) {
        $text[] = $label . ': ' . $value;
    }
    if (!empty($email['button'])) {
        $text[] = '';
        $text[] = $email['button'][0] . ': ' . $email['button'][1];
    }
    $text[] = '';
    foreach ($notes as $note) {
        $text[] = $note;
    }
    $text[] = '';
    $text[] = 'CrownCare';

    return [$html, implode("\n", $text)];
}

/** Builds and sends one email. Returns true when it was accepted for sending. */
function cc_email_send($toEmail, $toName, $subject, array $email)
{
    [$html, $text] = cc_email_build($email);

    return cc_send_mail($toEmail, $toName, $subject, $html, $text);
}

<?php

/*
 * Sends email through SMTP with PHPMailer (lib/PHPMailer).
 * Settings are in core/mail_config.php (copy of mail_config_example.php).
 */

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$mailConfig = __DIR__ . '/mail_config.php';
if (!is_file($mailConfig)) {
    $mailConfig = __DIR__ . '/mail_config_example.php';
}
require_once $mailConfig;

/**
 * Sends one email. Returns true when the mail server accepted it.
 * Problems are written to the PHP error log (C:\xampp\php\logs\php_error_log).
 */
function cc_send_mail($toEmail, $toName, $subject, $html, $text)
{
    if (!MAIL_ENABLED || SMTP_PASS === '') {
        error_log('CrownCare mail not sent: email is turned off or SMTP_PASS is empty in core/mail_config.php.');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = str_replace(' ', '', SMTP_PASS);
        $mail->SMTPSecure = SMTP_PORT == 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Timeout = 15;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        if (defined('MAIL_REPLY_TO') && MAIL_REPLY_TO !== '') {
            $mail->addReplyTo(MAIL_REPLY_TO, MAIL_FROM_NAME);
        }
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $text;

        return $mail->send();
    } catch (MailException $e) {
        error_log('CrownCare mail error: ' . $mail->ErrorInfo);
        return false;
    }
}

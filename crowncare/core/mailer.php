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
require_once __DIR__ . '/site_config.php';

/**
 * Sends one email. Returns true when the mail server accepted it.
 * Problems are written to the PHP error log (C:\xampp\php\logs\php_error_log).
 */
function cc_send_mail($toEmail, $toName, $subject, $html, $text)
{
    // For testing without Gmail: set MAIL_DEV_LOG in mail_config.php to a file
    // path, and emails are written to that file instead of being sent.
    if (defined('MAIL_DEV_LOG') && MAIL_DEV_LOG !== '') {
        file_put_contents(
            MAIL_DEV_LOG,
            "To: $toName <$toEmail>\nSubject: $subject\n\n$text\n\n" . str_repeat('-', 60) . "\n",
            FILE_APPEND
        );
        return true;
    }

    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

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

        // Customers' replies go to the shop's contact address (core/site_config.php).
        $replyTo = defined('CC_CONTACT_EMAIL') ? CC_CONTACT_EMAIL : (defined('MAIL_REPLY_TO') ? MAIL_REPLY_TO : '');
        if ($replyTo !== '') {
            $mail->addReplyTo($replyTo, 'CrownCare');
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

<?php

/**
 * Email settings for booking confirmations.
 *
 * Copy this file to mail_config.php (in this same core folder) and fill in
 * the Gmail address and app password of the CrownCare account that sends the emails.
 * mail_config.php is ignored by Git, so the password is never committed.
 *
 * Gmail app password: turn on 2-Step Verification for the Google account, then
 * create one at https://myaccount.google.com/apppasswords (16 letters, no spaces needed).
 */

/* Set to false to stop sending emails (bookings still save). */
define('MAIL_ENABLED', true);

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your.crowncare.account@gmail.com');
define('SMTP_PASS', '');

/* Who the email comes from. With Gmail this must be the same address as SMTP_USER. */
define('MAIL_FROM', 'your.crowncare.account@gmail.com');
define('MAIL_FROM_NAME', 'CrownCare');

/* Where customer replies go. */
define('MAIL_REPLY_TO', 'your.crowncare.account@gmail.com');

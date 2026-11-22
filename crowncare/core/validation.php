<?php

/*
 * Checks shared by sign up, account settings and checkout.
 * Each function returns an error message, or '' when the value is fine.
 */

const CC_ALLOWED_EMAIL_DOMAINS = [
    'gmail.com',
    'outlook.com',
    'hotmail.com',
    'yahoo.com',
    'icloud.com',
    'live.com',
    'protonmail.com',
    'aol.com',
    'mail.com',
    'zoho.com',
];

function cc_check_name($name)
{
    $name = trim((string) $name);

    if ($name === '') {
        return 'Enter your full name.';
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        return 'Your name must be between 2 and 100 characters.';
    }
    if (!preg_match("/^[\\p{L}][\\p{L} .'\\-]*$/u", $name)) {
        return 'Use letters, spaces, hyphens and apostrophes only.';
    }
    return '';
}

function cc_check_email($email)
{
    $email = trim((string) $email);

    if ($email === '') {
        return 'Enter your email address.';
    }
    if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Enter a valid email address.';
    }

    $domain = strtolower(substr(strrchr($email, '@'), 1));
    if (!in_array($domain, CC_ALLOWED_EMAIL_DOMAINS, true) && !str_ends_with($domain, '.edu.gh')) {
        return 'Use a common email provider (such as Gmail, Outlook or Yahoo) or a school address ending in .edu.gh.';
    }
    return '';
}

/** Password rules, also listed on the sign up and change password forms. */
function cc_check_password($password)
{
    $password = (string) $password;

    if (strlen($password) < 8
        || !preg_match('/[a-z]/', $password)
        || !preg_match('/[A-Z]/', $password)
        || !preg_match('/[0-9]/', $password)
        || !preg_match('/[^A-Za-z0-9]/', $password)) {
        return 'Use at least 8 characters with an uppercase letter, a lowercase letter, a number and a symbol.';
    }
    if (strlen($password) > 72) {
        return 'Use 72 characters or fewer.';
    }
    return '';
}

/**
 * Ghana phone numbers: 10 digits starting with 0 (for example 024 123 4567),
 * or +233 followed by 9 digits. Returns the number as 0XXXXXXXXX, or false.
 */
function cc_normalise_phone($phone)
{
    $digits = preg_replace('/[\s\-().]/', '', (string) $phone);

    if (preg_match('/^\+?233([2-5]\d{8})$/', $digits, $m)) {
        return '0' . $m[1];
    }
    if (preg_match('/^0[2-5]\d{8}$/', $digits)) {
        return $digits;
    }
    return false;
}

function cc_check_phone($phone)
{
    if (trim((string) $phone) === '') {
        return 'Enter your phone number.';
    }
    if (cc_normalise_phone($phone) === false) {
        return 'Enter a Ghana number: 10 digits starting with 0 (for example 024 123 4567), or +233 and 9 digits.';
    }
    return '';
}

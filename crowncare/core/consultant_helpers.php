<?php

/*
 * Shared helpers for the consultant pages and actions.
 */

const CC_APPOINTMENT_STATUSES = [
    'booked' => 'Booked',
    'completed' => 'Completed',
    'no_show' => 'No-show',
    'cancelled' => 'Cancelled',
];

/** A per-session token that every consultant form sends back. */
function cc_consultant_token()
{
    if (empty($_SESSION['consultant_token'])) {
        $_SESSION['consultant_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['consultant_token'];
}

function cc_consultant_token_field()
{
    return '<input type="hidden" name="token" value="' . htmlspecialchars(cc_consultant_token()) . '">';
}

/** Stops the request unless it is a POST with the right token. */
function cc_require_consultant_post($backUrl)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || !hash_equals($_SESSION['consultant_token'] ?? '', (string) ($_POST['token'] ?? ''))) {
        $_SESSION['error'] = 'Your session expired. Please try again.';
        redirect($backUrl);
    }
}

function cc_status_label($status)
{
    return CC_APPOINTMENT_STATUSES[$status] ?? ucfirst((string) $status);
}

/** Badge class: reuses the admin status colours. */
function cc_status_class($status)
{
    switch ($status) {
        case 'completed':
            return 'admin-status admin-status-paid';
        case 'cancelled':
        case 'no_show':
            return 'admin-status admin-status-failed';
        default:
            return 'admin-status consultant-status-booked';
    }
}

function cc_time_label($time)
{
    return date('g:i a', strtotime($time));
}

function cc_date_label($date, $format = 'D j M Y')
{
    return date($format, strtotime($date));
}

function cc_format_label($format)
{
    return $format === 'video' ? 'Video call' : 'In person';
}

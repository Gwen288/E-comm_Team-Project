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

/* Consultant forms use the site-wide form token (checked in core/core.php). */
function cc_consultant_token()
{
    return cc_csrf_token();
}

function cc_consultant_token_field()
{
    return cc_csrf_field();
}

/** Stops the request unless it is a POST (the form token was already checked). */
function cc_require_consultant_post($backUrl)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

<?php

/*
 * Shared helpers for the consultant pages and actions.
 * views/consultant/ uses these for labels, badges and the form token.
 */

/* Keys are the status values stored on a booking in the database. */
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

/*
 * Actions call this at the top, before touching the database.
 * core/core.php has already checked the token on every POST.
 */
function cc_require_consultant_post($backUrl)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect($backUrl);
    }
}

/* ---------- Status labels and badges ---------- */

/** Falls back to the raw value so a new status doesn't show up blank. */
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

/* ---------- Date, time and format labels ---------- */

/* The database keeps times in 24-hour form, e.g. 09:00 or 14:30. */
function cc_time_label($time)
{
    return date('g:i a', strtotime($time));
}

function cc_date_label($date, $format = 'D j M Y')
{
    return date($format, strtotime($date));
}

/** 'video' is what's stored on the booking; anything else is in person. */
function cc_format_label($format)
{
    return $format === 'video' ? 'Video call' : 'In person';
}

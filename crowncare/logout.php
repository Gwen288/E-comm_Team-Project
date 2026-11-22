<?php

/*
 * Signs the current customer out and sends them back to the home page.
 * The Sign out button posts here; core/core.php checks the form token.
 */

require_once __DIR__ . '/core/core.php';

// Signing out only happens through the Sign out button (a POST with the form token),
// so a link on another site can't sign people out.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(is_logged_in() ? cc_home_for_role() : BASE_URL . 'index.php');
}

$_SESSION = [];
// New session ID, so an old one can't be used after signing out.
session_regenerate_id(true);

// Set this after the clear above, or it would be wiped out too.
$_SESSION['success'] = 'You have been signed out.';

redirect(BASE_URL . 'index.php');

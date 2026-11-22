<?php

require_once __DIR__ . '/core/core.php';

// Signing out only happens through the Sign out button (a POST with the form token),
// so a link on another site can't sign people out.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(is_logged_in() ? cc_home_for_role() : BASE_URL . 'index.php');
}

$_SESSION = [];
session_regenerate_id(true);

$_SESSION['success'] = 'You have been signed out.';

redirect(BASE_URL . 'index.php');

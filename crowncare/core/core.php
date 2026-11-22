<?php

date_default_timezone_set('Africa/Accra');

// Base URL of the CrownCare project, worked out from where the crowncare
// folder actually is, so it is correct on the laptop (XAMPP) and on the
// school server whatever folder the site is uploaded to.
// It compares the file being run (for example .../crowncare/views/shop.php)
// with its web address (for example /~petra.asima/x/crowncare/views/shop.php).
function cc_detect_base_url()
{
    $normalise = function ($path) {
        return rtrim(str_replace('\\', '/', (string) $path), '/');
    };

    $appRoot   = $normalise(realpath(dirname(__DIR__)));
    $script    = $normalise(realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $scriptUrl = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    if ($appRoot !== '' && $script !== '' && stripos($script, $appRoot . '/') === 0) {
        // Part of the path inside the crowncare folder, for example /views/shop.php
        $inside = substr($script, strlen($appRoot));
        $tail   = substr($scriptUrl, -strlen($inside));

        if ($tail !== false && strcasecmp($tail, $inside) === 0) {
            return substr($scriptUrl, 0, strlen($scriptUrl) - strlen($inside)) . '/';
        }
    }

    // Fallback if the folder can't be worked out.
    return '/E-comm_Team-Project/crowncare/';
}

define("BASE_URL", cc_detect_base_url());

/* True on the laptop (XAMPP), false on the school server. */
define('CC_IS_LOCAL', in_array(
    strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost')),
    ['localhost', '127.0.0.1', '::1'],
    true
));

define('CC_IS_HTTPS', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

/* Signed-in people are signed out after this many seconds without activity. */
const CC_IDLE_TIMEOUT = 1800;

/* ---------------------------------------------------------------------------
   Errors: shown on the laptop, logged (never shown) on the server.
   A failed query or crash shows a friendly error page instead of a blank page.
   ------------------------------------------------------------------------ */
error_reporting(E_ALL);
ini_set('display_errors', CC_IS_LOCAL ? '1' : '0');
ini_set('log_errors', '1');

// Database errors throw exceptions on every PHP version, so the laptop
// behaves like the school server.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function cc_render_error_page($code)
{
    if (!headers_sent()) {
        http_response_code($code);
    }
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $errorCode = $code;
    require __DIR__ . '/../views/error_page.php';
    exit;
}

set_exception_handler(function ($e) {
    error_log('CrownCare error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if (true) {
        echo '<pre style="padding:16px;white-space:pre-wrap">'
            . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
        exit;
    }

    cc_render_error_page(500);
});

/* ---------------------------------------------------------------------------
   Session. CrownCare has its own session name, and its cookie is limited to
   the CrownCare folder, so another project on the same server can't share it.
   ------------------------------------------------------------------------ */
if (session_status() === PHP_SESSION_NONE) {
    session_name('CROWNCARESESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE_URL,
        'secure' => CC_IS_HTTPS,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// Sign out after a period without activity (guests keep their cart).
if (isset($_SESSION['customer_id'])) {
    $lastActive = $_SESSION['last_active'] ?? time();

    if (time() - $lastActive > CC_IDLE_TIMEOUT) {
        $guestCart = $_SESSION['guest_cart'] ?? null;
        $_SESSION = [];
        session_regenerate_id(true);
        if ($guestCart !== null) {
            $_SESSION['guest_cart'] = $guestCart;
        }
        $_SESSION['error'] = 'You were signed out after 30 minutes without activity. Please sign in again.';
    } else {
        $_SESSION['last_active'] = time();
    }
}

/* ---------------------------------------------------------------------------
   Form tokens (CSRF). Every POST request must send back the session's token,
   either as a hidden "csrf_token" field or an "X-CSRF-Token" header.
   Pages add the field with cc_csrf_field(); js/app.js also adds it to any
   POST form that is missing it.
   ------------------------------------------------------------------------ */
function cc_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function cc_csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(cc_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function cc_is_ajax()
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
}

/** Sends a JSON reply (for forms sent without reloading the page) and stops. */
function cc_json($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/** Address of the page the person came from, if it is inside CrownCare. */
function cc_back_url($fallback = null)
{
    $fallback = $fallback ?: BASE_URL . 'index.php';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $path = parse_url($referer, PHP_URL_PATH) ?: '';
    $host = parse_url($referer, PHP_URL_HOST) ?: '';
    $ownHost = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');

    if ($referer !== '' && strcasecmp($host, $ownHost) === 0 && stripos($path, BASE_URL) === 0) {
        $query = parse_url($referer, PHP_URL_QUERY);
        return $path . ($query ? '?' . $query : '');
    }

    return $fallback;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !defined('CC_SKIP_CSRF')) {
    $sentToken = (string) ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sentToken)) {
        $message = 'Your session expired. Please try again.';

        if (cc_is_ajax()) {
            cc_json(['ok' => false, 'message' => $message], 419);
        }

        $_SESSION['error'] = $message;
        header('Location: ' . cc_back_url());
        exit;
    }
}

require_once __DIR__ . '/site_config.php';
require_once __DIR__ . '/order_helpers.php';
require_once __DIR__ . '/db_class.php';
require_once __DIR__ . '/product_images.php';

// Paystack keys live in core/paystack_config.php (not in Git).
$paystackConfig = __DIR__ . '/paystack_config.php';
if (!is_file($paystackConfig)) {
    $paystackConfig = __DIR__ . '/paystack_config_example.php';
}
require_once $paystackConfig;
require_once __DIR__ . '/paystack.php';


function get_ip()
{
    return $_SERVER['REMOTE_ADDR'] ?? '';
}


function get_customer_id()
{
    return isset($_SESSION['customer_id'])
        ? (int) $_SESSION['customer_id']
        : 0;
}


function redirect($url)
{
    header("Location: $url");
    exit;
}


/** Stops with a friendly "not found" page. */
function cc_not_found()
{
    cc_render_error_page(404);
}


/**
 * Build a fully qualified URL from a path such as BASE_URL . 'actions/x.php'.
 * Paystack requires an absolute callback URL.
 */
function absolute_url($path)
{
    $scheme = CC_IS_HTTPS ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}


/**
 * Signs a person in: new session ID (so an old one can't be reused),
 * their details in the session, and the activity clock started.
 */
function cc_sign_in(array $customer)
{
    session_regenerate_id(true);

    $_SESSION['customer_id'] = (int) $customer['customer_id'];
    $_SESSION['customer_name'] = $customer['customer_name'];
    $_SESSION['customer_email'] = $customer['customer_email'];
    $_SESSION['user_role'] = (int) $customer['user_role'];
    $_SESSION['last_active'] = time();
    unset($_SESSION['csrf_token']);
}


/** Where a person goes after signing in, by role. */
function cc_home_for_role()
{
    if (is_admin()) {
        return BASE_URL . 'views/admin/index.php';
    }
    if (is_consultant()) {
        return BASE_URL . 'views/consultant/index.php';
    }
    return BASE_URL . 'index.php';
}


function is_logged_in()
{
    return isset($_SESSION['customer_id']);
}


function is_admin()
{
    return isset($_SESSION['user_role']) && (int) $_SESSION['user_role'] === 1;
}


/** Consultants (user_role 3) manage hair consultations in views/consultant/. */
function is_consultant()
{
    return isset($_SESSION['user_role']) && (int) $_SESSION['user_role'] === 3;
}


/** Customers are signed-in people who are not admins or consultants. */
function is_customer()
{
    return is_logged_in() && !is_admin() && !is_consultant();
}


function require_login()
{
    if (!is_logged_in()) {
        $_SESSION['error'] = 'Please sign in to continue.';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        }
        redirect(BASE_URL . 'views/login.php');
    }
}


/** Pages for customers only (account, orders). Staff go to their dashboard. */
function require_customer()
{
    require_login();

    if (!is_customer()) {
        redirect(cc_home_for_role());
    }
}


function require_admin()
{
    if (!is_logged_in()) {
        $_SESSION['error'] = 'Please sign in to continue.';
        redirect(BASE_URL . 'views/login.php');
    }

    if (!is_admin()) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        redirect(cc_home_for_role());
    }
}


function require_consultant()
{
    if (!is_logged_in()) {
        $_SESSION['error'] = 'Please sign in to continue.';
        redirect(BASE_URL . 'views/login.php');
    }

    if (!is_consultant()) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        redirect(cc_home_for_role());
    }
}


/**
 * Admins and consultants can't buy. Used on the cart, checkout and payment.
 * They are sent back to their own dashboard.
 */
function block_admin_purchase($redirectTo = null)
{
    if (is_admin() || is_consultant()) {
        if (cc_is_ajax()) {
            cc_json(['ok' => false, 'message' => 'Staff accounts cannot place orders.'], 403);
        }
        redirect(cc_home_for_role());
    }
}


/** Consultants only use their own area; store pages send them back to it. */
function keep_consultants_in_their_area()
{
    if (is_consultant()) {
        redirect(BASE_URL . 'views/consultant/index.php');
    }
}


/** Shortcut for escaping text shown on a page. */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}



/** Signs the person out (for example when their account no longer exists) and asks them to sign in. */
function cc_force_sign_out($message)
{
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['error'] = $message;
    redirect(BASE_URL . 'views/login.php');
}


/* ---------------------------------------------------------------------------
   Form replies. Forms sent without reloading (js/app.js) get JSON; without
   JavaScript the page reloads with a message, and what was typed is kept
   (never passwords) in $_SESSION['old_input'].
   ------------------------------------------------------------------------ */

function cc_form_error($message, array $fieldErrors = [], $backUrl = null)
{
    if (cc_is_ajax()) {
        cc_json(['ok' => false, 'message' => $message, 'errors' => $fieldErrors], 422);
    }

    $old = $_POST;
    foreach (array_keys($old) as $key) {
        if (stripos($key, 'pass') !== false || $key === 'csrf_token') {
            unset($old[$key]);
        }
    }
    $_SESSION['old_input'] = $old;
    $_SESSION['error'] = $message !== '' ? $message : (reset($fieldErrors) ?: 'Please check the form.');

    redirect($backUrl ?: cc_back_url());
}

function cc_form_success($redirectUrl, $message = '')
{
    if ($message !== '') {
        $_SESSION['success'] = $message;
    }
    unset($_SESSION['old_input']);

    if (cc_is_ajax()) {
        cc_json(['ok' => true, 'redirect' => $redirectUrl]);
    }
    redirect($redirectUrl);
}

/** A value typed before a page reload, for refilling a form without JavaScript. */
function cc_old($key, $default = '')
{
    return $_SESSION['old_input'][$key] ?? $default;
}

/** Call at the end of a page that used cc_old(). */
function cc_forget_old()
{
    unset($_SESSION['old_input']);
}

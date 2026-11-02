<?php

session_start();

date_default_timezone_set('Africa/Accra');

// Base URL of the CrownCare project
define("BASE_URL","/E-comm_Team-Project/crowncare/");

require_once __DIR__ . '/db_class.php';

// Use local Paystack keys when available, otherwise fall back to the example file.
$paystackConfig = __DIR__ . '/paystack_config.php';
if (!is_file($paystackConfig)) {
    $paystackConfig = __DIR__ . '/paystack_config_example.php';
}
require_once $paystackConfig;
require_once __DIR__ . '/paystack.php';


function get_ip()
{
    return $_SERVER['REMOTE_ADDR'];
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


/**
 * Build a fully qualified URL from a path such as BASE_URL . 'actions/x.php'.
 * Paystack requires an absolute callback URL.
 */
function absolute_url($path)
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https'
        : 'http';

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}


function is_logged_in()
{
    return isset($_SESSION['customer_id']);
}


function is_admin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
}


function require_login(){

    if(!is_logged_in()){
        $_SESSION["error"]="Please log in to continue.";
        redirect("../login.php");
    }

}


function require_admin(){

    if(!is_admin()){
        $_SESSION['error']="You do not have permission to access this page.";
        redirect("../index.php");
    }
}
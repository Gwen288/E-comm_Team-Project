<?php

session_start();

date_default_timezone_set('Africa/Accra');

//Base URL of the Shoppn project
//define("BASE_URL","/~wendy.dwumfuor/E-Commerce_Class/E-Commerce_Lab/shoppn/");
define("BASE_URL","/E-Commerce_Class/E-Commerce_Lab/shoppn/");

require_once __DIR__ . '/db_class.php';


function get_ip()
{
    return $_SERVER['REMOTE_ADDR'];
}


function redirect($url)
{
    header("Location: $url");
    exit;
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
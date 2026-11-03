
<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';


// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../views/login.php');
}


// Get and sanitize input
$email = trim(strip_tags($_POST['email'] ?? ''));
$pass = $_POST['pass'] ?? '';


// Check required fields
if ($email === '' || $pass === '') {
    $_SESSION['error'] = 'Email and password are required.';
    redirect('../views/login.php');
}


// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    redirect('../views/login.php');
}


// Send login data to the Controller
$controller = new CustomerController();

$result = $controller->login($email, $pass);


// Login successful
if (isset($result['customer_id'])) {

    $_SESSION['customer_id'] = $result['customer_id'];
    $_SESSION['customer_name'] = $result['customer_name'];
    $_SESSION['customer_email'] = $result['customer_email'];
    $_SESSION['user_role'] = $result['user_role'];

    redirect('../index.php');
}


// Login failed
$_SESSION['error'] = $result['error'];

redirect('../views/login.php');

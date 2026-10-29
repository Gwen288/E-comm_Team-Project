<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/register.php');
}


$name = trim(strip_tags($_POST['name'] ?? ''));

$email = trim(strip_tags($_POST['email'] ?? ''));

$pass = $_POST['pass'] ?? '';

$country = trim(strip_tags($_POST['country'] ?? ''));

$city = trim(strip_tags($_POST['city'] ?? ''));

$contact = trim(strip_tags($_POST['contact'] ?? ''));


// Check that all required fields were provided
if (
    $name === '' ||
    $email === '' ||
    $pass === '' ||
    $country === '' ||
    $city === '' ||
    $contact === ''
) {

    $_SESSION['error'] =
        'All required fields must be completed.';

    redirect(BASE_URL . 'views/register.php');
}


// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION['error'] =
        'Please enter a valid email address.';

    redirect(BASE_URL . 'views/register.php');
}


// Check email length based on database schema
if (strlen($email) > 100) {

    $_SESSION['error'] =
        'Email address must not exceed 100 characters.';

    redirect(BASE_URL . 'views/register.php');
}


// Check email domain

$allowedDomains = [
    'gmail.com',
    'outlook.com',
    'hotmail.com',
    'yahoo.com',
    'icloud.com',
    'live.com',
    'protonmail.com',
    'aol.com',
    'mail.com',
    'zoho.com'
];


$emailParts = explode('@', strtolower($email));

$domain = end($emailParts);


// Check for popular personal email provider
$isPopularDomain = in_array(
    $domain,
    $allowedDomains,
    true
);


// Allow Ghanaian school/institutional email domains
$isSchoolDomain = str_ends_with(
    $domain,
    '.edu.gh'
);


// Reject unrecognized domains
if (!$isPopularDomain && !$isSchoolDomain) {

    $_SESSION['error'] =
        'Please use a recognized email provider or a school email address ending in .edu.gh.';

    redirect(BASE_URL . 'views/register.php');
}


// Password strength validation

if (
    strlen($pass) < 8 ||
    !preg_match('/[a-z]/', $pass) ||
    !preg_match('/[A-Z]/', $pass) ||
    !preg_match('/[0-9]/', $pass) ||
    !preg_match('/[^A-Za-z0-9]/', $pass)
) {
    $_SESSION['error'] =
        'Password must be at least 8 characters and include uppercase and lowercase letters, a number, and a special character (e.g., !, @, #, $).';

    redirect(BASE_URL . 'views/register.php');
}

// Prepare data for controller

$data = [
    'name' => $name,
    'email' => strtolower($email),
    'pass' => $pass,
    'country' => $country,
    'city' => $city,
    'contact' => $contact
];


$controller = new CustomerController();

$result = $controller->register($data);


// Registration successful

if ($result['success']) {

    $_SESSION['customer_id'] =
        $result['customer_id'];

    $_SESSION['customer_name'] =
        $name;

    $_SESSION['customer_email'] =
        strtolower($email);

    $_SESSION['user_role'] =
        $result['user_role'];


    redirect(
        BASE_URL . 'views/account/my_account.php'
    );

    exit;
}


// Registration failed

$_SESSION['error'] =
    $result['error'];

redirect(
    BASE_URL . 'views/register.php'
);


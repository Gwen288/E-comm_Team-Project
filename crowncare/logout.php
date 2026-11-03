<?php

require_once 'core/core.php';

session_unset();
session_destroy();

session_start();

$_SESSION['success'] = 'You have been logged out successfully.';


redirect("index.php");


?>
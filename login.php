<?php
session_start();

// Set a demo session
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Sarah Mthembu';

// Redirect to dashboard
header('Location: Dashboard.php');
exit();
?>
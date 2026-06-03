<?php
// logout.php - Separate logout handler
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Clear session
logoutEmployee();

// Redirect to main page
header('Location: index.php');
exit();
?>
<?php
// ================================================
// MAIN ENTRY POINT - This file is the front controller for all requests.
// ================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/src/routes/index.php';
<?php

// Router for `php -S localhost:4321 router.php`
// This forwards every non-file request into the app's front controller.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$publicDir = __DIR__ . '/src/public';
$candidate = $publicDir . $path;

if ($path !== '/' && file_exists($candidate) && !is_dir($candidate)) {
return false;
}

require_once $publicDir . '/Index.php';
<?php
// router.php — PHP built-in server router
// Routes all non-static requests through index.php

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$filePath = __DIR__ . $requestPath;

// If the request is for a real file (css, js, images, etc.) serve it directly
if ($requestPath !== '/' && file_exists($filePath) && is_file($filePath)) {
    return false;
}

// Everything else goes through the front controller
require __DIR__ . '/index.php';
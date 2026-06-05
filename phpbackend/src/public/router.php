<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$filePath = __DIR__ . $requestPath;

if ($requestPath !== '/' && file_exists($filePath) && is_file($filePath)) {
    return false;
}

require __DIR__ . '/index.php';
<?php

declare(strict_types=1);

/**
 * Router for `php -S localhost:4321 router.php`
 * This forwards every non-file request into the app's front controller.
 * $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
 * $publicDir = __DIR__ . '/src/public';
 * $candidate = $publicDir . $path;
 *
 * if ($path !== '/' && file_exists($candidate) && !is_dir($candidate)) {
 *     return false;
 * }
 *
 * require_once $publicDir . '/index.php';
 */

/**
 * Router for: php -S localhost:4321 router.php
 * 
 * - Serves static files from /src/public if they exist
 * - Otherwise forwards everything to Index.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

$publicDir = __DIR__ . '/src/public';
$filePath  = realpath($publicDir . $uri);

// ==================================================
// 1. SERVE STATIC FILES (CSS, JS, images, etc.)
// ==================================================
if (
    $uri !== '/' &&
    $filePath !== false &&
    str_starts_with($filePath, realpath($publicDir)) &&
    is_file($filePath)
) {
    return false; // let PHP built-in server handle it
}

// ==================================================
// 2. FALLBACK → FRONT CONTROLLER
// ==================================================
require_once $publicDir . '/Index.php';
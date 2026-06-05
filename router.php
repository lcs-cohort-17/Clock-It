<?php
/**
 * router.php — PHP built-in server router
 *
 * Run with: php -S localhost:8000 router.php
 * (NO -t flag needed — this handles the docroot)
 *
 * Serves static files from public/ and routes everything else through
 * public/index.php, bypassing the built-in server's directory resolution
 * which would otherwise 404 on /api/* paths because public/api/ exists.
 */

$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Serve real static files directly from public/
$publicFile = __DIR__ . '/public' . $requestedPath;
if ($requestedPath !== '/' && is_file($publicFile)) {
    // Serve the file with correct MIME type
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mimes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'json' => 'application/json',
        'txt'  => 'text/plain',
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    readfile($publicFile);
    return true;
}

// Everything else → app router
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';


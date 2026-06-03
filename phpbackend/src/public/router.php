<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $publicFile = realpath(__DIR__ . $requestedPath);

    if (
        $requestedPath !== '/'
        && $publicFile !== false
        && str_starts_with($publicFile, __DIR__ . DIRECTORY_SEPARATOR)
        && is_file($publicFile)
    ) {
        return false;
    }
}

require __DIR__ . '/index.php';

<?php

declare(strict_types=1);

if (!function_exists('app_site_url')) {
    function app_site_url(): string
    {
        if (defined('APP_SITE_URL')) {
            return rtrim((string) APP_SITE_URL, '/');
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
        return rtrim(dirname($scriptName), '/');
    }
}

if (!function_exists('app_asset_url')) {
    function app_asset_url(string $path = ''): string
    {
        $base = defined('APP_ASSET_URL') ? rtrim((string) APP_ASSET_URL, '/') : app_site_url();

        if ($path === '') {
            return $base;
        }

        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $base = app_site_url();

        if ($path === '') {
            return $base === '' ? '/' : $base . '/';
        }

        return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
    }
}

if (!function_exists('app_request_path')) {
    function app_request_path(): string
    {
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $basePath = app_site_url();

        if ($basePath !== '' && $basePath !== '/' && str_starts_with($requestPath, $basePath)) {
            $requestPath = substr($requestPath, strlen($basePath));
            $requestPath = $requestPath === '' ? '/' : $requestPath;
        }

        return $requestPath;
    }
}

if (!defined('APP_SITE_URL')) {
    define('APP_SITE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/'));
}

if (!defined('APP_ASSET_URL')) {
    define('APP_ASSET_URL', APP_SITE_URL);
}

$requestPath = app_request_path();
$page = strtolower(trim((string) ($_GET['page'] ?? '')));

if ($page === 'staff') {
    require __DIR__ . '/../src/views/staff/scanqrpage.php';
    return;
}

if (
    $page === ''
    || $requestPath === '/'
    || basename($requestPath) === 'index.php'
) {
    require __DIR__ . '/../src/views/staff/scanqrpage.php';
    return;
}

http_response_code(404);
require __DIR__ . '/../src/views/404.php';

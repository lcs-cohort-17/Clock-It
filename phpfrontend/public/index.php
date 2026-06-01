<?php
// FRONTEND-ONLY ROUTER
// No Composer, no vendor folder, no backend models/controllers, no PHPUnit needed.
// This file only provides sample data so the PHP pages can display in the browser.

declare(strict_types=1);

session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$normalizedPath = rtrim($path, '/');

// Calculate the base URL for the 'public' directory
// e.g., /newapproach/Clock-It/phpfrontend/public
$public_base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Calculate the base URL for the 'phpfrontend' directory (parent of 'public' and 'api')
// e.g., /newapproach/Clock-It/phpfrontend
$phpfrontend_base_url = rtrim(dirname($public_base_url), '/\\');

// Simple fix for subdirectory hosting: 
// If the path ends with a known route, use that route.
if (str_ends_with($normalizedPath, '/admin/dashboard')) {
    $path = '/admin/dashboard';
} elseif ($normalizedPath === rtrim($public_base_url, '/')) {
    $path = '/';
} elseif ($normalizedPath !== '/' && str_ends_with($normalizedPath, '/index.php')) {
    $path = '/';
}

function view(string $view, array $data = []): void
{
    extract($data);
    require __DIR__ . '/../src/views/' . $view . '.php';
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

switch ($path) {
    case '/':
    case '/admin/dashboard':
        $title = 'Dashboard | Clock-It';
        view('admin/dashboard', compact('title', 'public_base_url', 'phpfrontend_base_url'));
        break;
    
        default:
        http_response_code(404);
        $title = 'Not Found | Clock-It';
        view('404', compact('title', 'public_base_url', 'phpfrontend_base_url'));
        break;    
}              
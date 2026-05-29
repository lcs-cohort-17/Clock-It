<?php
// FRONTEND-ONLY ROUTER
// No Composer, no vendor folder, no backend models/controllers, no PHPUnit needed.
// This file only provides sample data so the PHP pages can display in the browser.


declare(strict_types=1);

session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

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
        $title = 'User Management | Clock-It';
        view ('admin/usermanagement', compact ('title'));
        break;
        
    default:
        http_response_code(404);
        $title = 'Not Found | Clock-It';
        view('404', compact('title'));
        break;
}
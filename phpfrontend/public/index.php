<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

$sessionPath = dirname(__DIR__) . '/storage/sessions';

if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}

if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

session_start();

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$basePath = ($scriptDir === '' || $scriptDir === '.') ? '' : $scriptDir;

if ($basePath !== '' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath)) ?: '/';
}

if ($path === '/index.php') {
    $path = '/';
}

if (str_starts_with($path, '/index.php/')) {
    $path = substr($path, strlen('/index.php')) ?: '/';
}

function view(string $view, array $data = []): void
{
    global $basePath;

    $data = ['basePath' => $basePath] + $data;

    extract($data);

    require __DIR__ . '/../src/views/' . $view . '.php';
}

function layout_view(string $view, array $data = []): void
{
    global $basePath;

    $data = ['basePath' => $basePath] + $data;

    extract($data);

    ob_start();

    require __DIR__ . '/../src/views/' . $view . '.php';

    $content = ob_get_clean();

    require __DIR__ . '/../src/views/layouts/app.php';
}

function redirect_to(string $path): never
{
    global $basePath;

    header('Location: ' . $basePath . $path);

    exit;
}

switch ($path) {

    case '/':
        redirect_to('/admin-dashboard');
        break;

    case '/admin-dashboard':
        $title = 'Admin Dashboard | Clock-It';
        layout_view('admin/dashboard', ['title' => $title]);
        break;

    case '/staff-dashboard':
        $title = 'Staff Dashboard | Clock-It';
        view('staff/staff-dashboard');
        break;

    case '/testing':
        $title = 'Testing | Clock-It';
        view('admin/testing');
        break;

    case '/usermanagement':
        $title = 'User Management | Clock-It';
        view('admin/usermanagement', ['title' => $title]);
        break;

    default:
        http_response_code(404);
        view('404');
        break;
}
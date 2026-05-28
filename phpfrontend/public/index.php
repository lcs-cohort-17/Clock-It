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
        redirect_to('/admin-dashboard');
        
    case '/admin-dashboard':
        $title = 'Admin Dashboard | Clock-It';
        view('admin/admin-dashboard');
        break;
        
    case '/staff-dashboard':
        $title = 'Staff Dashboard | Clock-It';
        view('staff/staff-dashboard');
        break;
        
    case '/testing':
        $title = 'Testing | Clock-It';
        view('admin/testing');
        break;
        
    default:
        http_response_code(404);
        view('404');
}

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

$routes = require dirname(__DIR__) . '/src/web.php';
$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
$page = $_GET['page'] ?? ($path === '' ? 'dashboard' : $path);

if (!array_key_exists($page, $routes)) {
    $page = 'dashboard';
}

$route = $routes[$page];
$view = $page === 'dashboard'
    ? dirname(__DIR__) . '/src/views/admin/dashboard.php'
    : dirname(__DIR__) . '/src/views/page.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($route['title']) ?> | Clock It</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <script defer src="/assets/js/app.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  </head>
  <body>
    <main class="app-shell">
      <?php require $view; ?>
    </main>
  </body>
</html>

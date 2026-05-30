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
// FRONTEND-ONLY ROUTER
// No Composer, no vendor folder, no backend models/controllers, no PHPUnit needed.
// This file only provides sample data so the PHP pages can display in the browser.

declare(strict_types=1);

session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// $staffUser = [
//     'id' => 'staff-001',
//     'name' => 'Demo Staff',
//     'email' => 'staff@clockit.app',
//     'employeeId' => 'EMP-001',
//     'role' => 'staff',
// ];

// $adminUser = [
//     'id' => 'admin-001',
//     'name' => 'Demo Admin',
//     'email' => 'admin@clockit.app',
//     'employeeId' => 'ADM-001',
//     'role' => 'admin',
// ];

// $users = [
//     $staffUser,
//     ['id' => 'staff-002', 'name' => 'Anele Mokoena', 'email' => 'anele@clockit.app', 'employeeId' => 'EMP-002', 'role' => 'staff'],
//     ['id' => 'staff-003', 'name' => 'Lihle Dlamini', 'email' => 'lihle@clockit.app', 'employeeId' => 'EMP-003', 'role' => 'staff'],
//     $adminUser,
// ];

// $events = [
//     ['userName' => 'Demo Staff', 'type' => 'clock-in', 'timestamp' => date('Y-m-d') . ' 08:00'],
//     ['userName' => 'Anele Mokoena', 'type' => 'clock-in', 'timestamp' => date('Y-m-d') . ' 08:15'],
//     ['userName' => 'Lihle Dlamini', 'type' => 'clock-out', 'timestamp' => date('Y-m-d') . ' 16:02'],
// ];

// $stats = [
//     'currentlyOnsite' => 2,
//     'totalStaffToday' => 3,
//     'pendingSync' => 0,
//     'totalEvents' => count($events),
// ];

// $onsiteStaff = [
//     ['name' => 'Demo Staff', 'employeeId' => 'EMP-001', 'clockedInAt' => '08:00'],
//     ['name' => 'Anele Mokoena', 'employeeId' => 'EMP-002', 'clockedInAt' => '08:15'],
// ];

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

// // Frontend-only login simulation: no real authentication.
// if ($path === '/login' && $method === 'POST') {
//     $identifier = strtolower(trim((string) ($_POST['identifier'] ?? '')));
//     redirect_to(str_contains($identifier, 'admin') ? '/admin-dashboard' : '/staff-dashboard');
// }

// if ($path === '/logout') {
//     redirect_to('/');
// }

// if ($path === '/profile/password' && $method === 'POST') {
//     $_SESSION['flash'] = ['valid' => true, 'message' => 'Frontend demo: password form submitted successfully.'];
//     redirect_to('/profile');
// }

switch ($path) {
    // case '/':
    //     $title = 'Login | Clock-It';
    //     view('login', compact('title'));
    //     break;

    // case '/staff-dashboard':
    //     $title = 'Staff Dashboard | Clock-It';
    //     $user = $staffUser;
    //     view('staff/dashboard', compact('title', 'user', 'stats', 'events'));
    //     break;

    // case '/scan-qr':
    //     $title = 'Scan QR | Clock-It';
    //     $user = $staffUser;
    //     view('staff/scan_qr', compact('title', 'user'));
    //     break;

    // case '/history':
    //     $title = 'History | Clock-It';
    //     $user = $staffUser;
    //     view('staff/history', compact('title', 'user', 'events'));
    //     break;

    // case '/calendar':
    //     $title = 'Calendar | Clock-It';
    //     $user = $staffUser;
    //     view('staff/calendar', compact('title', 'user'));
    //     break;

    // case '/profile':
    //     $title = 'Profile | Clock-It';
    //     $user = $staffUser;
    //     view('staff/profile', compact('title', 'user'));
    //     break;

    // case '/admin-dashboard':
    //     $title = 'Admin Dashboard | Clock-It';
    //     $user = $adminUser;
    //     view('admin/dashboard', compact('title', 'user', 'stats', 'events', 'onsiteStaff'));
    //     break;

    // case '/admin-dashboard/users':
    //     $title = 'Users | Clock-It';
    //     $user = $adminUser;
    //     view('admin/users', compact('title', 'user', 'users'));
    //     break;

    case '/admin-dashboard/attendance':
        $title = 'Attendance | Clock-It';
        $user = $adminUser;
        view('admin/attendance_log', compact('title', 'user', 'events'));
        break;

    // case '/admin-dashboard/qr-generator':
    //     $title = 'QR Generator | Clock-It';
    //     $user = $adminUser;
    //     view('admin/qr_generator', compact('title', 'user'));
    //     break;

    // case '/admin-dashboard/settings':
    //     $title = 'Settings | Clock-It';
    //     $user = $adminUser;
    //     view('admin/settings', compact('title', 'user'));
    //     break;

    default:
        http_response_code(404);
        $title = 'Not Found | Clock-It';
        view('404', compact('title'));
        break;
}

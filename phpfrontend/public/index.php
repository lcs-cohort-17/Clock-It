<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

session_start();

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
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

function app_url(string $path = '/'): string
{
    global $basePath;

    return $basePath . '/' . ltrim($path, '/');
}

function view(string $view, array $data = []): void
{
    global $basePath;

    $data = ['basePath' => $basePath] + $data;
    extract($data);

    require __DIR__ . '/../src/views/' . $view . '.php';
}

function redirect_to(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

/*
|--------------------------------------------------------------------------
| DEMO USERS
|--------------------------------------------------------------------------
*/

$staffUser = [
    'id' => 'staff-001',
    'name' => 'Sarah Mthembu',
    'email' => 'sarah@clockit.app',
    'employeeId' => 'S-101',
    'role' => 'staff',
];

$adminUser = [
    'id' => 'admin-001',
    'name' => 'Priya Singh',
    'email' => 'admin@clockit.app',
    'employeeId' => 'A-001',
    'role' => 'admin',
];

$stats = [
    'currentlyOnsite' => 2,
    'totalStaffToday' => 15,
    'pendingSync' => 0,
    'totalEvents' => 42,
];

/*
|--------------------------------------------------------------------------
| ROUTES
|--------------------------------------------------------------------------
*/

switch ($path) {

    case '/':
        redirect_to('/admin-dashboard');
        break;

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

   case '/admin-dashboard/users':

    require_once dirname(__DIR__) . '/src/helpers/user-helper.php';
    require_once dirname(__DIR__) . '/src/controllers/UserController.php';

    $title = 'User Management';
    $user = $adminUser;
    $isAdminDashboard = true;

    view('admin/usermanagement', compact(
        'title',
        'user',
        'stats',
        'isAdminDashboard',
        'filtered'
    ));
    break;

    case '/admin-dashboard/users/clear-password':
        unset($_SESSION['generated_password']);
        redirect_to('/admin-dashboard/users');
        break;

    case '/admin-dashboard':
        $title = 'Admin Dashboard';
        $user = $adminUser;
        $isAdminDashboard = true;

        view('admin/admin-dashboard', compact(
            'title',
            'user',
            'stats',
            'isAdminDashboard'
        ));
        break;

    case '/admin-dashboard/attendance':
        $title = 'Attendance Logs';
        $user = $adminUser;
        $isAdminDashboard = true;

        view('admin/attendance_log', compact(
            'title',
            'user',
            'stats',
            'isAdminDashboard'
        ));
        break;

    // case '/admin-dashboard/testing':
    //     $title = 'Testing';
    //     $user = $adminUser;

    //     view('admin/testing', compact(
    //         'title',
    //         'user'
    //     ));
    //     break;

    /*
    |--------------------------------------------------------------------------
    | STAFF
    |--------------------------------------------------------------------------
    */

    case '/staff-dashboard':
        $title = 'Staff Dashboard';
        $user = $staffUser;
        $isAdminDashboard = false;

        view('staff/staff-dashboard', compact(
            'title',
            'user',
            'isAdminDashboard'
        ));
        break;

    case '/scan-qr':
        $title = 'Scan QR';
        $user = $staffUser;
        $isAdminDashboard = false;

        view('staff/scanqrpage', compact(
            'title',
            'user',
            'isAdminDashboard'
        ));
        break;

    case '/history':
        $title = 'Attendance History';
        $user = $staffUser;
        $isAdminDashboard = false;

        view('staff/AttendanceHistory', compact(
            'title',
            'user',
            'isAdminDashboard'
        ));
        break;

    case '/profile':
        $title = 'Profile';
        $user = $staffUser;
        $isAdminDashboard = false;

        view('staff/profile', compact(
            'title',
            'user',
            'isAdminDashboard'
        ));
        break;

    /*
    |--------------------------------------------------------------------------
    | 404
    |--------------------------------------------------------------------------
    */

    default:
        http_response_code(404);

        $title = '404 Not Found';

        view('404', compact('title'));
        break;
}

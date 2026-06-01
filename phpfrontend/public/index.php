<?php
declare(strict_types=1);
// FRONTEND-ONLY ROUTER
// No Composer, no vendor folder, no backend models/controllers, no PHPUnit needed.
// This file only provides sample data so the PHP pages can display in the browser.

session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$staffUser = [
    'id' => 'staff-001',
    'name' => 'Demo Staff',
    'email' => 'staff@clockit.app',
    'employeeId' => 'EMP-001',
    'role' => 'staff',
];

$adminUser = [
    'id' => 'admin-001',
    'name' => 'Demo Admin',
    'email' => 'admin@clockit.app',
    'employeeId' => 'ADM-001',
    'role' => 'admin',
];

$users = [
    $staffUser,
    ['id' => 'staff-002', 'name' => 'Anele Mokoena', 'email' => 'anele@clockit.app', 'employeeId' => 'EMP-002', 'role' => 'staff'],
    ['id' => 'staff-003', 'name' => 'Lihle Dlamini', 'email' => 'lihle@clockit.app', 'employeeId' => 'EMP-003', 'role' => 'staff'],
    $adminUser,
];

$events = [
    ['userName' => 'Demo Staff', 'type' => 'clock-in', 'timestamp' => date('Y-m-d') . ' 08:00'],
    ['userName' => 'Anele Mokoena', 'type' => 'clock-in', 'timestamp' => date('Y-m-d') . ' 08:15'],
    ['userName' => 'Lihle Dlamini', 'type' => 'clock-out', 'timestamp' => date('Y-m-d') . ' 16:02'],
];

$stats = [
    'currentlyOnsite' => 2,
    'totalStaffToday' => 3,
    'pendingSync' => 0,
    'totalEvents' => count($events),
];

$onsiteStaff = [
    ['name' => 'Demo Staff', 'employeeId' => 'EMP-001', 'clockedInAt' => '08:00'],
    ['name' => 'Anele Mokoena', 'employeeId' => 'EMP-002', 'clockedInAt' => '08:15'],
];

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

// Frontend-only login simulation: no real authentication.
if ($path === '/login' && $method === 'POST') {
    $identifier = strtolower(trim((string) ($_POST['identifier'] ?? '')));
    redirect_to(str_contains($identifier, 'admin') ? '/admin-dashboard' : '/staff-dashboard');
}

if ($path === '/logout') {
    redirect_to('/');
}

if ($path === '/profile/password' && $method === 'POST') {
    $_SESSION['flash'] = ['valid' => true, 'message' => 'Frontend demo: password form submitted successfully.'];
    redirect_to('/profile');
}

switch ($path) {
    case '/':
        $title = 'Login | Clock-It';
        view('login', compact('title'));
        break;

    case '/staff-dashboard':
        $title = 'Staff Dashboard | Clock-It';
        $user = $staffUser;
        view('staff/dashboard', compact('title', 'user', 'stats', 'events'));
        break;

        case '/password':
        require __DIR__ . '/../src/views/staff/password/password.php';
        break;

    case '/scan-qr':
        $title = 'Scan QR | Clock-It';
        $user = $staffUser;
        view('staff/scan_qr', compact('title', 'user'));
        break;

    case '/history':
        $title = 'History | Clock-It';
        $user = $staffUser;
        view('staff/history', compact('title', 'user', 'events'));
        break;

    case '/calendar':
        $title = 'Calendar | Clock-It';
        $user = $staffUser;
        view('staff/calendar', compact('title', 'user'));
        break;

    case '/profile':
        $title = 'Profile | Clock-It';
        $user = $staffUser;
        view('staff/profile', compact('title', 'user'));
        break;

    case '/admin-dashboard':
        $title = 'Admin Dashboard | Clock-It';
        $user = $adminUser;
        view('admin/dashboard', compact('title', 'user', 'stats', 'events', 'onsiteStaff'));
        break;

    case '/admin-dashboard/users':
        $title = 'Users | Clock-It';
        $user = $adminUser;
        view('admin/users', compact('title', 'user', 'users'));
        break;

    case '/admin-dashboard/attendance':
        $title = 'Attendance | Clock-It';
        $user = $adminUser;
        view('admin/attendance', compact('title', 'user', 'events'));
        break;

    case '/admin-dashboard/qr-generator':
        $title = 'QR Generator | Clock-It';
        $user = $adminUser;
        view('admin/qr_generator', compact('title', 'user'));
        break;

    case '/admin-dashboard/settings':
        $title = 'Settings | Clock-It';
        $user = $adminUser;
        view('admin/settings', compact('title', 'user'));
        break;

    default:
        http_response_code(404);
        $title = 'Not Found | Clock-It';
        view('404', compact('title'));
        break;
}
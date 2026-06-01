<?php
// FRONTEND-ONLY ROUTER
// No Composer, no vendor folder, no backend models/controllers, no PHPUnit needed.
// This file only provides sample data so the PHP pages can display in the browser.

declare(strict_types=1);

session_start();

$clockitPublicBase = $clockitPublicBase ?? '';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($path === '/index.php') {
    $path = '/';
} elseif (str_starts_with($path, '/index.php/')) {
    $path = substr($path, strlen('/index.php'));
}

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

$mockUsers = require __DIR__ . '/../src/data/MockUsers.php';

// Device assignment for this demo device. Default to 'staff'.
// You can override for testing with `?device=admin` or `?device=staff` in the URL.
if (isset($_GET['device'])) {
    $requested = (string) ($_GET['device'] ?? '');
    if (in_array($requested, ['staff', 'admin', 'any'], true)) {
        $_SESSION['device_assigned'] = $requested;
    }
}
$deviceAssigned = $_SESSION['device_assigned'] ?? 'staff';

function view(string $view, array $data = []): void
{
    extract($data);
    require __DIR__ . '/../src/views/' . $view . '.php';
}

function clockit_asset(string $path): string
{
    global $clockitPublicBase;

    return rtrim($clockitPublicBase, '/') . '/' . ltrim($path, '/');
}

function clockit_route(string $path): string
{
    $path = '/' . ltrim($path, '/');

    if ($path === '/index.php' || str_starts_with($path, '/index.php/')) {
        return $path;
    }

    return $path === '/' ? '/index.php' : '/index.php' . $path;
}

if (!function_exists('clockit_asset')) {
    function clockit_asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('clockit_route')) {
    function clockit_route(string $path): string
    {
        return $path === '/' ? '/' : '/' . ltrim($path, '/');
    }
}

function redirect_to(string $path): never
{
    header('Location: ' . clockit_route($path));
    exit;
}

function json_response(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function request_data(): array
{
    $rawBody = file_get_contents('php://input');
    $json = $rawBody !== false && $rawBody !== '' ? json_decode($rawBody, true) : null;

    return is_array($json) ? $json : $_POST;
}

function find_demo_user(array $users, string $email, string $password): ?array
{
    $email = strtolower(trim($email));

    foreach ($users as $user) {
        $userEmail = strtolower((string) ($user['email'] ?? ''));
        $userPassword = (string) ($user['password'] ?? '');

        if ($userEmail === $email && $userPassword === $password) {
            return $user;
        }
    }

    return null;
}

function find_demo_user_by_employee_id(array $users, string $employeeId): ?array
{
    $employeeId = strtolower(trim($employeeId));

    foreach ($users as $user) {
        $userEmployeeId = strtolower((string) ($user['employeeId'] ?? ''));

        if ($userEmployeeId === $employeeId) {
            return $user;
        }
    }

    return null;
}

function dashboard_route_for(array $user): string
{
    $path = strtolower((string) ($user['role'] ?? 'staff')) === 'admin'
        ? '/admin-dashboard'
        : '/staff-dashboard';

    return clockit_route($path);
}

// Frontend-only API placeholders. They let the login screen behave like it is
// calling BE-01 without adding real authentication or password reset behavior.
if ($path === '/api/forgot-password' && $method === 'POST') {
    json_response([
        'success' => true,
        'message' => "If that email exists in our system, we've sent a password reset link.",
    ]);
}

if ($path === '/api/login' && $method === 'POST') {
    $payload = request_data();
    $loginMethod = (string) ($payload['loginMethod'] ?? 'email');
    $email = (string) ($payload['email'] ?? '');
    $password = (string) ($payload['password'] ?? '');
    $employeeId = (string) ($payload['employeeId'] ?? '');
    $user = $loginMethod === 'employeeId'
        ? find_demo_user_by_employee_id($mockUsers, $employeeId)
        : find_demo_user($mockUsers, $email, $password);

    if ($user === null) {
        json_response([
            'success' => false,
            'message' => 'Invalid email or password.',
        ], 401);
    }

    // Persist a simple demo session for route protection and role checks.
    $_SESSION['current_user'] = $user;

    json_response([
        'success' => true,
        'role' => $user['role'] ?? 'staff',
        'redirect' => dashboard_route_for($user),
    ]);
}

// Social login (demo): accept provider + email and only sign in if the mocked
// account exists and matches the device assignment (so admin-only accounts
// cannot be used on a device assigned to a staff member).
if ($path === '/api/social-login' && $method === 'POST') {
    $payload = request_data();
    $provider = (string) ($payload['provider'] ?? '');
    $email = strtolower(trim((string) ($payload['email'] ?? '')));

    $user = null;
    foreach ($mockUsers as $u) {
        $uEmail = strtolower((string) ($u['email'] ?? ''));
        $msEmail = strtolower((string) ($u['microsoftEmail'] ?? ''));
        if ($msEmail && $msEmail === $email) { $user = $u; break; }
        if ($uEmail && $uEmail === $email) { $user = $u; break; }
    }

    if ($user === null) {
        json_response(['success' => false, 'message' => 'No matching account found for that social login.'], 401);
    }

    // Enforce device assignment: if this device is for staff only, block admin accounts.
    $deviceAssigned = $_SESSION['device_assigned'] ?? 'staff';
    if ($deviceAssigned === 'staff' && strtolower((string) ($user['role'] ?? '')) === 'admin') {
        json_response(['success' => false, 'message' => 'This device is assigned to staff only.'], 403);
    }

    // Set session and return redirect target
    $_SESSION['current_user'] = $user;
    json_response(['success' => true, 'redirect' => dashboard_route_for($user), 'role' => $user['role'] ?? 'staff']);
}

if ($path === '/api/logout' && $method === 'POST') {
    unset($_SESSION['current_user']);
    json_response(['success' => true]);
}

// Frontend-only non-JavaScript fallback: no real authentication.
if ($path === '/login' && $method === 'POST') {
    $user = find_demo_user($mockUsers, (string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));
    redirect_to($user ? dashboard_route_for($user) : '/');
}

if ($path === '/logout') {
    redirect_to('/');
}

// Protect admin UI routes: require a demo session with `role` === 'admin'.
if (strpos($path, '/admin-dashboard') === 0) {
    $current = $_SESSION['current_user'] ?? null;
    $role = $current['role'] ?? null;

    if (!($current && strtolower((string) $role) === 'admin')) {
        if ($current && strtolower((string) $role) === 'staff') {
            redirect_to('/staff-dashboard');
        }

        redirect_to('/');
    }
}

if ($path === '/profile/password' && $method === 'POST') {
    $_SESSION['flash'] = ['valid' => true, 'message' => 'Frontend demo: password form submitted successfully.'];
    redirect_to('/profile');
}

switch ($path) {
    case '/':
    case '/login':
        $title = 'Login | Clock-It';
        view('Login', compact('title', 'deviceAssigned'));
        break;

    case '/staff-dashboard':
        $title = 'Staff Dashboard | Clock-It';
        $user = $staffUser;
        view('staff/dashboard', compact('title', 'user', 'stats', 'events'));
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

<?php
// FRONTEND-ONLY ROUTER
// This file provides sample data and small demo endpoints so the PHP pages can display in the browser.

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$sessionPath = dirname(__DIR__) . '/storage/sessions';
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

session_start();

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$baseUrl = $baseUrl === '' ? '' : $baseUrl;
$GLOBALS['baseUrl'] = $baseUrl;

function serve_public_asset_if_requested(string $requestPath, string $baseUrl): void
{
    $prefixes = array_filter([
        ($baseUrl !== '' ? $baseUrl : '') . '/assets/',
        '/public/assets/',
        '/assets/',
    ]);

    foreach (array_unique($prefixes) as $prefix) {
        if (!str_starts_with($requestPath, $prefix)) {
            continue;
        }

        $relativePath = substr($requestPath, strlen($prefix));
        $assetRoot = realpath(__DIR__ . '/assets');
        $assetPath = realpath(__DIR__ . '/assets/' . $relativePath);

        if ($assetRoot === false || $assetPath === false || !str_starts_with($assetPath, $assetRoot) || !is_file($assetPath)) {
            http_response_code(404);
            exit;
        }

        $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
        $contentTypes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
        ];

        header('Content-Type: ' . ($contentTypes[$extension] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=3600');
        readfile($assetPath);
        exit;
    }
}

serve_public_asset_if_requested($requestPath, $baseUrl);

if (str_starts_with($requestPath, $scriptName)) {
    $path = substr($requestPath, strlen($scriptName)) ?: '/';
} elseif ($baseUrl !== '' && str_starts_with($requestPath, $baseUrl)) {
    $path = substr($requestPath, strlen($baseUrl)) ?: '/';
} else {
    $path = $requestPath;
}

$path = '/' . trim($path, '/');
$path = $path === '/' ? '/' : rtrim($path, '/');
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

function route_url(string $path = '/'): string
{
    $path = '/' . trim($path, '/');
    $path = $path === '/' ? '/' : $path;

    return ($GLOBALS['baseUrl'] ?? '') . '/index.php' . ($path === '/' ? '' : $path);
}

function asset_url(string $path): string
{
    return ($GLOBALS['baseUrl'] ?? '') . '/assets/' . ltrim($path, '/');
}

function redirect_to(string $path): never
{
    header('Location: ' . route_url($path));
    exit;
}

function login_as(array $user): void
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
}

function json_response(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function request_json(): array
{
    $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
    return is_array($payload) ? $payload : [];
}

function generate_qr_data_url(string $text): string
{
    $renderer = new BaconQrCode\Renderer\ImageRenderer(
        new BaconQrCode\Renderer\RendererStyle\RendererStyle(512, 4),
        new BaconQrCode\Renderer\Image\SvgImageBackEnd()
    );
    $writer = new BaconQrCode\Writer($renderer);
    $svg = $writer->writeString($text);

    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

if ($path === '/api/qr-code' && $method === 'POST') {
    $payload = request_json();
    $text = trim((string) ($payload['text'] ?? ''));

    if ($text === '') {
        json_response(['error' => 'QR text is required.'], 422);
    }

    json_response(['imageUrl' => generate_qr_data_url($text)]);
}

function send_user_invite_email(string $name, string $email, string $role, string $password): bool
{
    $subject = 'Clock-It Login Details';
    $message = implode("\n\n", [
        "Hi {$name}",
        "Role: {$role}",
        "Here is your generated password you can login with: {$password}",
        'You can change it once successfully logged in.',
        "If you are not {$name} kindly ignore this message.",
        'Team Clock It Team.',
    ]);
    $headers = [
        'From: Clock-It <no-reply@clock-it.local>',
        'Reply-To: no-reply@clock-it.local',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    return @mail($email, $subject, $message, implode("\r\n", $headers));
}

if ($path === '/api/users/invite' && $method === 'POST') {
    $payload = request_json();
    $name = trim((string) ($payload['name'] ?? ''));
    $email = trim((string) ($payload['email'] ?? ''));
    $role = trim((string) ($payload['role'] ?? ''));
    $password = trim((string) ($payload['password'] ?? ''));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $role === '' || $password === '') {
        json_response(['error' => 'Name, valid email, role, and password are required.'], 422);
    }

    if (!send_user_invite_email($name, $email, $role, $password)) {
        json_response(['error' => 'User was added, but PHP mail is not configured or could not send the invite email.'], 500);
    }

    json_response(['sent' => true]);
}

// Frontend-only login simulation: no real authentication.
if ($path === '/login' && $method === 'POST') {
    $identifier = strtolower(trim((string) ($_POST['identifier'] ?? '')));
    redirect_to(str_contains($identifier, 'admin') ? '/admin-dashboard' : '/staff-dashboard');
}

if ($path === '/auth/google' && $method === 'POST') {
    redirect_to('/staff-dashboard');
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
    case '/login':
        $title = 'Login | Clock-It';
        view('login', compact('title'));
        break;

    case '/staff-dashboard':
    case '/dashboard.php':
        $title = 'Staff Dashboard | Clock-It';
        $user = $staffUser;
        login_as($user);
        view('staff/dashboard', compact('title', 'user', 'stats', 'events'));
        break;

    case '/scan-qr':
    case '/scan-qr.php':
        $title = 'Scan QR | Clock-It';
        $user = $staffUser;
        login_as($user);
        view('staff/scan-qr', compact('title', 'user'));
        break;

    case '/history':
    case '/history.php':
        $title = 'History | Clock-It';
        $user = $staffUser;
        login_as($user);
        view('staff/history', compact('title', 'user', 'events'));
        break;

    case '/calendar':
    case '/calendar.php':
        $title = 'Calendar | Clock-It';
        $user = $staffUser;
        login_as($user);
        view('staff/calendar', compact('title', 'user'));
        break;

    case '/leave-requests':
    case '/leave-requests.php':
        $title = 'Leave Requests | Clock-It';
        $user = $staffUser;
        login_as($user);
        view('staff/leave-requests', compact('title', 'user'));
        break;

    case '/profile':
    case '/profile.php':
        $title = 'Profile | Clock-It';
        $user = $staffUser;
        login_as($user);
        view('staff/profile', compact('title', 'user'));
        break;

    case '/admin-dashboard':
    case '/admin/dashboard.php':
        $title = 'Admin Dashboard | Clock-It';
        $user = $adminUser;
        login_as($user);
        view('admin/dashboard', compact('title', 'user', 'stats', 'events', 'onsiteStaff'));
        break;

    case '/admin-dashboard/users':
    case '/admin/users.php':
        $title = 'Users | Clock-It';
        $user = $adminUser;
        login_as($user);
        view('admin/users', compact('title', 'user', 'users'));
        break;

    case '/admin-dashboard/attendance':
    case '/admin/attendance.php':
        $title = 'Attendance | Clock-It';
        $user = $adminUser;
        login_as($user);
        view('admin/attendance', compact('title', 'user', 'events'));
        break;

    case '/admin-dashboard/qr-generator':
    case '/admin/qr.php':
        $title = 'QR Generator | Clock-It';
        $user = $adminUser;
        login_as($user);
        view('admin/qr', compact('title', 'user'));
        break;

    case '/admin-dashboard/settings':
    case '/admin/settings.php':
        $title = 'Settings | Clock-It';
        $user = $adminUser;
        login_as($user);
        view('admin/settings', compact('title', 'user'));
        break;

    default:
        http_response_code(404);
        $title = 'Not Found | Clock-It';
        view('404', compact('title'));
        break;
}

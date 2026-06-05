<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $publicFile = realpath(__DIR__ . $requestedPath);

    if (
        $requestedPath !== '/'
        && $publicFile !== false
        && str_starts_with($publicFile, __DIR__ . DIRECTORY_SEPARATOR)
        && is_file($publicFile)
    ) {
        return false;
    }
}

$frontendRoot = dirname(__DIR__);

require $frontendRoot . '/src/bootstrap.php';
require_once __DIR__ . '/api/backend_proxy.php';

session_start();

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$basePath = PHP_SAPI === 'cli-server' || $scriptDir === '' || $scriptDir === '.'
    ? ''
    : $scriptDir;

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
    global $basePath, $frontendRoot;

    $data = ['basePath' => $basePath] + $data;
    extract($data);

    require $frontendRoot . '/src/views/' . $view . '.php';
}

function redirect_to(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function login_json_response(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_THROW_ON_ERROR);
    exit;
}

function login_request_data(): array
{
    $body = file_get_contents('php://input');
    $json = $body !== false && $body !== '' ? json_decode($body, true) : null;
    return is_array($json) ? $json : $_POST;
}

function dashboard_path_for(array $user): string
{
    $role = strtolower((string) ($user['role_key'] ?? $user['role'] ?? 'staff'));
    return $role === 'admin' ? '/admin-dashboard' : '/staff-dashboard';
}

function current_user(): ?array
{
    return isset($_SESSION['current_user']) && is_array($_SESSION['current_user'])
        ? $_SESSION['current_user']
        : null;
}

function require_auth(?string $role = null): array
{
    $user = current_user();

    if (!$user) {
        redirect_to('/login');
    }

    if ($role !== null) {
        $userRole = strtolower((string) ($user['role_key'] ?? $user['role'] ?? 'staff'));
        if ($userRole !== strtolower($role)) {
            redirect_to(dashboard_path_for($user));
        }
    }

    return $user;
}

function frontend_stats(): array
{
    $payload = clockit_backend_api_get('/api/admin/stats');
    $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;

    return [
        'currentlyOnsite' => (int) ($data['currentlyOnsite'] ?? 0),
        'totalStaffToday' => (int) ($data['totalStaffToday'] ?? 0),
        'pendingSync' => (int) ($data['pendingSync'] ?? 0),
        'totalEvents' => (int) ($data['totalEvents'] ?? 0),
    ];
}

function admin_settings_file(): string
{
    global $frontendRoot;
    return $frontendRoot . '/storage/settings.json';
}

function admin_settings_read(): array
{
    $file = admin_settings_file();

    if (!file_exists($file)) {
        return [
            'session_timeout' => 30,
            'data_retention_days' => 90,
        ];
    }

    $settings = json_decode((string) file_get_contents($file), true);

    return is_array($settings) ? $settings : [
        'session_timeout' => 30,
        'data_retention_days' => 90,
    ];
}

function admin_settings_write(array $settings): void
{
    file_put_contents(admin_settings_file(), json_encode($settings, JSON_THROW_ON_ERROR));
}

function proxy_backend_json(string $method, string $backendPath, array $body = [], array $query = []): never
{
    $response = clockit_backend_api_request($method, $backendPath, $body, $query);
    $backendStatus = clockit_backend_response_status();
    login_json_response($response, $backendStatus >= 400 ? $backendStatus : 200);
}

$loginUsers = [];

if (preg_match('#^/api/admin/attendance/([^/]+)$#', $path, $matches)) {
    require_auth('admin');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true)) {
        proxy_backend_json($method, '/api/admin/attendance/' . rawurlencode($matches[1]), login_request_data());
    }
    login_json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
}

switch ($path) {
    case '/':
        redirect_to(current_user() ? dashboard_path_for(current_user()) : '/login');
        break;

    case '/login':
        if (current_user()) {
            redirect_to(dashboard_path_for(current_user()));
        }

        $title = 'Login | Clock-It';
        view('login', compact('title', 'loginUsers'));
        break;

    case '/logout':
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $cookieParams = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookieParams['path'],
                'domain' => $cookieParams['domain'],
                'secure' => $cookieParams['secure'],
                'httponly' => $cookieParams['httponly'],
                'samesite' => $cookieParams['samesite'],
            ]);
        }

        session_destroy();
        redirect_to('/login');
        break;

    case '/api/login':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            login_json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
        }

        $payload = login_request_data();
        $response = clockit_backend_api_post('/api/login', $payload);
        $backendStatus = clockit_backend_response_status();

        if (!($response['success'] ?? false)) {
            login_json_response([
                'success' => false,
                'message' => $response['message'] ?? 'Invalid email or password.',
            ], $backendStatus >= 400 ? $backendStatus : 401);
        }

        $_SESSION['current_user'] = $response['user'] ?? [];
        $_SESSION['api_token'] = $response['token'] ?? null;

        login_json_response([
            'success' => true,
            'role' => $response['role'] ?? ($_SESSION['current_user']['role_key'] ?? 'staff'),
            'redirect' => app_url(dashboard_path_for($_SESSION['current_user'])),
        ]);
        break;

    case '/api/forgot-password':
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            login_json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
        }

        login_json_response([
            'success' => true,
            'message' => 'Password reset flow should be handled by the backend/email service later.',
        ]);
        break;

    case '/api/social-login':
        login_json_response([
            'success' => false,
            'message' => 'Social login is disabled in this service-account setup. Use email or employee ID login.',
        ], 400);
        break;

    case '/api/admin/settings':
        require_auth('admin');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET') {
            login_json_response(admin_settings_read());
        }

        if ($method !== 'PUT') {
            login_json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $payload = login_request_data();
        $sessionTimeout = (int) ($payload['session_timeout'] ?? 0);
        $retentionDays = (int) ($payload['data_retention_days'] ?? 0);

        if ($sessionTimeout <= 0 || $retentionDays <= 0) {
            login_json_response(['success' => false, 'error' => 'Values must be positive integers.'], 400);
        }

        admin_settings_write([
            'session_timeout' => $sessionTimeout,
            'data_retention_days' => $retentionDays,
        ]);

        login_json_response(['success' => true, 'message' => 'Settings saved.']);
        break;

    case '/api/admin/sheets/status':
    case '/api/admin/sheets/settings':
        require_auth('admin');
        proxy_backend_json('GET', $path);
        break;

    case '/api/admin/sheets/export':
    case '/api/admin/sheets/import':
    case '/api/admin/sheets/sync':
    case '/api/admin/sheets/push':
    case '/api/admin/sheets/connect':
    case '/api/admin/sheets/disconnect':
        require_auth('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            login_json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
        }
        proxy_backend_json('POST', $path, login_request_data());
        break;

    case '/api/admin/attendance':
        require_auth('admin');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'GET') {
            proxy_backend_json('GET', '/api/admin/attendance', [], $_GET);
        }
        if ($method === 'POST') {
            proxy_backend_json('POST', '/api/admin/attendance', login_request_data());
        }
        login_json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
        break;

    case '/api/admin/data-retention/purge':
        require_auth('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            login_json_response(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $settings = admin_settings_read();
        $retentionDays = (int) ($settings['data_retention_days'] ?? 90);
        $purgeLog = $frontendRoot . '/storage/purge_log.txt';
        file_put_contents($purgeLog, date('Y-m-d H:i:s') . " - Purged records older than {$retentionDays} days\n", FILE_APPEND);

        login_json_response([
            'success' => true,
            'message' => "Purge request recorded for records older than {$retentionDays} days.",
        ]);
        break;

    case '/admin-dashboard/users':
        $user = require_auth('admin');
        require_once $frontendRoot . '/src/helpers/user-helper.php';
        require_once $frontendRoot . '/src/controllers/UserController.php';

        $title = 'User Management';
        $stats = frontend_stats();
        $isAdminDashboard = true;

        view('admin/usermanagement', compact('title', 'user', 'stats', 'isAdminDashboard', 'filtered'));
        break;

    case '/admin-dashboard/users/clear-password':
        require_auth('admin');
        unset($_SESSION['generated_password']);
        redirect_to('/admin-dashboard/users');
        break;

    case '/admin-dashboard':
        $user = require_auth('admin');
        $title = 'Admin Dashboard';
        $stats = frontend_stats();
        $isAdminDashboard = true;

        view('admin/admin-dashboard', compact('title', 'user', 'stats', 'isAdminDashboard'));
        break;

    case '/admin-dashboard/attendance':
        $user = require_auth('admin');
        $title = 'Attendance Logs';
        $stats = frontend_stats();
        $isAdminDashboard = true;

        view('admin/attendance_log', compact('title', 'user', 'stats', 'isAdminDashboard'));
        break;

    case '/admin-dashboard/settings':
        $user = require_auth('admin');
        $title = 'Admin Settings';
        $isAdminDashboard = true;

        view('admin/admin_settings', compact('title', 'user', 'isAdminDashboard'));
        break;

    case '/staff-dashboard':
        $user = require_auth('staff');
        $title = 'Staff Dashboard';
        $isAdminDashboard = false;

        view('staff/staff-dashboard', compact('title', 'user', 'isAdminDashboard'));
        break;

    case '/scan-qr':
        $user = require_auth();
        $title = 'Scan QR';
        $isAdminDashboard = strtolower((string) ($user['role_key'] ?? $user['role'] ?? 'staff')) === 'admin';

        view('staff/scanqrpage', compact('title', 'user', 'isAdminDashboard'));
        break;

    case '/history':
        $user = require_auth('staff');
        $title = 'Attendance History';
        $isAdminDashboard = false;

        view('staff/AttendanceHistory', compact('title', 'user', 'isAdminDashboard'));
        break;

    case '/profile':
        $user = require_auth();
        $title = 'Profile';
        $isAdminDashboard = strtolower((string) ($user['role_key'] ?? $user['role'] ?? 'staff')) === 'admin';

        view('staff/profile', compact('title', 'user', 'isAdminDashboard'));
        break;

    default:
        http_response_code(404);
        $title = '404 Not Found';
        view('404', compact('title'));
        break;
}

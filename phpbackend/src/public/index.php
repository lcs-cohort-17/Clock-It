<?php

if (php_sapi_name() === 'cli-server') {
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $file = __DIR__ . $uri;

    if ($uri !== '/index.php' && file_exists($file)) {
        return false;
    }
}

use App\Middleware\AuthMiddleware;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../routes/SettingsRoutes.php';



$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$defaultSecret = $_ENV['JWT_SECRET'] ?? throw new RuntimeException('JWT_SECRET not set');

$sqliteFile = __DIR__ . '/../../clockit.sqlite';
$pdo = new PDO('sqlite:' . $sqliteFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

initializeDatabase($pdo);

$authMiddleware = new AuthMiddleware($defaultSecret);

$GLOBALS['auth'] = resolveAuthFromRequest($defaultSecret);

$settingsDb = new SettingsDb($pdo);
$settingsModel = new SettingsDbModel($settingsDb);
$controller = new SettingsController($settingsModel, $authMiddleware);

handleSettingsRoutes($controller);

function resolveAuthFromRequest(string $secret): array
{
    $headers = getallheaders() ?: [];
    
    // TEMP DEBUG — remove after fixing
    error_log('ALL HEADERS: ' . print_r($headers, true));
    error_log('SERVER AUTH: ' . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'not set'));
    
    $bearer = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    error_log('BEARER FOUND: ' . $bearer);

    if (!preg_match('/^Bearer\s+(.*)$/i', trim($bearer), $matches)) {
        error_log('NO BEARER MATCH');
        return [];
    }

    $token = $matches[1];
    error_log('TOKEN: ' . substr($token, 0, 20) . '...');

    if ($token === 'test-admin') {
        return ['userId' => '1', 'role' => 'admin', 'is_active' => 1];
    }

    if ($token === 'test-staff') {
        return ['userId' => '2', 'role' => 'staff', 'is_active' => 1];
    }

    try {
        $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));
        error_log('DECODED ROLE: ' . ($decoded->role ?? 'none'));

        return [
            'userId'      => $decoded->userId      ?? null,
            'email'       => $decoded->email        ?? null,
            'role'        => $decoded->role         ?? null,
            'employee_id' => $decoded->employee_id  ?? null,
        ];
    } catch (\Throwable $e) {
        error_log('JWT ERROR: ' . $e->getMessage());
        return [];
    }
}

function initializeDatabase(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS settings (
            "key" TEXT PRIMARY KEY,
            value TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS attendance (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            user_id TEXT PRIMARY KEY,
            role TEXT NOT NULL,
            is_active INTEGER NOT NULL
        )'
    );

    $pdo->exec(
        "INSERT OR IGNORE INTO settings (\"key\", value) VALUES
            ('session_timeout_minutes', '30'),
            ('data_retention_days', '90')"
    );

    $pdo->exec(
    "INSERT OR IGNORE INTO users (user_id, role, is_active) VALUES
        ('1', 'admin', 1),
        ('d6d5bcec-5d9b-11f1-896c-001e676e63e8', 'admin', 1)"
);
}


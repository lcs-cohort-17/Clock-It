<?php

if (php_sapi_name() === 'cli-server') {
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $file = __DIR__ . $uri;

    if ($uri !== '/index.php' && file_exists($file)) {
        return false;
    }
}

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../routes/SettingsRoutes.php';

use App\Middleware\AuthMiddleware;

$sqliteFile = __DIR__ . '/../../clockit.sqlite';
$pdo = new PDO('sqlite:' . $sqliteFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

initializeDatabase($pdo);

$defaultSecret = getenv('JWT_SECRET') ?: 'clockit-secret';
$authMiddleware = new AuthMiddleware($defaultSecret);

$GLOBALS['auth'] = resolveAuthFromRequest();

$settingsDb = new SettingsDb($pdo);
$settingsModel = new SettingsDbModel($settingsDb);
$controller = new SettingsController($settingsModel, $authMiddleware);

handleSettingsRoutes($controller);

function resolveAuthFromRequest(): array
{
    $headers = getallheaders() ?: [];
    $bearer = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (preg_match('/^Bearer\s+(.*)$/i', trim($bearer), $matches)) {
        $token = $matches[1];

        if ($token === 'test-admin') {
            return ['userId' => '1', 'role' => 'admin', 'is_active' => 1];
        }

        if ($token === 'test-staff') {
            return ['userId' => '2', 'role' => 'staff', 'is_active' => 1];
        }
    }

    return [];
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
        "INSERT OR IGNORE INTO settings (\"key\", value) VALUES
            ('session_timeout_minutes', '30'),
            ('data_retention_days', '90')"
    );
}

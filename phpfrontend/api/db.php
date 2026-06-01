<?php
// Database configuration
$host = 'localhost';
$db   = 'clock_it';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Ensure PHP and MySQL are using the same timezone (adjust to your local zone)
    date_default_timezone_set('UTC');
    $pdo->exec("SET time_zone = '+00:00'");
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

function checkAdminRole() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Temporary: Auto-login as admin for local development
    $_SESSION['user_role'] = $_SESSION['user_role'] ?? 'admin';

    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin role required']);
        exit;
    }
}
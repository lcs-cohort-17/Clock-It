<?php

use App\Models\QrCodeDb;
use Middleware\AuthMiddleware;
use Config\Database;

// INIT
$db = Database::getInstance()->getConnection();
$jwtSecret = $_ENV['JWT_SECRET'] ?? 'dev-secret';

$auth = new AuthMiddleware($jwtSecret, $db);
$qrModel = new QrCodeDb();

// ===============================
// ADMIN: GENERATE QR TOKEN
// ===============================
if ($method === 'POST' && $path === '/admin/qr/generate') {

    $authResult = $auth->requireAdmin($request);
    if ($authResult) {
        http_response_code($authResult['status']);
        echo json_encode($authResult['body']);
        exit;
    }

    $body = $request['body'];

    $type = $body['type'] ?? null; // clock_in or clock_out

    if (!$type || !in_array($type, ['clock_in', 'clock_out'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid QR type'
        ]);
        exit;
    }

    $token = bin2hex(random_bytes(16)); // 32-char token

    $userId = $request['user']['user_id'] ?? null;

    $qrModel->createToken($token, $type, $userId);

    echo json_encode([
        'success' => true,
        'token' => $token,
        'type' => $type,
        'expires_in' => 60
    ]);
    exit;
}

// ===============================
// PUBLIC: VALIDATE QR TOKEN
// ===============================
if ($method === 'POST' && $path === '/api/scan/validate') {

    $body = $request['body'];
    $token = $body['qr_token'] ?? null;

    if (!$token) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'QR token required'
        ]);
        exit;
    }

    $qr = $qrModel->validateAndUseToken($token);

    if (!$qr) {
        http_response_code(410); // expired or used
        echo json_encode([
            'success' => false,
            'error' => 'QR code invalid, expired, or already used'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'type' => $qr['type'],
        'message' => 'QR valid'
    ]);
    exit;
}
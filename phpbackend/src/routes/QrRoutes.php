<?php

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Clean the URI (remove query strings)
$requestUri = strtok($requestUri, '?');

if ($requestUri === '/api/attendance/clock' && $method === 'POST') {
    require_once __DIR__ . '/routes/api.php';
} else {
    http_response_code(404);
    echo json_encode([
        'error' => 'Not Found',
        'message' => 'The endpoint ' . $requestUri . ' does not exist'
    ]);
}
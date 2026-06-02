<?php

declare(strict_types=1);

require __DIR__ . '/backend_proxy.php';

header('Content-Type: application/json; charset=utf-8');

$payload = clockit_backend_api_get('/api/admin/recent-activity');
$data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

echo json_encode([
    'data' => $data,
    'meta' => [
        'count' => count($data),
        'limit' => 10,
        'source' => 'backend',
        'generatedAt' => date(DATE_ATOM),
    ],
], JSON_THROW_ON_ERROR);


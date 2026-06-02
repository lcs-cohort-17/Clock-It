<?php

declare(strict_types=1);

function clockit_backend_base_url(): string
{
    $configured = rtrim((string) getenv('CLOCKIT_BACKEND_URL'), '/');

    if ($configured !== '') {
        return $configured;
    }

    $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return "{$scheme}://{$host}/phpbackend/src/public/index.php";
}

function clockit_backend_api_get(string $path): array
{
    $url = clockit_backend_base_url() . '/' . ltrim($path, '/');
    $context = stream_context_create([
        'http' => [
            'header' => "Accept: application/json\r\n",
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);

    $response = file_get_contents($url, false, $context);
    $payload = $response !== false ? json_decode($response, true) : null;

    return is_array($payload) ? $payload : [];
}

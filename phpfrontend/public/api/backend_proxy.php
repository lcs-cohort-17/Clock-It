<?php

declare(strict_types=1);

$clockitBackendLastStatus = 0;

function clockit_backend_base_url(): string
{
    $configured = rtrim((string) ($_ENV['CLOCKIT_BACKEND_URL'] ?? getenv('CLOCKIT_BACKEND_URL') ?: ''), '/');

    if ($configured !== '') {
        return $configured;
    }

    $scheme   = $_SERVER['REQUEST_SCHEME'] ?? 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $hostname = parse_url("{$scheme}://{$host}", PHP_URL_HOST) ?: 'localhost';
    $port     = parse_url("{$scheme}://{$host}", PHP_URL_PORT);

    if (in_array($hostname, ['localhost', '127.0.0.1'], true) && (int) $port === 8000) {
        return "{$scheme}://127.0.0.1:8001";
    }

    return "{$scheme}://{$host}/phpbackend/src/public/index.php";
}

/**
 * Build the Authorization header from the current PHP session token.
 * Added for Fix 5 — backend admin routes require a Bearer token.
 */
function clockit_backend_auth_header(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        return '';
    }
    $token = $_SESSION['api_token'] ?? '';
    return $token !== '' ? "Authorization: Bearer {$token}\r\n" : '';
}

/**
 * Generic request — supports GET, POST, PUT, PATCH, DELETE.
 * This is what proxy_backend_json() calls for all methods.
 */
function clockit_backend_api_request(string $method, string $path, array $body = [], array $query = []): array
{
    global $clockitBackendLastStatus;

    $url = clockit_backend_base_url() . '/' . ltrim($path, '/');

    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    $method  = strtoupper($method);
    $headers = "Accept: application/json\r\n" . clockit_backend_auth_header();

    $options = [
        'method'        => $method,
        'header'        => $headers,
        'ignore_errors' => true,
        'timeout'       => 15,
    ];

    if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        $json                    = json_encode($body, JSON_THROW_ON_ERROR);
        $options['header']      .= "Content-Type: application/json\r\n";
        $options['content']      = $json;
    }

    $context  = stream_context_create(['http' => $options]);
    $response = @file_get_contents($url, false, $context);

    $clockitBackendLastStatus = clockit_backend_parse_response_status($http_response_header ?? []);
    $payload  = $response !== false ? json_decode($response, true) : null;

    if (is_array($payload)) {
        return $payload;
    }

    $status = clockit_backend_response_status();

    return [
        'success' => false,
        'message' => $status > 0
            ? "Backend returned HTTP {$status}, but the response was not valid JSON. Check the backend terminal output."
            : 'Backend request failed. Check CLOCKIT_BACKEND_URL and make sure the PHP backend is running at ' . clockit_backend_base_url() . '.',
    ];
}

function clockit_backend_api_get(string $path, array $query = []): array
{
    return clockit_backend_api_request('GET', $path, [], $query);
}

function clockit_backend_api_post(string $path, array $body = []): array
{
    return clockit_backend_api_request('POST', $path, $body);
}

function clockit_backend_response_status(): int
{
    global $clockitBackendLastStatus;
    return $clockitBackendLastStatus;
}

function clockit_backend_parse_response_status(array $headers): int
{
    foreach ($headers as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches)) {
            return (int) $matches[1];
        }
    }
    return 0;
}
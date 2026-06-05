<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use PDO;
use Exception;

/**
 * AuthMiddleware
 *
 * Centralised authentication and authorisation middleware with session timeout support.
 */
class AuthMiddleware
{
    private string $jwtSecret;
    private ?PDO $db = null;

    public function __construct(string $jwtSecret, ?PDO $db = null)
    {
        $this->jwtSecret = $jwtSecret;
        $this->db = $db;
    }

    public function handle(array $request, callable $next): array
    {
        $result = $this->decodeToken($request);

        if (isset($result['error'])) {
            return $this->unauthorizedResponse($result['error'], $result['status']);
        }

        $request['user'] = $result['user'] ?? null;

        return $next($request);
    }

    // =============================================
    // SESSION TIMEOUT CHECK
    // =============================================
    private function checkSessionTimeout(array &$request): ?array
    {
        if (!$this->db) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT setting_value 
                FROM settings 
                WHERE setting_key = 'session_timeout_minutes'
            ");
            $stmt->execute();

            $timeout = $stmt->fetchColumn();
            $timeoutMinutes = $timeout ? (int)$timeout : 30;
            $timeoutSeconds = $timeoutMinutes * 60;

            $lastActivity = $request['last_activity'] ?? time();

            if (time() - $lastActivity > $timeoutSeconds) {
                return $this->unauthorizedResponse(
                    'Session expired due to inactivity. Please login again.',
                    401
                );
            }

            $request['last_activity'] = time();

            return null;

        } catch (Exception $e) {
            error_log("Session timeout check failed: " . $e->getMessage());
            return null;
        }
    }

    // =============================================
    // LOGIN CHECK
    // =============================================
    public function requireLogin(array &$request): ?array
    {
        $result = $this->decodeToken($request);

        if (isset($result['error'])) {
            return $this->unauthorizedResponse($result['error'], $result['status']);
        }

        $request['user'] = $result['user'] ?? null;
        $request['last_activity'] = $result['last_activity'] ?? time();

        $timeoutCheck = $this->checkSessionTimeout($request);
        if ($timeoutCheck !== null) {
            return $timeoutCheck;
        }

        return null;
    }

    // =============================================
    // ADMIN CHECK
    // =============================================
    public function requireAdmin(array &$request): ?array
    {
        $guard = $this->requireLogin($request);
        if ($guard !== null) {
            return $guard;
        }

        if (($request['user']['role'] ?? '') !== 'admin') {
            return $this->unauthorizedResponse('Admin privileges required', 403);
        }

        return null;
    }

    // =============================================
    // ROLE CHECK
    // =============================================
    public function requireRole(array &$request, string $role): ?array
    {
        $guard = $this->requireLogin($request);
        if ($guard !== null) {
            return $guard;
        }

        if (($request['user']['role'] ?? '') !== $role) {
            return $this->unauthorizedResponse("Role '$role' required", 403);
        }

        return null;
    }

    // =============================================
    // TOKEN DECODE
    // =============================================
    private function decodeToken(array $request): array
    {
        $authHeader = $request['headers']['Authorization']
            ?? $request['headers']['authorization']
            ?? null;

        if (!$authHeader || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return ['error' => 'Access token required', 'status' => 401];
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $decodedArray = (array)$decoded;

            return [
                'user' => [
                    'user_id'     => $decodedArray['user_id'] ?? $decodedArray['userId'] ?? null,
                    'email'       => $decodedArray['email'] ?? null,
                    'role'        => $decodedArray['role'] ?? null,
                    'employee_id' => $decodedArray['employee_id'] ?? null,
                ],
                'last_activity' => $decodedArray['last_activity'] ?? time(),
            ];

        } catch (ExpiredException $e) {
            return ['error' => 'Token has expired', 'status' => 401];

        } catch (SignatureInvalidException $e) {
            return ['error' => 'Invalid token signature', 'status' => 401];

        } catch (Exception $e) {
            return ['error' => 'Invalid token', 'status' => 401];
        }
    }

    // =============================================
    // RESPONSE HELPERS
    // =============================================
    private function unauthorizedResponse(string $message, int $status = 401): array
    {
        return [
            'status' => $status,
            'body' => [
                'success' => false,
                'error'   => $message
            ]
        ];
    }
}
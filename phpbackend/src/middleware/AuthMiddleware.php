<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use PDO;
use Exception;

class AuthMiddleware
{
    private string $jwtSecret;
    private ?PDO $db = null;

    public function __construct(string $jwtSecret, ?PDO $db = null)
    {
        $this->jwtSecret = $jwtSecret;
        $this->db = $db;
    }

    /**
     * Verify the Bearer token, attach decoded user data to the request,
     * then continue into the matching route.
     */
    public function handle(array $request, callable $next): array
    {
        $result = $this->decodeToken($request);

        if (isset($result['error'])) {
            return $this->unauthorizedResponse($result['error'], $result['status']);
        }

        $request['user'] = $result['user'];
        $request['last_activity'] = $result['last_activity'] ?? time();

        // Optional: check session timeout on every handled request
        $timeout = $this->checkSessionTimeout($request);
        if ($timeout !== null) {
            return $timeout;
        }

        return $next($request);
    }

    // ----------------------------------------------------------------
    // Session Timeout Check
    // ----------------------------------------------------------------

    private function checkSessionTimeout(array &$request): ?array
    {
        if (!$this->db) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'session_timeout_minutes'");
            $stmt->execute();
            $timeout = $stmt->fetchColumn();
            $timeoutMinutes = $timeout ? (int)$timeout : 30;
            $timeoutSeconds = $timeoutMinutes * 60;

            $lastActivity = $request['last_activity'] ?? time();

            if (time() - $lastActivity > $timeoutSeconds) {
                return $this->unauthorizedResponse('Session expired due to inactivity. Please login again.', 401);
            }

            $request['last_activity'] = time();
            return null;
        } catch (Exception $e) {
            error_log("Session timeout check failed: " . $e->getMessage());
            return null;
        }
    }

    // ----------------------------------------------------------------
    // Pattern A guards – accept full $request with headers
    // ----------------------------------------------------------------

    public function requireLogin(array &$request): ?array
    {
        $result = $this->decodeToken($request);

        if (isset($result['error'])) {
            return $this->unauthorizedResponse($result['error'], $result['status']);
        }

        $request['user'] = $result['user'];
        $request['last_activity'] = $result['last_activity'] ?? time();

        $timeoutCheck = $this->checkSessionTimeout($request);
        if ($timeoutCheck !== null) {
            return $timeoutCheck;
        }

        return null;
    }

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

    public function requireRole(array &$request, string $role): ?array
    {
        $guard = $this->requireLogin($request);
        if ($guard !== null) {
            return $guard;
        }

        if (($request['user']['role'] ?? '') !== $role) {
            return $this->unauthorizedResponse("Role '{$role}' required", 403);
        }

        return null;
    }

    // ----------------------------------------------------------------
    // Pattern B guards – accept plain $auth array
    // ----------------------------------------------------------------

    public function requireLoginFromAuth(array $auth): ?array
    {
        $userId = $auth['userId'] ?? $auth['user_id'] ?? null;

        if ($userId === null || trim((string) $userId) === '') {
            return $this->unauthorizedResponse('Access token required', 401);
        }

        return null;
    }

    public function requireAdminFromAuth(array $auth): ?array
    {
        $guard = $this->requireLoginFromAuth($auth);
        if ($guard !== null) {
            return $guard;
        }

        if (($auth['role'] ?? '') !== 'admin') {
            return $this->unauthorizedResponse('Admin privileges required', 403);
        }

        return null;
    }

    public function requireRoleFromAuth(array $auth, string $role): ?array
    {
        $guard = $this->requireLoginFromAuth($auth);
        if ($guard !== null) {
            return $guard;
        }

        if (($auth['role'] ?? '') !== $role) {
            return $this->unauthorizedResponse("Role '{$role}' required", 403);
        }

        return null;
    }

    // ----------------------------------------------------------------
    // Token decoding
    // ----------------------------------------------------------------

    private function decodeToken(array $request): array
    {
        // Accept both "Authorization" and "authorization" headers
        $authHeader = $request['headers']['Authorization']
            ?? $request['headers']['authorization']
            ?? null;

        error_log("DEBUG: Looking for auth header, found: " . ($authHeader ? substr($authHeader, 0, 30) : 'null'));

        if (!$authHeader || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return ['error' => 'Access token required', 'status' => 401];
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $decodedArray = (array) $decoded;

            $lastActivity = $decodedArray['last_activity'] ?? time();

            return [
                'user' => [
                    'user_id'     => $decodedArray['user_id'] ?? $decodedArray['userId'] ?? null,
                    'email'       => $decodedArray['email'] ?? null,
                    'role'        => $decodedArray['role'] ?? null,
                    'employee_id' => $decodedArray['employee_id'] ?? null,
                ],
                'last_activity' => $lastActivity,
            ];
        } catch (ExpiredException $e) {
            error_log("JWT Expired: " . $e->getMessage());
            return ['error' => 'Token has expired', 'status' => 403];
        } catch (SignatureInvalidException $e) {
            error_log("JWT Invalid Signature: " . $e->getMessage());
            return ['error' => 'Invalid token signature', 'status' => 403];
        } catch (Exception $e) {
            error_log("JWT Decode Error: " . $e->getMessage());
            return ['error' => 'Invalid token', 'status' => 403];
        }
    }

    private function unauthorizedResponse(string $message, int $status): array
    {
        return [
            'status' => $status,
            'body'   => [
                'success' => false,
                'error'   => $message,
            ],
        ];
    }
}
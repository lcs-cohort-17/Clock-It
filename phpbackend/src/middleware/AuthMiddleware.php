<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Throwable;

class AuthMiddleware
{
    private string $jwtSecret;

    public function __construct(string $jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
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

        return $next($request);
    }

    public function requireLogin(array &$request): ?array
    {
        $result = $this->decodeToken($request);

        if (isset($result['error'])) {
            return $this->unauthorizedResponse($result['error'], $result['status']);
        }

        $request['user'] = $result['user'];

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

    public function requireLoginFromAuth(array $auth): ?array
    {
        $userId = $auth['userId'] ?? null;

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

    /**
     * @return array{user?: array<string, mixed>, error?: string, status?: int}
     */
    private function decodeToken(array $request): array
    {
        $headers = $request['headers'] ?? [];
        if (!is_array($headers)) {
            $headers = [];
        }

        $headers = array_change_key_case($headers, CASE_LOWER);
        $authHeader = trim((string) ($headers['authorization'] ?? ''));

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return ['error' => 'Access token required', 'status' => 401];
        }

        $token = trim($matches[1]);

        if ($token === '') {
            return ['error' => 'Access token required', 'status' => 401];
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            return [
                'user' => [
                    'userId'      => (string) ($decoded->userId ?? ''),
                    'email'       => (string) ($decoded->email ?? ''),
                    'role'        => strtolower((string) ($decoded->role ?? 'staff')),
                    'employee_id' => (string) ($decoded->employee_id ?? ''),
                ],
            ];
        } catch (ExpiredException) {
            return ['error' => 'Token has expired', 'status' => 403];
        } catch (SignatureInvalidException) {
            return ['error' => 'Invalid token signature', 'status' => 403];
        } catch (Throwable) {
            return ['error' => 'Invalid token', 'status' => 403];
        }
    }

    private function unauthorizedResponse(string $message, int $status): array
    {
        return [
            'status' => $status,
            'body'   => [
                'success' => false,
                'message' => $message,
            ],
        ];
    }
}
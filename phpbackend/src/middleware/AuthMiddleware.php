<?php

namespace App\Middleware;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

/**
 * Centralised authentication and authorisation middleware.
 *
 * Supports full request pipelines:
 * $auth->handle($request, $next);
 *
 * Also supports controller guards:
 * $guard = $auth->requireLogin($request);
 * $guard = $auth->requireLoginFromAuth($auth);
 * $guard = $auth->requireAdmin($request);
 * $guard = $auth->requireAdminFromAuth($auth);
 * $guard = $auth->requireRole($request, 'manager');
 * $guard = $auth->requireRoleFromAuth($auth, 'manager');
 */
class AuthMiddleware
{
    private string $jwtSecret;

    public function __construct(string $jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
    }

    /**
     * Verify the bearer token, attach the decoded user to the request,
     * then call the next handler.
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

    /**
     * Ensures the request carries a valid JWT token.
     * Returns a 401/403 response array on failure, null on success.
     */
    public function requireLogin(array &$request): ?array
    {
        $result = $this->decodeToken($request);

        if (isset($result['error'])) {
            return $this->unauthorizedResponse($result['error'], $result['status']);
        }

        $request['user'] = $result['user'];

        return null;
    }

    /**
     * Ensures the request carries a valid JWT token and admin role.
     * Returns a 401/403 response array on failure, null on success.
     */
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

    /**
     * Ensures the request carries a valid JWT token and matching role.
     * Returns a 401/403 response array on failure, null on success.
     */
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

    /**
     * Ensures the auth array is a non-empty user payload.
     * Returns a 401 response array on failure, null on success.
     */
    public function requireLoginFromAuth(array $auth): ?array
    {
        $userId = $auth['userId'] ?? null;

        if ($userId === null || $userId === '') {
            return $this->unauthorizedResponse('Access token required', 401);
        }

        return null;
    }

    /**
     * Ensures the auth array is present and admin.
     * Returns a 401/403 response array on failure, null on success.
     */
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

    /**
     * Ensures the auth array is present and has the requested role.
     * Returns a 401/403 response array on failure, null on success.
     */
    public function requireRoleFromAuth(array $auth, string $role): ?array
    {
        $guard = $this->requireLoginFromAuth($auth);
        if ($guard !== null) {
            return $guard;
        }

        if (($auth['role'] ?? '') !== $role) {
            return $this->unauthorizedResponse("Role '$role' required", 403);
        }

        return null;
    }

    /**
     * Decode and validate the bearer JWT from the request headers.
     *
     * @return array{user?: array<string, mixed>, error?: string, status?: int}
     */
    private function decodeToken(array $request): array
    {
        $authHeader = $request['headers']['authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return ['error' => 'Access token required', 'status' => 401];
        }

        $token = explode(' ', $authHeader, 2)[1];

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            return [
                'user' => [
                    'userId' => $decoded->userId ?? null,
                    'email' => $decoded->email ?? null,
                    'role' => $decoded->role ?? null,
                    'employee_id' => $decoded->employee_id ?? null,
                ],
            ];
        } catch (ExpiredException $e) {
            return ['error' => 'Token has expired', 'status' => 403];
        } catch (SignatureInvalidException $e) {
            return ['error' => 'Invalid token', 'status' => 403];
        } catch (\Exception $e) {
            return ['error' => 'Invalid token', 'status' => 403];
        }
    }

    /**
     * Build a standardised error response.
     */
    private function unauthorizedResponse(string $message, int $status): array
    {
        return [
            'status' => $status,
            'body' => [
                'success' => false,
                'error' => $message,
            ],
        ];
    }
}

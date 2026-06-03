<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * AuthMiddleware
 *
 * Centralised authentication and authorisation middleware.
 *
 * Two usage patterns are supported:
 *
 * Pattern A — Full request pipeline (route middleware):
 *   $auth->handle($request, $next);
 *
 * Pattern B — Plain auth array (controller guards):
 *   $guard = $auth->requireLogin($request);          // pass full $request
 *   $guard = $auth->requireLoginFromAuth($auth);     // pass decoded $auth array
 *   $guard = $auth->requireAdmin($request);
 *   $guard = $auth->requireAdminFromAuth($auth);
 *   $guard = $auth->requireRole($request, 'manager');
 *   $guard = $auth->requireRoleFromAuth($auth, 'manager');
 *   if ($guard !== null) return $guard;
 */
class AuthMiddleware
{
    private string $jwtSecret;

    public function __construct(string $jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
    }

    // ----------------------------------------------------------------
    // Core handler — used by route pipelines
    // ----------------------------------------------------------------

    /**
     * Verify the Bearer token and attach the decoded user to the request,
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

    // ----------------------------------------------------------------
    // Pattern A guards — accept full $request with headers
    // ----------------------------------------------------------------

    /**
     * Ensures the request carries a valid JWT token.
     * Returns a 401/403 response array on failure, null on success.
     * Attaches decoded user to $request['user'] on success.
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
     * Ensures the request carries a valid JWT token AND role is 'admin'.
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
     * Ensures the request carries a valid JWT token AND role matches $role.
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

    // ----------------------------------------------------------------
    // Pattern B guards — accept plain $auth array (already decoded)
    // ----------------------------------------------------------------

    /**
     * Ensures the $auth array is a non-empty, valid user payload.
     * Use this in controllers that receive $auth directly (not a full request).
     *
     * Returns a 401 response array if $auth is missing or empty, null on success.
     *
     * Intended use:
     *   $guard = $this->auth->requireLoginFromAuth($auth);
     *   if ($guard !== null) { $this->respond(...); return; }
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
     * Ensures the $auth array is present AND role is 'admin'.
     * Returns a 401/403 response array on failure, null on success.
     *
     * Intended use:
     *   $guard = $this->auth->requireAdminFromAuth($auth);
     *   if ($guard !== null) { $this->respond(...); return; }
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
     * Ensures the $auth array is present AND role matches $role.
     * Returns a 401/403 response array on failure, null on success.
     *
     * Intended use:
     *   $guard = $this->auth->requireRoleFromAuth($auth, 'manager');
     *   if ($guard !== null) { $this->respond(...); return; }
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

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    /**
     * Decode and validate the Bearer JWT from the request headers.
     *
     * @return array On success: ['user' => [...]]
     *               On failure: ['error' => string, 'status' => int]
     */
    private function decodeToken(array $request): array
    {
        // Try both common cases
        $authHeader = $request['headers']['Authorization'] ?? 
                    $request['headers']['authorization'] ?? 
                    null;
        
        error_log("DEBUG: Looking for auth header, found: " . ($authHeader ? substr($authHeader, 0, 30) : 'null'));
        
        if (!$authHeader || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return ['error' => 'Access token required', 'status' => 401];
        }

        $token = $matches[1];
        
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            return [
                'user' => [
                    'userId'      => $decoded->user_id ?? $decoded->userId ?? null,
                    'email'       => $decoded->email ?? null,
                    'role'        => $decoded->role ?? null,
                    'employee_id' => $decoded->employee_id ?? null,
                ],
            ];
        } catch (Exception $e) {
            error_log("JWT Decode Error: " . $e->getMessage());
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
            'body'   => [
                'success' => false,
                'error'   => $message,
            ],
        ];
    }
}
<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

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
     *
     * @param  array  
     * @param  callable 
     * @return array              
     
   
    // ----------------------------------------------------------------
    // Guard helpers — call at the top of any endpoint that needs auth
    // ----------------------------------------------------------------

   
     *
     * @param  array      $request  The incoming request array.
     * @return array|null           A 401/403 response array, or null on success.
     */
    public function requireLogin(array &$request): ?array
    {
        $result = $this->decodeToken($request);

        if (isset($result['error'])) {
            return $this->unauthorizedResponse($result['error'], $result['status']);
        }

        // Attach the decoded user so the endpoint can use it
        $request['user'] = $result['user'];

        return null; // Authentication passed
    }

    /**
     
     *
     * @param  array      $request  The incoming request array.
     * @return array|null           A 401/403 response array, or null on success.
     */
    public function requireAdmin(array &$request): ?array
    {
        // First check: must be authenticated
        $guard = $this->requireLogin($request);
        if ($guard !== null) {
            return $guard;
        }

        // Second check: must hold the admin role
        if (($request['user']['role'] ?? '') !== 'admin') {
            return $this->unauthorizedResponse('Admin privileges required', 403);
        }

        return null; // Both checks passed
    }

    /**
     * 
     *
     * @param  array      $request  The incoming request array.
     * @param  string     $role     The exact role string that is required.
     * @return array|null           A 401/403 response array, or null on success.
     */
    public function requireRole(array &$request, string $role): ?array
    {
        // First check: must be authenticated
        $guard = $this->requireLogin($request);
        if ($guard !== null) {
            return $guard;
        }

        // Second check: role must match
        if (($request['user']['role'] ?? '') !== $role) {
            return $this->unauthorizedResponse(
                "Role '$role' required",
                403
            );
        }

        return null; // Both checks passed
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    /**
     * Decode and validate the Bearer JWT from the request headers.
     *
     * @param  array $request
     * @return array  On success: ['user' => [...]]
     *                On failure: ['error' => string, 'status' => int]
     */
    private function decodeToken(array $request): array
    {
        $authHeader = $request['headers']['authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return ['error' => 'Access token required', 'status' => 401];
        }

        $token = explode(' ', $authHeader)[1];

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            return [
                'user' => [
                    'userId'      => $decoded->userId,
                    'email'       => $decoded->email,
                    'role'        => $decoded->role,
                    'employee_id' => $decoded->employee_id,
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
     *
     * @param  string $message  Human-readable error string.
     * @param  int    $status   HTTP status code (401 or 403).
     * @return array
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
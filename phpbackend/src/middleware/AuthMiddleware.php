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

    // Update constructor to accept database connection and make arguments optional
    public function __construct(string $jwtSecret = '', ?PDO $db = null)
    {
        $this->jwtSecret = $jwtSecret ?: ($_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this');
        $this->db = $db;
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
    // Session Timeout Check
    // ----------------------------------------------------------------

    /**
     * Check if the session has expired due to inactivity.
     * Returns a 401 response array if expired, null otherwise.
     */
    private function checkSessionTimeout(array &$request): ?array
    {
        // Skip timeout check if no database connection
        if (!$this->db) {
            return null;
        }
        
        try {
            // Get timeout from settings table
            $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'session_timeout_minutes'");
            $stmt->execute();
            $timeout = $stmt->fetchColumn();
            $timeoutMinutes = $timeout ? (int)$timeout : 30;
            $timeoutSeconds = $timeoutMinutes * 60;
            
            // Get last activity from request (set in token or passed in)
            $lastActivity = $request['last_activity'] ?? time();
            
            // Check if session expired
            if (time() - $lastActivity > $timeoutSeconds) {
                return $this->unauthorizedResponse('Session expired due to inactivity. Please login again.', 401);
            }
            
            // Update last activity for this request
            $request['last_activity'] = time();
            
            return null;
            
        } catch (Exception $e) {
            error_log("Session timeout check failed: " . $e->getMessage());
            return null; // Don't block on error - allow request to proceed
        }
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
        
        // Get last_activity from token if present
        $request['last_activity'] = $result['last_activity'] ?? time();
        
        // Check session timeout
        $timeoutCheck = $this->checkSessionTimeout($request);
        if ($timeoutCheck !== null) {
            return $timeoutCheck;
        }

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

    public function requireLoginFromAuth(array $auth): ?array
    {
        $userId = $auth['userId'] ?? $auth['user_id'] ?? null;

        if ($userId === null || $userId === '') {
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
     * @return array On success: ['user' => [...], 'last_activity' => int]
     *               On failure: ['error' => string, 'status' => int]
     */
    private function decodeToken(array $request): array
    {
        // Try both common cases for Authorization header
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
            
            // Convert decoded object to array
            $decodedArray = (array)$decoded;
            
            // Extract last_activity if present
            $lastActivity = $decodedArray['last_activity'] ?? time();
            
            return [
                'user' => [
                    'user_id'     => $decodedArray['user_id'] ?? $decodedArray['userId'] ?? null,
                    'userId'      => $decodedArray['user_id'] ?? $decodedArray['userId'] ?? null,
                    'email'       => $decodedArray['email'] ?? null,
                    'role'        => $decodedArray['role'] ?? null,
                    'employee_id' => $decodedArray['employee_id'] ?? $decodedArray['employeeId'] ?? null,
                    'employeeId'  => $decodedArray['employee_id'] ?? $decodedArray['employeeId'] ?? null,
                ],
                'last_activity' => $lastActivity,
            ];
            
        } catch (ExpiredException $e) {
            error_log("JWT Expired: " . $e->getMessage());
            return ['error' => 'Token has expired', 'status' => 403];
        } catch (SignatureInvalidException $e) {
            error_log("JWT Invalid Signature: " . $e->getMessage());
            return ['error' => 'Invalid token', 'status' => 403];
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
<?php

namespace Tests\Middleware;

use Middleware\AuthMiddleware;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;

/**
 * RbacVerificationTest
 *
 * Verifies the three core RBAC scenarios required by the ticket:
 *   1. Non-logged-in user  → 401 on any protected endpoint
 *   2. Logged-in staff     → 403 on admin endpoints, 200 on user endpoints
 *   3. Logged-in admin     → 200 on both admin and user endpoints
 *
 * Covers both guard patterns:
 *   - requireLogin / requireAdmin / requireRole  (full $request with headers)
 *   - requireLoginFromAuth / requireAdminFromAuth / requireRoleFromAuth ($auth array)
 */
class RbacVerificationTest extends TestCase
{
    private AuthMiddleware $middleware;
    private string $secret = 'test_secret_key_that_is_long_enough';

    protected function setUp(): void
    {
        $this->middleware = new AuthMiddleware($this->secret);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function generateToken(array $payload = []): string
    {
        $defaults = [
            'userId'      => '123',
            'email'       => 'user@example.com',
            'role'        => 'employee',
            'employee_id' => 'EMP001',
            'exp'         => time() + 3600,
        ];

        return JWT::encode(array_merge($defaults, $payload), $this->secret, 'HS256');
    }

    private function makeRequest(string $token): array
    {
        return ['headers' => ['authorization' => "Bearer $token"]];
    }

    private function makeAuthArray(string $role, string $userId = '123'): array
    {
        return [
            'userId'      => $userId,
            'email'       => 'user@example.com',
            'role'        => $role,
            'employee_id' => 'EMP001',
        ];
    }

    // ----------------------------------------------------------------
    // Scenario 1: Non-logged-in user gets 401 on protected endpoints
    // ----------------------------------------------------------------

    public function test_unauthenticated_user_gets_401_on_user_endpoint_via_request(): void
    {
        $request = ['headers' => []];

        $result = $this->middleware->requireLogin($request);

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
        $this->assertEquals('Access token required', $result['body']['error']);
    }

    public function test_unauthenticated_user_gets_401_on_admin_endpoint_via_request(): void
    {
        $request = ['headers' => []];

        $result = $this->middleware->requireAdmin($request);

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
        $this->assertEquals('Access token required', $result['body']['error']);
    }

    public function test_unauthenticated_user_gets_401_on_user_endpoint_via_auth_array(): void
    {
        $result = $this->middleware->requireLoginFromAuth([]);

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
        $this->assertEquals('Access token required', $result['body']['error']);
    }

    public function test_unauthenticated_user_gets_401_on_admin_endpoint_via_auth_array(): void
    {
        $result = $this->middleware->requireAdminFromAuth([]);

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
        $this->assertEquals('Access token required', $result['body']['error']);
    }

    public function test_missing_bearer_prefix_gets_401(): void
    {
        $token   = $this->generateToken();
        $request = ['headers' => ['authorization' => "Token $token"]];

        $result = $this->middleware->requireLogin($request);

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
    }

    public function test_expired_token_gets_403_not_401(): void
    {
        // Expired tokens are a different failure mode to missing tokens —
        // the user was authenticated but the session has lapsed (403, not 401)
        $token   = $this->generateToken(['exp' => time() - 3600]);
        $request = $this->makeRequest($token);

        $result = $this->middleware->requireLogin($request);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertEquals('Token has expired', $result['body']['error']);
    }

    // ----------------------------------------------------------------
    // Scenario 2: Logged-in staff gets 403 on admin endpoints
    //             but passes user (requireLogin) endpoints
    // ----------------------------------------------------------------

    public function test_staff_gets_403_on_admin_endpoint_via_request(): void
    {
        $token   = $this->generateToken(['role' => 'employee']);
        $request = $this->makeRequest($token);

        $result = $this->middleware->requireAdmin($request);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertEquals('Admin privileges required', $result['body']['error']);
    }

    public function test_staff_gets_403_on_admin_endpoint_via_auth_array(): void
    {
        $auth   = $this->makeAuthArray('employee');
        $result = $this->middleware->requireAdminFromAuth($auth);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertEquals('Admin privileges required', $result['body']['error']);
    }

    public function test_staff_passes_user_endpoint_via_request(): void
    {
        $token   = $this->generateToken(['role' => 'employee']);
        $request = $this->makeRequest($token);

        $result = $this->middleware->requireLogin($request);

        $this->assertNull($result, 'Staff with a valid token should pass requireLogin()');
        $this->assertEquals('employee', $request['user']['role']);
    }

    public function test_staff_passes_user_endpoint_via_auth_array(): void
    {
        $auth   = $this->makeAuthArray('employee');
        $result = $this->middleware->requireLoginFromAuth($auth);

        $this->assertNull($result, 'Staff with a populated auth array should pass requireLoginFromAuth()');
    }

    public function test_manager_role_also_gets_403_on_admin_endpoint(): void
    {
        // Only 'admin' passes — any other role, including manager, must be blocked
        $auth   = $this->makeAuthArray('manager');
        $result = $this->middleware->requireAdminFromAuth($auth);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertEquals('Admin privileges required', $result['body']['error']);
    }

    // ----------------------------------------------------------------
    // Scenario 3: Logged-in admin gets access to both endpoint types
    // ----------------------------------------------------------------

    public function test_admin_passes_admin_endpoint_via_request(): void
    {
        $token   = $this->generateToken(['role' => 'admin']);
        $request = $this->makeRequest($token);

        $result = $this->middleware->requireAdmin($request);

        $this->assertNull($result, 'Admin should pass requireAdmin()');
        $this->assertEquals('admin', $request['user']['role']);
    }

    public function test_admin_passes_admin_endpoint_via_auth_array(): void
    {
        $auth   = $this->makeAuthArray('admin');
        $result = $this->middleware->requireAdminFromAuth($auth);

        $this->assertNull($result, 'Admin should pass requireAdminFromAuth()');
    }

    public function test_admin_passes_user_endpoint_via_request(): void
    {
        $token   = $this->generateToken(['role' => 'admin']);
        $request = $this->makeRequest($token);

        $result = $this->middleware->requireLogin($request);

        $this->assertNull($result, 'Admin should also pass requireLogin()');
    }

    public function test_admin_passes_user_endpoint_via_auth_array(): void
    {
        $auth   = $this->makeAuthArray('admin');
        $result = $this->middleware->requireLoginFromAuth($auth);

        $this->assertNull($result, 'Admin should also pass requireLoginFromAuth()');
    }

    // ----------------------------------------------------------------
    // requireRole / requireRoleFromAuth — arbitrary role checks
    // ----------------------------------------------------------------

    public function test_requireRole_passes_when_role_matches_via_request(): void
    {
        $token   = $this->generateToken(['role' => 'manager']);
        $request = $this->makeRequest($token);

        $result = $this->middleware->requireRole($request, 'manager');

        $this->assertNull($result);
        $this->assertEquals('manager', $request['user']['role']);
    }

    public function test_requireRole_blocks_wrong_role_via_request(): void
    {
        $token   = $this->generateToken(['role' => 'employee']);
        $request = $this->makeRequest($token);

        $result = $this->middleware->requireRole($request, 'manager');

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertStringContainsString('manager', $result['body']['error']);
    }

    public function test_requireRoleFromAuth_passes_when_role_matches(): void
    {
        $auth   = $this->makeAuthArray('manager');
        $result = $this->middleware->requireRoleFromAuth($auth, 'manager');

        $this->assertNull($result);
    }

    public function test_requireRoleFromAuth_blocks_wrong_role(): void
    {
        $auth   = $this->makeAuthArray('employee');
        $result = $this->middleware->requireRoleFromAuth($auth, 'manager');

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertStringContainsString('manager', $result['body']['error']);
    }

    public function test_requireRoleFromAuth_returns_401_when_auth_is_empty(): void
    {
        $result = $this->middleware->requireRoleFromAuth([], 'manager');

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
    }

    public function test_role_check_is_case_sensitive(): void
    {
        // 'Admin' must not pass a check for 'admin'
        $auth   = $this->makeAuthArray('Admin');
        $result = $this->middleware->requireAdminFromAuth($auth);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
    }

    // ----------------------------------------------------------------
    // Consistent error response shape across all guard types
    // ----------------------------------------------------------------

    public function test_all_guard_failures_return_success_false_and_error_string(): void
    {
      $emptyRequest        = ['headers' => []];
$emptyRequest2       = ['headers' => []];
$staffAuth           = $this->makeAuthArray('employee');
$staffAuthForRole    = $this->makeAuthArray('employee');

$cases = [
    $this->middleware->requireLogin($emptyRequest),
    $this->middleware->requireAdmin($emptyRequest2),
    $this->middleware->requireLoginFromAuth([]),
    $this->middleware->requireAdminFromAuth([]),
    $this->middleware->requireAdminFromAuth($staffAuth),
    $this->middleware->requireRoleFromAuth($staffAuthForRole, 'manager'),
];

        foreach ($cases as $index => $result) {
            $this->assertNotNull($result, "Case $index should not be null");
            $this->assertArrayHasKey('success', $result['body'], "Case $index missing 'success'");
            $this->assertArrayHasKey('error', $result['body'], "Case $index missing 'error'");
            $this->assertFalse($result['body']['success'], "Case $index success should be false");
            $this->assertIsString($result['body']['error'], "Case $index error should be a string");
        }
    }
}
<?php

namespace Tests\Middleware;

use Middleware\AuthMiddleware;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;

class AuthMiddlewareTest extends TestCase
{
    private AuthMiddleware $middleware;
    private string $secret = 'test_secret_key_that_is_long_enough';

    protected function setUp(): void
    {
        $this->middleware = new AuthMiddleware($this->secret);
    }

    // ----------------------------------------------------------------
    // Helper — builds a valid signed token
    // ----------------------------------------------------------------

    private function generateToken(array $payload = []): string
    {
        $default = [
            'userId'      => '123',
            'email'       => 'user@example.com',
            'role'        => 'employee',
            'employee_id' => 'EMP001',
            'exp'         => time() + 3600,
        ];

        return JWT::encode(array_merge($default, $payload), $this->secret, 'HS256');
    }

    // ----------------------------------------------------------------
    // handle() — core pipeline middleware (existing tests preserved)
    // ----------------------------------------------------------------

    public function test_handle_allows_request_with_valid_token(): void
    {
        $token   = $this->generateToken();
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $response = $this->middleware->handle($request, function ($req) {
            return ['status' => 200, 'body' => ['success' => true, 'user' => $req['user']]];
        });

        $this->assertEquals(200, $response['status']);
        $this->assertTrue($response['body']['success']);
        $this->assertEquals('user@example.com', $response['body']['user']['email']);
    }

    public function test_handle_attaches_user_data_to_request(): void
    {
        $token   = $this->generateToken();
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $capturedRequest = null;
        $this->middleware->handle($request, function ($req) use (&$capturedRequest) {
            $capturedRequest = $req;
            return ['status' => 200, 'body' => ['success' => true]];
        });

        $this->assertArrayHasKey('user', $capturedRequest);
        $this->assertEquals('123', $capturedRequest['user']['userId']);
        $this->assertEquals('EMP001', $capturedRequest['user']['employee_id']);
        $this->assertEquals('employee', $capturedRequest['user']['role']);
    }

    public function test_handle_blocks_request_with_no_authorization_header(): void
    {
        $request  = ['headers' => []];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(401, $response['status']);
        $this->assertFalse($response['body']['success']);
        $this->assertEquals('Access token required', $response['body']['error']);
    }

    public function test_handle_blocks_request_without_bearer_prefix(): void
    {
        $token    = $this->generateToken();
        $request  = ['headers' => ['authorization' => "Token $token"]];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(401, $response['status']);
        $this->assertEquals('Access token required', $response['body']['error']);
    }

    public function test_handle_blocks_request_with_invalid_token(): void
    {
        $request  = ['headers' => ['authorization' => 'Bearer invalidtoken123']];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(403, $response['status']);
        $this->assertFalse($response['body']['success']);
        $this->assertEquals('Invalid token', $response['body']['error']);
    }

    public function test_handle_blocks_request_with_expired_token(): void
    {
        $token    = $this->generateToken(['exp' => time() - 3600]);
        $request  = ['headers' => ['authorization' => "Bearer $token"]];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(403, $response['status']);
        $this->assertEquals('Token has expired', $response['body']['error']);
    }

    public function test_handle_blocks_request_signed_with_wrong_secret(): void
    {
        $token    = JWT::encode(['userId' => '999', 'exp' => time() + 3600], 'wrong_secret_key_that_is_long_enough', 'HS256');
        $request  = ['headers' => ['authorization' => "Bearer $token"]];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(403, $response['status']);
        $this->assertEquals('Invalid token', $response['body']['error']);
    }

    public function test_handle_error_responses_always_contain_success_false_and_error_message(): void
    {
        $cases = [
            ['headers' => []],
            ['headers' => ['authorization' => 'Bearer bad']],
        ];

        foreach ($cases as $request) {
            $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);
            $this->assertArrayHasKey('success', $response['body']);
            $this->assertArrayHasKey('error', $response['body']);
            $this->assertFalse($response['body']['success']);
            $this->assertIsString($response['body']['error']);
        }
    }

    // ----------------------------------------------------------------
    // requireLogin() — unauthenticated user endpoint guard
    // ----------------------------------------------------------------

    public function test_requireLogin_returns_null_and_attaches_user_when_token_is_valid(): void
    {
        $token   = $this->generateToken();
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireLogin($request);

        $this->assertNull($result, 'requireLogin() should return null on success');
        $this->assertArrayHasKey('user', $request);
        $this->assertEquals('123', $request['user']['userId']);
        $this->assertEquals('employee', $request['user']['role']);
    }

    public function test_requireLogin_returns_401_when_no_token_is_present(): void
    {
        $request = ['headers' => []];

        $result = $this->middleware->requireLogin($request);

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
        $this->assertFalse($result['body']['success']);
        $this->assertEquals('Access token required', $result['body']['error']);
    }

    public function test_requireLogin_returns_401_when_bearer_prefix_is_missing(): void
    {
        $token   = $this->generateToken();
        $request = ['headers' => ['authorization' => "Token $token"]];

        $result = $this->middleware->requireLogin($request);

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
        $this->assertEquals('Access token required', $result['body']['error']);
    }

    public function test_requireLogin_returns_403_when_token_is_expired(): void
    {
        $token   = $this->generateToken(['exp' => time() - 3600]);
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireLogin($request);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertEquals('Token has expired', $result['body']['error']);
    }

    public function test_requireLogin_returns_403_when_token_signature_is_invalid(): void
    {
        $token   = JWT::encode(['userId' => '999', 'exp' => time() + 3600], 'wrong_secret_key_that_is_long_enough', 'HS256');
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireLogin($request);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertEquals('Invalid token', $result['body']['error']);
    }

    // ----------------------------------------------------------------
    // requireAdmin() — admin endpoint guard
    // ----------------------------------------------------------------

    public function test_requireAdmin_returns_null_when_user_is_admin(): void
    {
        $token   = $this->generateToken(['role' => 'admin']);
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireAdmin($request);

        $this->assertNull($result, 'requireAdmin() should return null for an admin user');
        $this->assertArrayHasKey('user', $request);
        $this->assertEquals('admin', $request['user']['role']);
    }

    public function test_requireAdmin_returns_403_when_logged_in_user_is_staff(): void
    {
        $token   = $this->generateToken(['role' => 'employee']);
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireAdmin($request);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertFalse($result['body']['success']);
        $this->assertEquals('Admin privileges required', $result['body']['error']);
    }

    public function test_requireAdmin_returns_403_when_logged_in_user_is_manager(): void
    {
        $token   = $this->generateToken(['role' => 'manager']);
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireAdmin($request);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertEquals('Admin privileges required', $result['body']['error']);
    }

    public function test_requireAdmin_returns_401_when_no_token_is_present(): void
    {
        $request = ['headers' => []];

        $result = $this->middleware->requireAdmin($request);

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
        $this->assertEquals('Access token required', $result['body']['error']);
    }

    public function test_requireAdmin_returns_403_when_token_is_expired(): void
    {
        $token   = $this->generateToken(['role' => 'admin', 'exp' => time() - 3600]);
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireAdmin($request);

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertEquals('Token has expired', $result['body']['error']);
    }

    // ----------------------------------------------------------------
    // requireRole() — arbitrary role guard
    // ----------------------------------------------------------------

    public function test_requireRole_returns_null_when_role_matches(): void
    {
        $token   = $this->generateToken(['role' => 'manager']);
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireRole($request, 'manager');

        $this->assertNull($result, 'requireRole() should return null when role matches');
        $this->assertEquals('manager', $request['user']['role']);
    }

    public function test_requireRole_returns_403_when_role_does_not_match(): void
    {
        $token   = $this->generateToken(['role' => 'employee']);
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireRole($request, 'manager');

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
        $this->assertFalse($result['body']['success']);
        $this->assertStringContainsString('manager', $result['body']['error']);
    }

    public function test_requireRole_returns_401_when_no_token_is_present(): void
    {
        $request = ['headers' => []];

        $result = $this->middleware->requireRole($request, 'manager');

        $this->assertNotNull($result);
        $this->assertEquals(401, $result['status']);
        $this->assertEquals('Access token required', $result['body']['error']);
    }

    public function test_requireRole_is_case_sensitive(): void
    {
        // 'Admin' (capital A) must NOT pass a check for 'admin' (lowercase)
        $token   = $this->generateToken(['role' => 'Admin']);
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireRole($request, 'admin');

        $this->assertNotNull($result);
        $this->assertEquals(403, $result['status']);
    }

    public function test_requireRole_works_for_admin_role_same_as_requireAdmin(): void
    {
        $token   = $this->generateToken(['role' => 'admin']);
        $request = ['headers' => ['authorization' => "Bearer $token"]];

        $result = $this->middleware->requireRole($request, 'admin');

        $this->assertNull($result);
    }
}
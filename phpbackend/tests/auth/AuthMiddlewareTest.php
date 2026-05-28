<?php

namespace Tests\Middleware;

use App\Middleware\AuthMiddleware;
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
    // AC: Authentication middleware verifies the user
    // ----------------------------------------------------------------

    public function test_it_allows_request_with_valid_token(): void
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

    public function test_it_attaches_user_data_to_request(): void
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

    // ----------------------------------------------------------------
    // AC: All endpoints are protected and tested
    // ----------------------------------------------------------------

    public function test_it_blocks_request_with_no_authorization_header(): void
    {
        $request  = ['headers' => []];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(401, $response['status']);
        $this->assertFalse($response['body']['success']);
        $this->assertEquals('Access token required', $response['body']['error']);
    }

    public function test_it_blocks_request_without_bearer_prefix(): void
    {
        $token    = $this->generateToken();
        $request  = ['headers' => ['authorization' => "Token $token"]];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(401, $response['status']);
        $this->assertEquals('Access token required', $response['body']['error']);
    }

    public function test_it_blocks_request_with_invalid_token(): void
    {
        $request  = ['headers' => ['authorization' => 'Bearer invalidtoken123']];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(403, $response['status']);
        $this->assertFalse($response['body']['success']);
        $this->assertEquals('Invalid token', $response['body']['error']);
    }

    public function test_it_blocks_request_with_expired_token(): void
    {
        $token    = $this->generateToken(['exp' => time() - 3600]); // already expired
        $request  = ['headers' => ['authorization' => "Bearer $token"]];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(403, $response['status']);
        $this->assertEquals('Token has expired', $response['body']['error']);
    }

    public function test_it_blocks_request_signed_with_wrong_secret(): void
    {
        $token    = JWT::encode(['userId' => '999', 'exp' => time() + 3600], 'wrong_secret_key_that_is_long_enough', 'HS256');
        $request  = ['headers' => ['authorization' => "Bearer $token"]];
        $response = $this->middleware->handle($request, fn($r) => ['status' => 200]);

        $this->assertEquals(403, $response['status']);
        $this->assertEquals('Invalid token', $response['body']['error']);
    }

    // ----------------------------------------------------------------
    // AC: API responses are consistent and clear
    // ----------------------------------------------------------------

    public function test_error_responses_always_contain_success_false_and_error_message(): void
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
}

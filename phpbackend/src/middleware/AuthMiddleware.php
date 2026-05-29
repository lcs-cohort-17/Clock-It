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

    public function handle(array $request, callable $next): array
    {
        $authHeader = $request['headers']['authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorizedResponse('Access token required', 401);
        }

        $token = explode(' ', $authHeader)[1];

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            $request['user'] = [
                'userId'      => $decoded->userId,
                'email'       => $decoded->email,
                'role'        => $decoded->role,
                'employee_id' => $decoded->employee_id,
            ];

            return $next($request);

        } catch (ExpiredException $e) {
            return $this->unauthorizedResponse('Token has expired', 403);
        } catch (SignatureInvalidException $e) {
            return $this->unauthorizedResponse('Invalid token', 403);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Invalid token', 403);
        }
    }

    private function unauthorizedResponse(string $message, int $status): array
    {
        return [
            'status'  => $status,
            'body'    => [
                'success' => false,
                'error'   => $message,
            ],
        ];
    }
}
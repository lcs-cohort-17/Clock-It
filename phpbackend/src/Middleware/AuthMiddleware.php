<?php

declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    public function __invoke($request = null, $handler = null)
    {
        if ($handler !== null && method_exists($handler, 'handle')) {
            return $handler->handle($request);
        }

        return $request;
    }
}

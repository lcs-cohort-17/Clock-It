<?php

declare(strict_types=1);

namespace App\Types;

// Standard response object for controller/model results.
class ApiResponse
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $error = null,
        public ?string $message = null
    ) {
    }

    public static function ok(mixed $data = null, ?string $message = null): self
    {
        return new self(true, $data, null, $message);
    }

    public static function fail(string $error): self
    {
        return new self(false, null, $error);
    }

    public function toArray(): array
    {
        $response = ['success' => $this->success];

        if ($this->data !== null) {
            $response['data'] = $this->data;
        }

        if ($this->error !== null) {
            $response['error'] = $this->error;
        }

        if ($this->message !== null) {
            $response['message'] = $this->message;
        }

        return $response;
    }
}

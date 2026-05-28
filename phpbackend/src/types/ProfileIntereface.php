<?php

namespace Types;

class ApiResponse {
    public bool $success;
    public $data;
    public ?string $error;
    public ?string $message;

    public function __construct(bool $success, $data = null, ?string $error = null, ?string $message = null) {
        $this->success = $success;
        $this->data = $data;
        $this->error = $error;
        $this->message = $message;
    }

    public function toArray(): array {
        $array = ['success' => $this->success];

        if ($this->data !== null) {
            $array['data'] = $this->data;
        }
        if ($this->error !== null) {
            $array['error'] = $this->error;
        }
        if ($this->message !== null) {
            $array['message'] = $this->message;
        }

        return $array;
    }
}   

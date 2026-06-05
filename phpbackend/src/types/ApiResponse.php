<?php

namespace Types;

/**
 * This class PROMISES to follow the ApiResponseInterface rules
 * The "implements" keyword means: "I agree to do everything the interface says"
 */
class ApiResponse implements ApiResponseInterface {
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
    
    // I PROMISE to have this method (because the interface said so)
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
    
    // I PROMISE to have this method (because the interface said so)
    public function isSuccess(): bool {
        return $this->success;
    }
    
    // I PROMISE to have this method (because the interface said so)
    public function getError(): ?string {
        return $this->error;
    }
}

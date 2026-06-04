<?php

class QrToken {
    public $id;
    public $token;
    public $user_id;
    public $expires_at;
    public $used_at;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->token = $data['token'];
        $this->user_id = $data['user_id'];
        $this->expires_at = $data['expires_at'];
        $this->used_at = $data['used_at'] ?? null;
    }
}
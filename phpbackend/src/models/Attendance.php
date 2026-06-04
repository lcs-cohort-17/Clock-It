<?php

class Attendance {
    public $id;
    public $user_id;
    public $timestamp;
    public $type;
    public $device;
    public $location;
    public $qr_token_id;
    public $edited_by;
    public $edited_at;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'];
        $this->timestamp = $data['timestamp'] ?? null;
        $this->type = $data['type'];
        $this->device = $data['device'] ?? null;
        $this->location = $data['location'] ?? null;
        $this->qr_token_id = $data['qr_token_id'] ?? null;
        $this->edited_by = $data['edited_by'] ?? null;
        $this->edited_at = $data['edited_at'] ?? null;
    }
}
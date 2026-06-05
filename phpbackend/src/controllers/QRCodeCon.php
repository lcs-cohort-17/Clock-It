<?php

namespace Controllers;

use App\Models\QrCodeDb;

class QrAdminController
{
    private QrCodeDb $model;

    public function __construct(QrCodeDb $model)
    {
        $this->model = $model;
    }

    public function generate(array $request): array
    {
        $user = $request['user'] ?? null;

        if (!$user || ($user['role'] ?? '') !== 'admin') {
            return [
                'status' => 403,
                'body' => [
                    'success' => false,
                    'error' => 'Admin only'
                ]
            ];
        }

        $token = bin2hex(random_bytes(16));

        $created = $this->model->createToken(
            $token,
            'attendance',
            $user['user_id']
        );

        if (!$created) {
            return [
                'status' => 500,
                'body' => [
                    'success' => false,
                    'error' => 'Failed to create QR token'
                ]
            ];
        }

        return [
            'status' => 200,
            'body' => [
                'success' => true,
                'qr_token' => $token,
                'expires_in' => 60
            ]
        ];
    }
}
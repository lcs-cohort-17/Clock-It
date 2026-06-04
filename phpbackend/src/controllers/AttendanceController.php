<?php

namespace App\Controllers;

use App\Models\AttendanceDb;
use Throwable;

class AttendanceController
{
    private AttendanceDb $model;

    public function __construct(AttendanceDb $model)
    {
        $this->model = $model;
    }

    public function clock(array $request): array
    {
        try {
            $user = $request['user'] ?? null;
            $body = $request['body'] ?? [];

            $userId = $user['user_id'] ?? null;

            if (!$userId) {
                return $this->response(401, "Unauthorized");
            }

            $qrToken = $body['qr_token'] ?? null;
            $device = $body['device_info'] ?? null;
            $location = $body['location'] ?? null;

            if (!$qrToken) {
                return $this->response(400, "QR token required");
            }

            // ----------------------------------------
            // 1. CHECK USER ACTIVE
            // ----------------------------------------
            $isActive = $this->model->getUserStatus($userId);

            if ($isActive === null) {
                return $this->response(401, "User not found");
            }

            if ((int)$isActive === 0) {
                return $this->response(403, "User is inactive");
            }

            // ----------------------------------------
            // 2. VALIDATE QR TOKEN
            // ----------------------------------------
            $qr = $this->model->getQrByToken($qrToken);

            if (!$qr) {
                return $this->response(410, "QR code invalid, used or expired");
            }

            // 60 SECOND RULE (ticket requirement)
            if (strtotime($qr['created_at']) < time() - 60) {
                return $this->response(410, "QR code expired");
            }

            // ----------------------------------------
            // 3. GET LAST ATTENDANCE (TODAY ONLY)
            // ----------------------------------------
            $last = $this->model->getLastEvent($userId);

            $newEvent = "in";

            if ($last) {
                $lastEvent = $last['event_type'];

                $newEvent = ($lastEvent === "in") ? "out" : "in";

                if ($lastEvent === $newEvent) {
                    return $this->response(409, "Duplicate clock action not allowed");
                }
            }

            // ----------------------------------------
            // 4. TRANSACTION
            // ----------------------------------------
            $this->model->db->beginTransaction();

            $this->model->insertAttendance([
                'user_id' => $userId,
                'event_type' => $newEvent,
                'device_info' => $device,
                'location' => $location,
                'qr_code_id' => $qr['id']
            ]);

            $this->model->markQrUsed($qr['id']);

            $this->model->db->commit();

            return $this->response(200, [
                "message" => "Clock {$newEvent} successful"
            ]);

        } catch (Throwable $e) {
            if (isset($this->model->db) && $this->model->db->inTransaction()) {
                $this->model->db->rollBack();
            }

            return $this->response(500, $e->getMessage());
        }
    }

    private function response(int $status, $message): array
    {
        return [
            "status" => $status,
            "body" => [
                "success" => $status === 200,
                "message" => $message
            ]
        ];
    }
}
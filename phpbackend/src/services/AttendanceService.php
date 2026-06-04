<?php

require_once __DIR__ . '/../repositories/QrTokenRepository.php';
require_once __DIR__ . '/../repositories/AttendanceRepository.php';
require_once __DIR__ . '/../models/Attendance.php';

class AttendanceService {
    private $qrRepo;
    private $attendanceRepo;

    public function __construct() {
        $this->qrRepo = new QrTokenRepository();
        $this->attendanceRepo = new AttendanceRepository();
    }

    public function clock($user, $qrTokenString, $device = null, $location = null) {
        try {
            $this->qrRepo->beginTransaction();

            if (!$user || !$user->active) {
                throw new Exception("User is inactive", 403);
            }

            $qrToken = $this->qrRepo->findByToken($qrTokenString);

            if (!$qrToken) {
                throw new Exception("Invalid QR token", 400);
            }
            if (strtotime($qrToken->expires_at) < time()) {
                throw new Exception("QR code expired", 410);
            }
            if ($qrToken->used_at !== null) {
                throw new Exception("QR code already used", 409);
            }

            $last = $this->attendanceRepo->getLastAttendanceToday($user->id);
            $type = ($last && $last->type === 'clock_in') ? 'clock_out' : 'clock_in';

            if ($last) {
                if ($last->type === 'clock_in' && $type === 'clock_in') {
                    throw new Exception("Already clocked in today", 409);
                }
                if ($last->type === 'clock_out' && $type === 'clock_out') {
                    throw new Exception("Already clocked out today", 409);
                }
            }

            $attendance = new Attendance([
                'user_id' => $user->id,
                'type' => $type,
                'device' => $device,
                'location' => $location,
                'qr_token_id' => $qrToken->id
            ]);

            $this->attendanceRepo->create($attendance);
            $this->qrRepo->markAsUsed($qrToken->id);

            $this->qrRepo->commit();

            return [
                'success' => true,
                'message' => "Successfully " . str_replace('_', ' ', $type),
                'type' => $type,
                'attendance' => $attendance
            ];

        } catch (Exception $e) {
            $this->qrRepo->rollBack();
            http_response_code($e->getCode() ?: 400);
            return ['error' => $e->getMessage()];
        }
    }
}
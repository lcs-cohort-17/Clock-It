<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/GoogleSheetsService.php';

use Config\Database;

class AttendanceController
{
    private PDO $conn;

    public function __construct()
    {
        $database   = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    public function index(): array
    {
        try {
            return [
                'status' => 200,
                'headers' => ['content-type' => 'application/json'],
                'body' => [
                    'data' => $this->fetchAttendanceLogs([]),
                ],
            ];
        } catch (Exception $e) {
            error_log('[AttendanceController] Index error: ' . $e->getMessage());

            return [
                'status' => 500,
                'headers' => ['content-type' => 'application/json'],
                'body' => [
                    'success' => false,
                    'message' => 'Failed to load attendance logs.',
                ],
            ];
        }
    }

    // ── POST /api/admin/export/sheets ─────────────────────────
    public function exportToSheets(): array
    {
        try {
            $body      = json_decode(file_get_contents('php://input'), true) ?? [];
            $startDate = $body['start_date'] ?? null;
            $endDate   = $body['end_date']   ?? null;
            $eventIds  = is_array($body['event_ids'] ?? null) ? $body['event_ids'] : [];

            if (($startDate !== null || $endDate !== null)
                && (!$this->isValidDate($startDate) || !$this->isValidDate($endDate))
            ) {
                return [
                    'status'  => 400,
                    'headers' => ['content-type' => 'application/json'],
                    'body'    => ['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD for both start_date and end_date.'],
                ];
            }

            $attendanceData = $this->fetchAttendanceLogs([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'event_ids' => $eventIds,
            ]);

            if (empty($attendanceData)) {
                return [
                    'status'  => 200,
                    'headers' => ['content-type' => 'application/json'],
                    'body'    => ['success' => false, 'message' => 'No attendance logs found for the selected filters.'],
                ];
            }

            $googleSheetsService = new GoogleSheetsService();
            $sheetUrl            = $googleSheetsService->exportAttendance($attendanceData);

            return [
                'status'  => 200,
                'headers' => ['content-type' => 'application/json'],
                'body'    => [
                    'success'          => true,
                    'message'          => 'Attendance exported successfully.',
                    'sheet_url'        => $sheetUrl,
                    'records_exported' => count($attendanceData),
                ],
            ];
        } catch (Exception $e) {
            error_log('[AttendanceController] Export error: ' . $e->getMessage());

            return [
                'status'  => 500,
                'headers' => ['content-type' => 'application/json'],
                'body'    => [
                    'success' => false,
                    'message' => 'Failed to export attendance.',
                    'error'   => $e->getMessage(),
                ],
            ];
        }
    }

    // ── Helpers ───────────────────────────────────────────────

    private function isValidDate(?string $date): bool
    {
        if (!$date) return false;
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function fetchAttendanceLogs(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $conditions[] = 'DATE(al.event_time) BETWEEN ? AND ?';
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }

        $eventIds = array_values(array_filter(
            $filters['event_ids'] ?? [],
            fn($id) => is_scalar($id) && trim((string) $id) !== ''
        ));

        if (!empty($eventIds)) {
            $conditions[] = 'al.id IN (' . implode(',', array_fill(0, count($eventIds), '?')) . ')';
            foreach ($eventIds as $eventId) {
                $params[] = $eventId;
            }
        }

        $where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $query = "
            SELECT
                al.id,
                al.event_type,
                al.event_time,
                al.device_info,
                al.location,
                al.sync_status,
                u.first_name,
                u.last_name
            FROM attendance_logs al
            LEFT JOIN users u ON u.user_id = al.user_id
            {$where}
            ORDER BY al.event_time DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        $logs = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $staffName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'Unknown Staff';

            $logs[] = [
                'id' => $row['id'],
                'staff' => $staffName,
                'staff_name' => $staffName,
                'type' => $this->formatEventType($row['event_type'] ?? ''),
                'event_type' => $row['event_type'] ?? '',
                'timestamp' => $row['event_time'] ?? '',
                'device' => $row['device_info'] ?? '',
                'location' => $row['location'] ?? '',
                'sync' => $this->formatSyncStatus($row['sync_status'] ?? ''),
                'sync_status' => $row['sync_status'] ?? '',
            ];
        }

        return $logs;
    }

    private function formatEventType(string $eventType): string
    {
        return match (strtolower($eventType)) {
            'in', 'clock_in' => 'Clock In',
            'out', 'clock_out' => 'Clock Out',
            default => ucwords(str_replace('_', ' ', $eventType)),
        };
    }

    private function formatSyncStatus(string $syncStatus): string
    {
        return match (strtolower($syncStatus)) {
            'synced' => 'Synced',
            'failed' => 'Failed',
            default => 'Pending',
        };
    }
}

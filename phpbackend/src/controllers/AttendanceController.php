<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/GoogleSheetsService.php';

class AttendanceController
{
    private PDO $conn;

    public function __construct()
    {
        $database   = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    // ── POST /api/admin/export/sheets ─────────────────────────
    public function exportToSheets(): array
    {
        try {
            $body      = json_decode(file_get_contents('php://input'), true) ?? [];
            $startDate = $body['start_date'] ?? null;
            $endDate   = $body['end_date']   ?? null;

            if (($startDate !== null || $endDate !== null)
                && (!$this->isValidDate($startDate) || !$this->isValidDate($endDate))
            ) {
                return [
                    'status'  => 400,
                    'headers' => ['content-type' => 'application/json'],
                    'body'    => ['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD for both start_date and end_date.'],
                ];
            }

            $conditions = ["s.status = 'completed'"];
            $params     = [];

            if ($startDate && $endDate) {
                $conditions[] = 'DATE(s.clock_in_time) BETWEEN ? AND ?';
                $params[]     = $startDate;
                $params[]     = $endDate;
            }

            $where = implode(' AND ', $conditions);

            $query = "
            SELECT
                p.first_name,
                p.last_name,
                DATE(s.clock_in_time)  AS work_date,
                s.clock_in_time,
                s.clock_out_time,
                s.duration_minutes
            FROM   sessions s
            INNER  JOIN profiles p ON s.profile_id = p.id
            WHERE  {$where}
            ORDER  BY s.clock_in_time DESC
        ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            $attendanceData = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $totalHours = $row['duration_minutes']
                    ? round($row['duration_minutes'] / 60, 2)
                    : 0;

                $attendanceData[] = [
                    'staff_name'  => trim($row['first_name'] . ' ' . $row['last_name']),
                    'date'        => $row['work_date'],
                    'clock_in'    => (new DateTime($row['clock_in_time']))->format('H:i:s'),
                    'clock_out'   => $row['clock_out_time']
                        ? (new DateTime($row['clock_out_time']))->format('H:i:s')
                        : 'N/A',
                    'total_hours' => $totalHours,
                ];
            }

            if (empty($attendanceData)) {
                return [
                    'status'  => 200,
                    'headers' => ['content-type' => 'application/json'],
                    'body'    => ['success' => false, 'message' => 'No completed sessions found for the selected date range.'],
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
}

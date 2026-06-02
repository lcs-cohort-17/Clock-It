<?php
// src/models/GoogleSheetsModel.php

class GoogleSheetsModel
{
    private ?PDO $connection = null;
    private bool $useDatabase = false;

    private static array $store = [
        'sync_frequency' => null,
        'sheet_id'       => null,
        'attendance'     => [],
    ];

    public function __construct(?PDO $connection = null)
    {
        if ($connection instanceof PDO) {
            $this->connection = $connection;
            $this->useDatabase = true;
            $this->ensureConfigTableExists();
            return;
        }

        $this->loadEnvironment();
        $this->initializeDatabaseConnection();

        if ($this->useDatabase) {
            $this->ensureConfigTableExists();
        }
    }

    public function saveSyncFrequency(string $frequency): bool
    {
        return $this->saveSetting('sync_frequency', $frequency);
    }

    public function getSyncFrequency(): ?string
    {
        $frequency = $this->getSetting('sync_frequency');

        if ($frequency !== null && $frequency !== '') {
            return $frequency;
        }

        return trim($_ENV['GOOGLE_SHEETS_SYNC_FREQUENCY'] ?? getenv('GOOGLE_SHEETS_SYNC_FREQUENCY') ?: '') ?: null;
    }

    public function updateAttendanceSyncStatus(string $attendanceId, string $status): bool
    {
        if ($this->useDatabase && $this->connection) {
            try {
                $query = 'UPDATE attendance_logs SET sync_status = :status WHERE id = :id';
                $stmt  = $this->connection->prepare($query);
                return $stmt->execute([
                    ':status' => $status,
                    ':id'     => $attendanceId,
                ]);
            } catch (PDOException $e) {
                error_log('Failed to update attendance sync status: ' . $e->getMessage());
                return false;
            }
        }

        self::$store['attendance'][$attendanceId] = $status;
        return true;
    }

    public function getPendingAttendance(): array
    {
        if ($this->useDatabase && $this->connection) {
            try {
                $query = "SELECT al.id, al.user_id, al.event_time, al.event_type, u.first_name, u.last_name
                          FROM attendance_logs al
                          LEFT JOIN users u ON u.user_id = al.user_id
                          WHERE al.sync_status = 'pending'
                          ORDER BY al.event_time ASC";

                $stmt = $this->connection->prepare($query);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (PDOException $e) {
                error_log('Failed to retrieve pending attendance: ' . $e->getMessage());
            }
        }

        return array_filter(
            self::$store['attendance'],
            fn($status) => $status === 'pending'
        );
    }

    public function getAttendanceByDateRange(string $startDate, string $endDate): array
    {
        if ($this->useDatabase && $this->connection) {
            try {
                $query = "SELECT u.first_name,
                                 u.last_name,
                                 DATE(s.clock_in_time) AS work_date,
                                 s.clock_in_time,
                                 s.clock_out_time,
                                 s.duration_minutes
                          FROM sessions s
                          INNER JOIN users u ON s.user_id = u.user_id
                          WHERE s.status = 'completed'
                            AND DATE(s.clock_in_time) BETWEEN :start_date AND :end_date
                          ORDER BY s.clock_in_time DESC";

                $stmt = $this->connection->prepare($query);
                $stmt->execute([
                    ':start_date' => $startDate,
                    ':end_date'   => $endDate,
                ]);

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

                return $attendanceData;
            } catch (PDOException $e) {
                error_log('Failed to retrieve attendance by date range: ' . $e->getMessage());
            }
        }

        return self::$store['attendance'];
    }

    public function saveSheetId(string $sheetId): bool
    {
        return $this->saveSetting('sheet_id', $sheetId);
    }

    public function getSheetId(): ?string
    {
        $sheetId = $this->getSetting('sheet_id');

        if ($sheetId !== null && $sheetId !== '') {
            return $sheetId;
        }

        return trim($_ENV['GOOGLE_SHEETS_SPREADSHEET_ID'] ?? getenv('GOOGLE_SHEETS_SPREADSHEET_ID') ?: '') ?: null;
    }

    public function syncAttendanceFromSheet(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $synced = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            if (count($row) < 2) {
                continue;
            }

            $staffName  = $row[0] ?? '';
            $date       = $row[1] ?? '';
            $clockIn    = $row[2] ?? '';
            $clockOut   = $row[3] ?? '';
            $totalHours = $row[4] ?? '';

            if (empty($staffName) || empty($date)) {
                continue;
            }

            $attendanceId = 'sync_' . md5($staffName . $date . $clockIn);
            $record = [
                'id'          => $attendanceId,
                'staff_name'  => $staffName,
                'date'        => $date,
                'clock_in'    => $clockIn,
                'clock_out'   => $clockOut,
                'total_hours' => $totalHours,
                'synced_at'   => date('Y-m-d H:i:s'),
            ];

            self::$store['attendance'][$attendanceId] = $record;
            $synced++;
        }

        return $synced;
    }

    public function markAttendanceAsSynced(array $attendanceIds): int
    {
        if ($this->useDatabase && $this->connection && !empty($attendanceIds)) {
            try {
                $placeholders = implode(',', array_fill(0, count($attendanceIds), '?'));
                $query = "UPDATE attendance_logs SET sync_status = 'synced' WHERE id IN ({$placeholders})";
                $stmt  = $this->connection->prepare($query);
                $stmt->execute($attendanceIds);

                return $stmt->rowCount();
            } catch (PDOException $e) {
                error_log('Failed to mark attendance records as synced: ' . $e->getMessage());
            }
        }

        foreach ($attendanceIds as $attendanceId) {
            self::$store['attendance'][$attendanceId] = 'synced';
        }

        return count($attendanceIds);
    }

    private function loadEnvironment(): void
    {
        if (!class_exists('Dotenv\\Dotenv') && file_exists(__DIR__ . '/../../vendor/autoload.php')) {
            require_once __DIR__ . '/../../vendor/autoload.php';
        }

        if (class_exists('Dotenv\\Dotenv')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad();
        }
    }

    private function initializeDatabaseConnection(): void
    {
        $requiredKeys = [
            'DB_HOST',
            'DB_PORT',
            'DB_NAME',
            'DB_CHARSET',
            'DB_USER',
            'DB_PASS',
        ];

        foreach ($requiredKeys as $key) {
            if (trim((string) ($_ENV[$key] ?? getenv($key) ?? '')) === '') {
                return;
            }
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $_ENV['DB_HOST'] ?? getenv('DB_HOST'),
                $_ENV['DB_PORT'] ?? getenv('DB_PORT'),
                $_ENV['DB_NAME'] ?? getenv('DB_NAME'),
                $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET')
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->connection = new PDO(
                $dsn,
                $_ENV['DB_USER'] ?? getenv('DB_USER'),
                $_ENV['DB_PASS'] ?? getenv('DB_PASS'),
                $options
            );
            $this->useDatabase = true;
        } catch (PDOException $e) {
            error_log('GoogleSheetsModel DB connection failed: ' . $e->getMessage());
            $this->useDatabase = false;
        }
    }

    private function ensureConfigTableExists(): void
    {
        if (!$this->useDatabase || !$this->connection) {
            return;
        }

        $query = "CREATE TABLE IF NOT EXISTS google_sheets_settings (
            setting_key VARCHAR(100) PRIMARY KEY,
            setting_value TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        try {
            $this->connection->exec($query);
        } catch (PDOException $e) {
            error_log('Failed to ensure google_sheets_settings exists: ' . $e->getMessage());
        }
    }

    private function getSetting(string $key): ?string
    {
        if ($this->useDatabase && $this->connection) {
            try {
                $query = 'SELECT setting_value FROM google_sheets_settings WHERE setting_key = :key LIMIT 1';
                $stmt  = $this->connection->prepare($query);
                $stmt->execute([':key' => $key]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                return $result['setting_value'] ?? null;
            } catch (PDOException $e) {
                error_log('Failed to read google sheets setting: ' . $e->getMessage());
            }
        }

        return self::$store[$key] ?? null;
    }

    private function saveSetting(string $key, string $value): bool
    {
        if ($this->useDatabase && $this->connection) {
            try {
                $query = 'INSERT INTO google_sheets_settings (setting_key, setting_value)
                          VALUES (:key, :value)
                          ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)';
                $stmt  = $this->connection->prepare($query);
                return $stmt->execute([
                    ':key'   => $key,
                    ':value' => $value,
                ]);
            } catch (PDOException $e) {
                error_log('Failed to save google sheets setting: ' . $e->getMessage());
            }
        }

        self::$store[$key] = $value;
        return true;
    }
}

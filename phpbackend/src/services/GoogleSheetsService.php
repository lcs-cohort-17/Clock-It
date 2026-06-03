<?php
// src/services/GoogleSheetsService.php

require_once __DIR__ . '/../../vendor/autoload.php';

class GoogleSheetsService
{
    private bool $connected = false;
    private ?Google_Service_Sheets $sheetsService = null;
    private ?Google_Service_Drive $driveService = null;

    public function __construct()
    {
        $credentialsPath = __DIR__ . '/../../credentials.json';

        if (!file_exists($credentialsPath)) {
            return;
        }

        try {
            $client = new Google_Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope(Google_Service_Sheets::SPREADSHEETS);
            $client->addScope(Google_Service_Drive::DRIVE);

            $this->sheetsService = new Google_Service_Sheets($client);
            $this->driveService  = new Google_Service_Drive($client);
            $this->connected     = true;
        } catch (Exception $e) {
            $this->connected = false;
            error_log('Google Sheets Service connection failed: ' . $e->getMessage());
        }
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function createSpreadsheet(string $title): string
    {
        if (!$this->connected || empty($title)) {
            return '';
        }

        try {
            $spreadsheet = new Google_Service_Sheets_Spreadsheet([
                'properties' => ['title' => $title]
            ]);

            $response = $this->sheetsService->spreadsheets->create($spreadsheet);
            return $response->getSpreadsheetId();
        } catch (Exception $e) {
            error_log('Failed to create spreadsheet: ' . $e->getMessage());
            return '';
        }
    }

    public function writeAttendanceData(array $data): bool
    {
        if (!$this->connected || empty($data)) {
            return false;
        }

        try {
            // Only try to get sheet ID if GoogleSheetsModel exists
            if (class_exists('GoogleSheetsModel')) {
                $sheetId = (new GoogleSheetsModel())->getSheetId();
            } else {
                // For testing without database
                return false;
            }

            if (empty($sheetId)) {
                return false;
            }

            $rows = [['Employee ID', 'Event Type', 'Timestamp']];
            foreach ($data as $record) {
                $rows[] = [
                    $record['employee_id'] ?? '',
                    $record['event_type']  ?? '',
                    $record['timestamp']   ?? date('Y-m-d H:i:s'),
                ];
            }

            $body = new Google_Service_Sheets_ValueRange(['values' => $rows]);
            $this->sheetsService->spreadsheets_values->update(
                $sheetId,
                'A1',
                $body,
                ['valueInputOption' => 'RAW']
            );

            return true;
        } catch (Exception $e) {
            error_log('Failed to write attendance data: ' . $e->getMessage());
            return false;
        }
    }

    public function exportAttendance(array $attendanceData): string
    {
        if (!$this->connected) {
            throw new RuntimeException('Google Sheets is not connected. Check credentials.json.');
        }

        if (empty($attendanceData)) {
            throw new InvalidArgumentException('No attendance data supplied for export.');
        }

        $sheetId = $this->getConfiguredSpreadsheetId();
        $usedConfiguredSheet = !empty($sheetId);

        if (empty($sheetId)) {
            $sheetId = $this->createSpreadsheet('Attendance Export ' . date('Y-m-d H:i:s'));

            if (!empty($sheetId) && class_exists('GoogleSheetsModel')) {
                (new GoogleSheetsModel())->saveSheetId($sheetId);
            }
        }

        if (empty($sheetId)) {
            throw new RuntimeException('Unable to access or create a Google Sheet.');
        }

        $isLogExport = array_key_exists('timestamp', $attendanceData[0] ?? [])
            || array_key_exists('event_type', $attendanceData[0] ?? []);

        if ($isLogExport) {
            $rows = [[
                'Staff Name',
                'Event Type',
                'Timestamp',
                'Device',
                'Location',
                'Sync Status',
            ]];

            foreach ($attendanceData as $record) {
                $rows[] = [
                    $record['staff_name'] ?? $record['staff'] ?? '',
                    $record['type'] ?? $record['event_type'] ?? '',
                    $record['timestamp'] ?? '',
                    $record['device'] ?? '',
                    $record['location'] ?? '',
                    $record['sync'] ?? $record['sync_status'] ?? '',
                ];
            }
        } else {
            $rows = [[
                'Staff Name',
                'Date',
                'Clock In',
                'Clock Out',
                'Total Hours',
            ]];

            foreach ($attendanceData as $record) {
                $rows[] = [
                    $record['staff_name'] ?? '',
                    $record['date'] ?? '',
                    $record['clock_in'] ?? '',
                    $record['clock_out'] ?? '',
                    $record['total_hours'] ?? '',
                ];
            }
        }

        $wroteRows = $this->writeRows($sheetId, $rows);

        if (!$wroteRows && $usedConfiguredSheet) {
            $sheetId = $this->createSpreadsheet('Attendance Export ' . date('Y-m-d H:i:s'));

            if (!empty($sheetId) && class_exists('GoogleSheetsModel')) {
                (new GoogleSheetsModel())->saveSheetId($sheetId);
            }

            $wroteRows = !empty($sheetId) && $this->writeRows($sheetId, $rows);
        }

        if (!$wroteRows) {
            throw new RuntimeException('Unable to write attendance data to Google Sheet.');
        }

        $this->makeReadableByLink($sheetId);

        return $this->generateSheetUrl($sheetId);
    }

    private function getConfiguredSpreadsheetId(): string
    {
        if (class_exists('GoogleSheetsModel')) {
            $modelSheetId = (new GoogleSheetsModel())->getSheetId();

            if (!empty($modelSheetId)) {
                return $modelSheetId;
            }
        }

        return trim(
            $_ENV['GOOGLE_SHEETS_SPREADSHEET_ID']
            ?? getenv('GOOGLE_SHEETS_SPREADSHEET_ID')
            ?: ''
        );
    }

    private function writeRows(string $sheetId, array $rows): bool
    {
        try {
            $body = new Google_Service_Sheets_ValueRange(['values' => $rows]);

            $this->sheetsService->spreadsheets_values->update(
                $sheetId,
                'A1',
                $body,
                ['valueInputOption' => 'RAW']
            );

            return true;
        } catch (Exception $e) {
            error_log('Failed to write rows to spreadsheet: ' . $e->getMessage());
            return false;
        }
    }

    public function appendAttendanceRows(string $sheetId, array $rows): bool
    {
        if (!$this->connected || empty($sheetId) || empty($rows)) {
            return false;
        }

        try {
            $body = new Google_Service_Sheets_ValueRange(['values' => $rows]);

            $this->sheetsService->spreadsheets_values->append(
                $sheetId,
                'A1',
                $body,
                [
                    'valueInputOption'  => 'RAW',
                    'insertDataOption'  => 'INSERT_ROWS',
                ]
            );

            return true;
        } catch (Exception $e) {
            error_log('Failed to append rows to spreadsheet: ' . $e->getMessage());
            return false;
        }
    }

    private function makeReadableByLink(string $sheetId): void
    {
        if (!$this->driveService) {
            return;
        }

        try {
            $permission = new Google_Service_Drive_Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);

            $this->driveService->permissions->create($sheetId, $permission);
        } catch (Exception $e) {
            error_log('Failed to update spreadsheet sharing: ' . $e->getMessage());
        }
    }

    public function readAttendanceData(): array
    {
        if (!$this->connected) {
            return [];
        }

        try {
            if (class_exists('GoogleSheetsModel')) {
                $sheetId = (new GoogleSheetsModel())->getSheetId();
            } else {
                return [];
            }

            if (empty($sheetId)) {
                return [];
            }

            $response = $this->sheetsService->spreadsheets_values->get(
                $sheetId,
                'A1:Z'
            );

            return $response->getValues() ?? [];
        } catch (Exception $e) {
            error_log('Failed to read attendance data: ' . $e->getMessage());
            return [];
        }
    }

    public function generateSheetUrl(string $sheetId): string
    {
        if (empty($sheetId)) {
            return '';
        }

        return "https://docs.google.com/spreadsheets/d/{$sheetId}";
    }
}

<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class GoogleSheetsService
{
    private Google\Client $client;
    private Google\Service\Sheets $service;
    private ?string $spreadsheetId;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/Google.php';

        $credentialsPath = $config['credentials_path'];

        if (!file_exists($credentialsPath)) {
            throw new RuntimeException(
                'credentials.json not found. Download your service account key from ' .
                    'Google Cloud Console > IAM & Admin > Service Accounts > Keys.'
            );
        }

        $this->client = new Google\Client();
        $this->client->setApplicationName($config['application_name']);
        $this->client->setScopes([
            Google\Service\Sheets::SPREADSHEETS,
            Google\Service\Drive::DRIVE_FILE,
        ]);
        $this->client->setAuthConfig($credentialsPath);

        $this->service = new Google\Service\Sheets($this->client);
        $this->spreadsheetId = $config['spreadsheet_id'] ?? null;
    }

    /**
     * Write attendance data to the configured Google Sheet.
     * Returns the URL of the spreadsheet.
     *
     * @param  array $attendanceData  Rows from AttendanceController::exportToSheets()
     * @return string                 Google Sheets URL
     * @throws Google\Service\Exception
     */
    public function exportAttendance(array $attendanceData): string
    {
        if (!empty($this->spreadsheetId)) {
            $spreadsheetId = $this->spreadsheetId;
        } else {
            // Create the spreadsheet when no configured sheet ID is available.
            $spreadsheet = new Google\Service\Sheets\Spreadsheet([
                'properties' => [
                    'title' => 'Clock It - Attendance Export ' . date('Y-m-d H:i:s'),
                ],
            ]);

            $spreadsheet   = $this->service->spreadsheets->create($spreadsheet);
            $spreadsheetId = $spreadsheet->spreadsheetId;
        }

        // Build rows: header first, then data
        $rows   = [];
        $rows[] = ['Staff Name', 'Date', 'Clock-In', 'Clock-Out', 'Total Hours'];

        foreach ($attendanceData as $record) {
            $rows[] = [
                $record['staff_name'],
                $record['date'],
                $record['clock_in'],
                $record['clock_out'],
                $record['total_hours'],
            ];
        }

        $body = new Google\Service\Sheets\ValueRange(['values' => $rows]);

        $this->service->spreadsheets_values->update(
            $spreadsheetId,
            'A1',
            $body,
            ['valueInputOption' => 'RAW']
        );

        return "https://docs.google.com/spreadsheets/d/{$spreadsheetId}";
    }

    public function isConnected(): bool
    {
        // TDD: This will fail the test - implement connection check
        return false;
    }

    public function createSpreadsheet(string $title): string
    {
        // TDD: This will fail the test - implement spreadsheet creation
        return '';
    }

    public function writeAttendanceData(array $data): bool
    {
        // TDD: This will fail the test - implement write logic
        return false;
    }

    public function readAttendanceData(): array
    {
        // TDD: This will fail the test - implement read logic
        return [];
    }

    public function generateSheetUrl(string $sheetId): string
    {
        // TDD: This will fail the test - implement URL generation
        return '';
    }
}

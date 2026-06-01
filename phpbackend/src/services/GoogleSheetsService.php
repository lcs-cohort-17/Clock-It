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
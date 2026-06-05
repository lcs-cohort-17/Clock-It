<?php
// src/services/GoogleSheetsService.php

$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

class GoogleSheetsService
{
    private bool $connected = false;
    private ?Google_Service_Sheets $sheetsService = null;
    private ?Google_Service_Drive $driveService = null;
    private string $spreadsheetId = '';

    public function __construct(?string $spreadsheetId = null)
    {
        $this->spreadsheetId = trim((string) ($spreadsheetId ?? $this->loadSpreadsheetId()));

        if (!class_exists('Google_Client')) {
            error_log('Google API PHP client not installed. Run composer install in phpbackend.');
            return;
        }

        $credentialsPath = $this->credentialsPath();

        if (!is_file($credentialsPath)) {
            error_log('Google service account credentials not found at ' . $credentialsPath);
            return;
        }

        try {
            $client = new Google_Client();
            $client->setApplicationName('Clock-It Attendance');
            $client->setAuthConfig($credentialsPath);
            $client->setScopes([
                Google_Service_Sheets::SPREADSHEETS,
                Google_Service_Drive::DRIVE_FILE,
            ]);

            $this->sheetsService = new Google_Service_Sheets($client);
            $this->driveService  = new Google_Service_Drive($client);
            $this->connected     = true;
        } catch (Throwable $e) {
            $this->connected = false;
            error_log('Google Sheets connection failed: ' . $e->getMessage());
        }
    }

    // ── Public API ───────────────────────────────────────────────────────────

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function spreadsheetId(): string
    {
        return $this->spreadsheetId;
    }

    public function setSpreadsheetId(string $spreadsheetId): void
    {
        $this->spreadsheetId = trim($spreadsheetId);
    }

    public function readRows(string $range = 'A1:Z'): array
    {
        if (!$this->connected || $this->spreadsheetId === '') {
            return [];
        }

        try {
            $response = $this->sheetsService->spreadsheets_values->get($this->spreadsheetId, $range);
            return $response->getValues() ?? [];
        } catch (Throwable $e) {
            error_log('Google Sheets read failed: ' . $e->getMessage());
            return [];
        }
    }

    public function replaceRows(array $rows, string $range = 'A1'): bool
    {
        if (!$this->connected || $this->spreadsheetId === '' || empty($rows)) {
            return false;
        }

        try {
            $body       = new Google_Service_Sheets_ValueRange(['values' => $rows]);
            $clearRange = str_contains($range, '!')
                ? preg_replace('/!.*/', '!A:Z', $range)
                : 'A:Z';

            $this->sheetsService->spreadsheets_values->clear(
                $this->spreadsheetId,
                $clearRange ?: 'A:Z',
                new Google_Service_Sheets_ClearValuesRequest()
            );
            $this->sheetsService->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                ['valueInputOption' => 'USER_ENTERED']
            );
            return true;
        } catch (Throwable $e) {
            error_log('Google Sheets replace rows failed: ' . $e->getMessage());
            return false;
        }
    }

    public function appendRows(array $rows, string $range = 'A1'): bool
    {
        if (!$this->connected || $this->spreadsheetId === '' || empty($rows)) {
            return false;
        }

        try {
            $body = new Google_Service_Sheets_ValueRange(['values' => $rows]);
            $this->sheetsService->spreadsheets_values->append(
                $this->spreadsheetId,
                $range,
                $body,
                [
                    'valueInputOption' => 'USER_ENTERED',
                    'insertDataOption' => 'INSERT_ROWS',
                ]
            );
            return true;
        } catch (Throwable $e) {
            error_log('Google Sheets append rows failed: ' . $e->getMessage());
            return false;
        }
    }

    public function createSpreadsheet(string $title): string
    {
        if (!$this->connected) {
            return '';
        }

        try {
            $spreadsheet = new Google_Service_Sheets_Spreadsheet([
                'properties' => ['title' => $title],
            ]);
            $response            = $this->sheetsService->spreadsheets->create($spreadsheet);
            $this->spreadsheetId = (string) $response->getSpreadsheetId();
            return $this->spreadsheetId;
        } catch (Throwable $e) {
            error_log('Google Sheets create spreadsheet failed: ' . $e->getMessage());
            return '';
        }
    }

    public function shareReadableByLink(): void
    {
        if (!$this->driveService || $this->spreadsheetId === '') {
            return;
        }

        try {
            $permission = new Google_Service_Drive_Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);
            $this->driveService->permissions->create($this->spreadsheetId, $permission);
        } catch (Throwable $e) {
            error_log('Google Sheets share permission failed: ' . $e->getMessage());
        }
    }

    public function url(): string
    {
        return $this->spreadsheetId === ''
            ? ''
            : 'https://docs.google.com/spreadsheets/d/' . $this->spreadsheetId;
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Read a value from the .env file or fall back to a real PHP env var /
     * $_ENV / $_SERVER — in that priority order.
     *
     * This makes GoogleSheetsService self-contained: it no longer depends on
     * Database::env(), which is not guaranteed to exist.
     */
    private static function env(string $key): string
    {
        // 1. Try to parse the .env file that sits two directories above this file
        //    (phpbackend/.env).  We parse it ourselves so this class has no
        //    dependency on any framework or the Database class.
        static $envCache = null;

        if ($envCache === null) {
            $envCache = [];
            $envFile  = __DIR__ . '/../../.env';

            if (is_file($envFile)) {
                foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                    $line = trim($line);
                    // Skip comments and lines without an = sign
                    if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                        continue;
                    }
                    [$envKey, $envVal] = explode('=', $line, 2);
                    $envKey = trim($envKey);
                    // Strip optional surrounding quotes from the value
                    $envVal = trim(trim($envVal), '"\'');
                    if ($envKey !== '') {
                        $envCache[$envKey] = $envVal;
                    }
                }
            }
        }

        if (isset($envCache[$key]) && $envCache[$key] !== '') {
            return $envCache[$key];
        }

        // 2. Fall back to putenv() / $_ENV / $_SERVER (set by the web server or CLI)
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        return (string) ($_ENV[$key] ?? $_SERVER[$key] ?? '');
    }

    private function credentialsPath(): string
    {
        $configured = self::env('GOOGLE_APPLICATION_CREDENTIALS')
            ?: self::env('GOOGLE_SHEETS_CREDENTIALS_PATH')
            ?: '';

        if ($configured !== '') {
            // Absolute path → use as-is; relative path → resolve from project root
            if (str_starts_with($configured, DIRECTORY_SEPARATOR)
                || (strlen($configured) > 1 && $configured[1] === ':') // Windows C:\...
            ) {
                return $configured;
            }
            return realpath(__DIR__ . '/../../' . $configured)
                ?: (__DIR__ . '/../../' . $configured);
        }

        // Default: credentials.json in the project root (phpbackend/)
        return __DIR__ . '/../../credentials.json';
    }

    private function loadSpreadsheetId(): string
    {
        $fromEnv = self::env('GOOGLE_SHEETS_SPREADSHEET_ID');
        if ($fromEnv !== '') {
            return $fromEnv;
        }

        // Optional fallback: src/config/Google.php returning ['spreadsheet_id' => '...']
        $configFile = __DIR__ . '/../config/Google.php';
        if (is_file($configFile)) {
            $config = require $configFile;
            if (!empty($config['spreadsheet_id'])) {
                return (string) $config['spreadsheet_id'];
            }
        }

        return '';
    }
}
<?php

class GoogleSheetsModel
{
    private string $storageFile;

    public function __construct()
    {
        $this->storageFile = sys_get_temp_dir() . '/clockit_google_sheets.json';
    }

    public function saveSyncFrequency(string $frequency): bool
    {
        $data = $this->loadData();
        $data['sync_frequency'] = $frequency;

        return $this->saveData($data);
    }

    public function getSyncFrequency(): ?string
    {
        $data = $this->loadData();

        return $data['sync_frequency'] ?? null;
    }

    public function saveSheetId(string $sheetId): bool
    {
        $data = $this->loadData();
        $data['sheet_id'] = $sheetId;

        return $this->saveData($data);
    }

    public function getSheetId(): ?string
    {
        $data = $this->loadData();

        return $data['sheet_id'] ?? null;
    }

    public function updateAttendanceSyncStatus(string $attendanceId, string $status): bool
    {
        return true;
    }

    public function getPendingAttendance(): array
    {
        return [];
    }

    public function getAttendanceByDateRange(string $startDate, string $endDate): array
    {
        return [];
    }

    private function loadData(): array
    {
        if (!file_exists($this->storageFile)) {
            return [];
        }

        $contents = file_get_contents($this->storageFile);
        $data = json_decode($contents, true);

        return is_array($data) ? $data : [];
    }

    private function saveData(array $data): bool
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);

        return file_put_contents($this->storageFile, $json) !== false;
    }
}

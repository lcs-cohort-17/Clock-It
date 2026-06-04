<?php
// src/models/GoogleSheetsModel.php

class GoogleSheetsModel
{
    private static array $store = [
        'sync_frequency' => null,
        'sheet_id'       => null,
        'attendance'     => [],
    ];

    public function saveSyncFrequency(string $frequency): bool
    {
        self::$store['sync_frequency'] = $frequency;
        return true;
    }

    public function getSyncFrequency(): ?string
    {
        return self::$store['sync_frequency'];
    }

    public function updateAttendanceSyncStatus(string $attendanceId, string $status): bool
    {
        self::$store['attendance'][$attendanceId] = $status;
        return true;
    }

    public function getPendingAttendance(): array
    {
        return array_filter(
            self::$store['attendance'],
            fn($status) => $status === 'pending'
        );
    }

    public function getAttendanceByDateRange(string $startDate, string $endDate): array
    {
        return self::$store['attendance'];
    }

    public function saveSheetId(string $sheetId): bool
    {
        self::$store['sheet_id'] = $sheetId;
        return true;
    }

    public function getSheetId(): ?string
    {
        return self::$store['sheet_id'];
    }
}
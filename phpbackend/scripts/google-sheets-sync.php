<?php
// phpbackend/scripts/google-sheets-sync.php

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

require_once __DIR__ . '/../src/models/GoogleSheetsModel.php';
require_once __DIR__ . '/../src/services/GoogleSheetsService.php';

$googleSheetsService = new GoogleSheetsService();
$googleSheetsModel   = new GoogleSheetsModel();

if (!$googleSheetsService->isConnected()) {
    fwrite(STDERR, "Google Sheets service is not connected.\n");
    exit(1);
}

$pendingRecords = $googleSheetsModel->getPendingAttendance();

if (empty($pendingRecords)) {
    fwrite(STDOUT, "No pending attendance records to sync.\n");
    exit(0);
}

$sheetId = $googleSheetsModel->getSheetId();
$needsHeader = false;

if (empty($sheetId)) {
    $sheetId = $googleSheetsService->createSpreadsheet('Attendance Sync ' . date('Y-m-d H:i:s'));
    if (empty($sheetId)) {
        fwrite(STDERR, "Unable to create a Google Sheet for attendance sync.\n");
        exit(1);
    }

    $googleSheetsModel->saveSheetId($sheetId);
    $needsHeader = true;
}

$rows = [];
if ($needsHeader) {
    $rows[] = ['Staff Name', 'Event Type', 'Timestamp'];
}

foreach ($pendingRecords as $record) {
    $rows[] = [
        trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')),
        $record['event_type'] ?? '',
        $record['event_time'] ?? '',
    ];
}

if (!$googleSheetsService->appendAttendanceRows($sheetId, $rows)) {
    fwrite(STDERR, "Failed to append pending attendance rows to Google Sheet.\n");
    exit(1);
}

$attendanceIds = array_column($pendingRecords, 'id');
$updatedCount  = $googleSheetsModel->markAttendanceAsSynced($attendanceIds);

fwrite(STDOUT, "Successfully pushed {$updatedCount} pending record(s) to Google Sheet {$sheetId}.\n");
exit(0);

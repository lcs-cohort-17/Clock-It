<?php
// src/controllers/GoogleSheetsController.php

require_once __DIR__ . '/../validators/SyncFrequencyValidator.php';

class GoogleSheetsController
{
    private GoogleSheetsService $service;
    private GoogleSheetsModel $model;

    public function __construct(
        ?GoogleSheetsService $service = null,
        ?GoogleSheetsModel $model = null,
        ?PDO $dbConnection = null
    ) {
        $this->service = $service ?? new GoogleSheetsService();
        $this->model   = $model   ?? new GoogleSheetsModel($dbConnection);
    }

    public function status(): array
    {
        return [
            'connected'      => $this->service->isConnected(),
            'sheet_id'       => $this->model->getSheetId(),
            'sync_frequency' => $this->model->getSyncFrequency(),
        ];
    }

    public function getSettings(): array
    {
        return [
            'connected'      => $this->service->isConnected(),
            'sheet_id'       => $this->model->getSheetId(),
            'sync_frequency' => $this->model->getSyncFrequency(),
        ];
    }

    public function connect(): array
    {
        return [
            'success' => true,
            'message' => 'Service account integration is managed server-side; no manual connect step is required.',
        ];
    }

    public function export(array $params): array
    {
        if (empty($params['start_date']) || empty($params['end_date'])) {
            throw new InvalidArgumentException('Date range required');
        }

        $data    = $this->model->getAttendanceByDateRange(
            $params['start_date'],
            $params['end_date']
        );
        $sheetId = $this->service->createSpreadsheet('Attendance Export');

        if (empty($sheetId)) {
            throw new RuntimeException('Unable to create Google Sheet');
        }

        $this->model->saveSheetId($sheetId);
        $this->service->writeAttendanceData($data);

        return ['sheet_url' => $this->service->generateSheetUrl($sheetId)];
    }

    public function sync(): array
    {
        if (!$this->service->isConnected()) {
            throw new RuntimeException('Google Sheets is not connected');
        }

        $rows = $this->service->readAttendanceData();

        if (empty($rows)) {
            return [
                'success'        => true,
                'records_synced' => 0,
                'message'        => 'No data found in sheet to sync',
            ];
        }

        $synced = $this->model->syncAttendanceFromSheet($rows);

        return [
            'success'        => true,
            'records_synced' => $synced,
            'message'        => "Successfully synced {$synced} record(s) from Google Sheet",
        ];
    }

    public function pushPendingAttendance(): array
    {
        if (!$this->service->isConnected()) {
            throw new RuntimeException('Google Sheets is not connected');
        }

        $pendingRecords = $this->model->getPendingAttendance();

        if (empty($pendingRecords)) {
            return [
                'success'          => true,
                'records_pushed'   => 0,
                'message'          => 'No pending attendance records to push.',
            ];
        }

        $sheetId = $this->model->getSheetId();
        $needsHeader = false;

        if (empty($sheetId)) {
            $sheetId = $this->service->createSpreadsheet('Attendance Sync ' . date('Y-m-d H:i:s'));
            $this->model->saveSheetId($sheetId);
            $needsHeader = true;
        }

        if (empty($sheetId)) {
            throw new RuntimeException('Unable to determine or create a Google Sheet for syncing attendance.');
        }

        $rows = [];

        if ($needsHeader) {
            $rows[] = [
                'Staff Name',
                'Event Type',
                'Timestamp',
            ];
        }

        foreach ($pendingRecords as $record) {
            $rows[] = [
                trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')),
                $record['event_type'] ?? '',
                $record['event_time'] ?? '',
            ];
        }

        if (!$this->service->appendAttendanceRows($sheetId, $rows)) {
            throw new RuntimeException('Unable to write pending attendance data to Google Sheet.');
        }

        $attendanceIds = array_column($pendingRecords, 'id');
        $updatedCount  = $this->model->markAttendanceAsSynced($attendanceIds);

        return [
            'success'        => true,
            'records_pushed' => $updatedCount,
            'message'        => "Successfully pushed {$updatedCount} pending attendance record(s)",
        ];
    }

    public function disconnect(): array
    {
        $this->model->saveSheetId('');
        return ['success' => true];
    }

    public function updateSettings(array $params): array
    {
        $hasFrequency = isset($params['sync_frequency']);
        $hasSheetId   = isset($params['sheet_id']);

        if (!$hasFrequency && !$hasSheetId) {
            throw new InvalidArgumentException('At least one setting is required.');
        }

        if ($hasFrequency) {
            if (!SyncFrequencyValidator::isValid($params['sync_frequency'])) {
                throw new InvalidArgumentException('Invalid sync frequency');
            }
            $this->model->saveSyncFrequency($params['sync_frequency']);
        }

        if ($hasSheetId) {
            $this->model->saveSheetId(trim((string) $params['sheet_id']));
        }

        return ['success' => true];
    }
}

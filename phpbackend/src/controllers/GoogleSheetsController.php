<?php
// src/controllers/GoogleSheetsController.php

class GoogleSheetsController
{
    private GoogleSheetsService $service;
    private GoogleSheetsModel $model;

    public function __construct(
        ?GoogleSheetsService $service = null,
        ?GoogleSheetsModel $model = null
    ) {
        $this->service = $service ?? new GoogleSheetsService();
        $this->model   = $model   ?? new GoogleSheetsModel();
    }

    public function status(): array
    {
        return ['connected' => $this->service->isConnected()];
    }

    public function export(array $params): array
    {
        if (empty($params['start_date']) || empty($params['end_date'])) {
            throw new InvalidArgumentException('Date range required');
        }

        $data     = $this->model->getAttendanceByDateRange(
            $params['start_date'],
            $params['end_date']
        );
        $sheetId  = $this->service->createSpreadsheet('Attendance Export');

        if (empty($sheetId)) {
            throw new RuntimeException('Unable to create Google Sheet');
        }

        $this->model->saveSheetId($sheetId);
        $this->service->writeAttendanceData($data);

        return ['sheet_url' => $this->service->generateSheetUrl($sheetId)];
    }

    public function sync(): array
    {
        $pending = $this->model->getPendingAttendance();
        $result  = $this->service->writeAttendanceData($pending);

        return ['success' => $result];
    }

    public function disconnect(): array
    {
        $this->model->saveSheetId('');
        return ['success' => true];
    }

    public function updateSettings(array $params): array
    {
        if (empty($params['sync_frequency']) ||
            !SyncFrequencyValidator::isValid($params['sync_frequency'])) {
            throw new InvalidArgumentException('Invalid sync frequency');
        }

        $result = $this->model->saveSyncFrequency($params['sync_frequency']);
        return ['success' => $result];
    }
}

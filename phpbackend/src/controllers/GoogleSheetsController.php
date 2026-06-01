<?php

require_once __DIR__ . '/../models/GoogleSheetsModel.php';

class GoogleSheetsController
{
    public function status(): array
    {
        // TDD: This will fail the test - implement to return actual status
        return ['connected' => false];
    }

    public function export(array $params): array
    {
        // TDD: This will fail - implement export logic
        if (empty($params['start_date']) || empty($params['end_date'])) {
            throw new InvalidArgumentException('Date range is required');
        }

        return ['sheet_url' => ''];
    }

    public function sync(): array
    {
        // TDD: This will fail - implement sync logic
        return ['success' => false];
    }
    public function disconnect(): array
    {
        $model = new GoogleSheetsModel();
        $success = true;

        if (method_exists($model, 'saveSheetId')) {
            $success = $model->saveSheetId('') === true;
        }

        return ['success' => $success];
    }

    public function updateSettings(array $settings): array
    {
        if (empty($settings['sync_frequency'])) {
            throw new InvalidArgumentException('Sync frequency is required');
        }

        $syncFrequency = $settings['sync_frequency'];

        if (!preg_match('/^[1-9]\d*[smhd]$/i', $syncFrequency)) {
            throw new InvalidArgumentException('Invalid sync frequency');
        }

        $model = new GoogleSheetsModel();
        $success = true;

        if (method_exists($model, 'saveSyncFrequency')) {
            $success = $model->saveSyncFrequency($syncFrequency);
        }

        return ['success' => $success === true];
    }}

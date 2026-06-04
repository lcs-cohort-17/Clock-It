<?php

use PHPUnit\Framework\TestCase;

class GoogleSheetsModelTest extends TestCase
{
    private GoogleSheetsModel $model;

    protected function setUp(): void
    {
        $this->model = new GoogleSheetsModel();
    }

    public function testCanSaveSyncFrequency(): void
    {
        $result = $this->model->saveSyncFrequency('15m');

        $this->assertTrue($result);
    }

    public function testCanRetrieveSyncFrequency(): void
    {
        $frequency = $this->model->getSyncFrequency();

        $this->assertNotNull($frequency);
    }

    public function testCanMarkAttendanceAsSynced(): void
    {
        $result = $this->model->updateAttendanceSyncStatus(
            'attendance-id',
            'synced'
        );

        $this->assertTrue($result);
    }

    public function testCanRetrievePendingAttendanceRecords(): void
    {
        $records = $this->model->getPendingAttendance();

        $this->assertIsArray($records);
    }

    public function testCanRetrieveAttendanceByDateRange(): void
    {
        $records = $this->model->getAttendanceByDateRange(
            '2026-01-01',
            '2026-01-31'
        );

        $this->assertIsArray($records);
    }

    public function testCanStoreSheetId(): void
    {
        $result = $this->model->saveSheetId(
            'sheet-id-123'
        );

        $this->assertTrue($result);
    }

    public function testCanRetrieveSheetId(): void
    {
        $sheetId = $this->model->getSheetId();

        $this->assertNotNull($sheetId);
    }
}
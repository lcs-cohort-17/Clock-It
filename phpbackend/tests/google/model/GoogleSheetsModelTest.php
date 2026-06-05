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

    public function testCanSyncAttendanceFromSheetReturnsZeroForEmptyRows(): void
    {
        $result = $this->model->syncAttendanceFromSheet([]);

        $this->assertEquals(0, $result);
    }

    public function testCanSyncAttendanceFromSheetSkipsHeaderRow(): void
    {
        $rows = [
            ['Staff Name', 'Date', 'Clock In', 'Clock Out', 'Total Hours'],
        ];

        $result = $this->model->syncAttendanceFromSheet($rows);

        $this->assertEquals(0, $result);
    }

    public function testCanSyncAttendanceFromSheetParsesValidRecords(): void
    {
        $rows = [
            ['Staff Name', 'Date', 'Clock In', 'Clock Out', 'Total Hours'],
            ['John Doe', '2026-01-15', '09:00', '17:00', '8'],
            ['Jane Smith', '2026-01-15', '08:30', '16:30', '8'],
        ];

        $result = $this->model->syncAttendanceFromSheet($rows);

        $this->assertEquals(2, $result);
    }

    public function testCanSyncAttendanceFromSheetSkipsInvalidRows(): void
    {
        $rows = [
            ['Staff Name', 'Date', 'Clock In', 'Clock Out', 'Total Hours'],
            ['John Doe', '2026-01-15', '09:00', '17:00', '8'],
            ['', '2026-01-15', '08:30', '16:30', '8'],  // Missing staff name
            ['Jane Smith', '', '08:30', '16:30', '8'],  // Missing date
        ];

        $result = $this->model->syncAttendanceFromSheet($rows);

        $this->assertEquals(1, $result);  // Only first record is valid
    }
}
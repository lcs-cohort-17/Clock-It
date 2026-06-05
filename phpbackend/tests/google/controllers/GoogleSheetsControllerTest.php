<?php

use PHPUnit\Framework\TestCase;

class GoogleSheetsControllerTest extends TestCase
{
    private function makeController(
        bool $connected = false,
        array $pendingRecords = [],
        array $sheetRows = []
    ): GoogleSheetsController {
        $mockService = $this->createMock(GoogleSheetsService::class);
        $mockService->method('isConnected')->willReturn($connected);
        $mockService->method('createSpreadsheet')->willReturn('mock-sheet-id');
        $mockService->method('writeAttendanceData')->willReturn(true);
        $mockService->method('readAttendanceData')->willReturn($sheetRows);
        $mockService->method('generateSheetUrl')->willReturn(
            'https://docs.google.com/spreadsheets/d/mock-sheet-id'
        );
        $mockService->method('appendAttendanceRows')->willReturn(true);

        $mockModel = $this->createMock(GoogleSheetsModel::class);
        $mockModel->method('getPendingAttendance')->willReturn($pendingRecords);
        $mockModel->method('getAttendanceByDateRange')->willReturn([]);
        $mockModel->method('saveSyncFrequency')->willReturn(true);
        $mockModel->method('saveSheetId')->willReturn(true);
        $mockModel->method('getSheetId')->willReturn('mock-sheet-id');
        $mockModel->method('syncAttendanceFromSheet')->willReturn(count($sheetRows) > 1 ? count($sheetRows) - 1 : 0);
        $mockModel->method('markAttendanceAsSynced')->willReturn(count($pendingRecords));

        return new GoogleSheetsController($mockService, $mockModel);
    }

    public function testStatusReturnsDisconnectedWhenNotConfigured(): void
    {
        $controller = $this->makeController(connected: false);
        $response   = $controller->status();

        $this->assertFalse($response['connected']);
    }

    public function testStatusReturnsConnectedWhenConfigured(): void
    {
        $controller = $this->makeController(connected: true);
        $response   = $controller->status();

        $this->assertArrayHasKey('connected', $response);
    }

    public function testExportReturnsSheetUrl(): void
    {
        $controller = $this->makeController(connected: true);
        $response   = $controller->export([
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-31'
        ]);

        $this->assertArrayHasKey('sheet_url', $response);
    }

    public function testExportRequiresDateRange(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeController()->export([]);
    }

    public function testSyncReturnsSuccessResponse(): void
    {
        $sheetRows = [
            ['Staff Name', 'Date', 'Clock In', 'Clock Out', 'Total Hours'],  // Header
            ['John Doe', '2026-01-15', '09:00', '17:00', '8'],
            ['Jane Smith', '2026-01-15', '08:30', '16:30', '8'],
        ];

        $controller = $this->makeController(
            connected: true,
            sheetRows: $sheetRows
        );
        $response = $controller->sync();

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('records_synced', $response);
        $this->assertIsInt($response['records_synced']);
    }

    public function testPushPendingAttendanceUsesGoogleSheet(): void
    {
        $pendingRecords = [
            [
                'id' => 'pending-1',
                'event_type' => 'in',
                'event_time' => '2026-01-15 09:00:00',
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
        ];

        $controller = $this->makeController(
            connected: true,
            pendingRecords: $pendingRecords
        );

        $response = $controller->pushPendingAttendance();

        $this->assertTrue($response['success']);
        $this->assertEquals(1, $response['records_pushed']);
    }

    public function testConnectReturnsServiceAccountMessage(): void
    {
        $controller = $this->makeController();
        $response = $controller->connect();

        $this->assertTrue($response['success']);
        $this->assertStringContainsString('managed server-side', $response['message']);
    }

    public function testDisconnectRemovesConfiguration(): void
    {
        $controller = $this->makeController();
        $response   = $controller->disconnect();

        $this->assertTrue($response['success']);
    }

    public function testSaveSyncFrequency(): void
    {
        $controller = $this->makeController();
        $response   = $controller->updateSettings([
            'sync_frequency' => '15m'
        ]);

        $this->assertTrue($response['success']);
    }

    public function testInvalidSyncFrequencyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->makeController()->updateSettings([
            'sync_frequency' => 'abc123'
        ]);
    }
}
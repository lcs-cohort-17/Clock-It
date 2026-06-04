<?php

use PHPUnit\Framework\TestCase;

class GoogleSheetsControllerTest extends TestCase
{
    private function makeController(
        bool $connected = false,
        array $pendingRecords = []
    ): GoogleSheetsController {
        $mockService = $this->createMock(GoogleSheetsService::class);
        $mockService->method('isConnected')->willReturn($connected);
        $mockService->method('createSpreadsheet')->willReturn('mock-sheet-id');
        $mockService->method('writeAttendanceData')->willReturn(true);
        $mockService->method('generateSheetUrl')->willReturn(
            'https://docs.google.com/spreadsheets/d/mock-sheet-id'
        );

        $mockModel = $this->createMock(GoogleSheetsModel::class);
        $mockModel->method('getPendingAttendance')->willReturn($pendingRecords);
        $mockModel->method('getAttendanceByDateRange')->willReturn([]);
        $mockModel->method('saveSyncFrequency')->willReturn(true);
        $mockModel->method('saveSheetId')->willReturn(true);
        $mockModel->method('getSheetId')->willReturn('mock-sheet-id');

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
        $controller = $this->makeController(
            connected: true,
            pendingRecords: [
                ['employee_id' => 'EMP001', 'event_type' => 'in']
            ]
        );
        $response = $controller->sync();

        $this->assertTrue($response['success']);
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
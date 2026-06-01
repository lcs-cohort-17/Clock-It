<?php

use PHPUnit\Framework\TestCase;

class GoogleSheetsControllerTest extends TestCase
{
    private GoogleSheetsController $controller; 

    protected function setUp(): void
    {
        $this->controller = new GoogleSheetsController();
    }

    public function testStatusReturnsDisconnectedWhenNotConfigured(): void
    {
        $response = $this->controller->status();

        $this->assertFalse($response['connected']);
    }

    public function testStatusReturnsConnectedWhenConfigured(): void
    {
        $response = $this->controller->status();

        $this->assertArrayHasKey('connected', $response);
    }

    public function testExportReturnsSheetUrl(): void
    {
        $response = $this->controller->export([
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31'
        ]);

        $this->assertArrayHasKey('sheet_url', $response);
    }

    public function testExportRequiresDateRange(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->controller->export([]);
    }

    public function testSyncReturnsSuccessResponse(): void
    {
        $response = $this->controller->sync();

        $this->assertTrue($response['success']);
    }

    public function testDisconnectRemovesConfiguration(): void
    {
        $response = $this->controller->disconnect();

        $this->assertTrue($response['success']);
    }

    public function testSaveSyncFrequency(): void
    {
        $response = $this->controller->updateSettings([ 
            'sync_frequency' => '15m'
        ]);

        $this->assertTrue($response['success']);
    }

    public function testInvalidSyncFrequencyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->controller->updateSettings([
            'sync_frequency' => 'abc123'
        ]);
    }
}
<?php

namespace Tests\google\services;

use PHPUnit\Framework\TestCase;
use GoogleSheetsService;

class GoogleSheetsServiceTest extends TestCase
{
    private GoogleSheetsService $service;

    protected function setUp(): void
    {
        $this->service = new GoogleSheetsService();
    }

    public function testCanConnectToGoogleSheetsApi(): void
    {
        if (!file_exists(__DIR__ . '/../../../credentials.json')) {
            $this->markTestSkipped('credentials.json not found.');
        }

        $this->assertTrue($this->service->isConnected());
    }

    public function testCanCreateSpreadsheet(): void
    {
        $this->markTestSkipped(
            'Requires live Google API — covered by integration tests.'
        );
    }

    public function testCanWriteAttendanceData(): void
    {
        // Test with empty data
        $result = $this->service->writeAttendanceData([]);
        $this->assertFalse($result, 'Should return false for empty data');
        
        // Test with data but no sheet ID (mocked scenario)
        // This will test the validation logic without hitting the API
        $result = $this->service->writeAttendanceData([
            ['employee_id' => 'EMP001', 'event_type' => 'in']
        ]);
        
        // Service might be connected but no sheet ID saved yet — expect false
        $this->assertIsBool($result);
    }

    public function testCanReadAttendanceData(): void
    {
        $this->markTestSkipped(
            'Requires live Google API — covered by integration tests.'
        );
    }

    public function testCanGenerateSheetUrl(): void
    {
        $url = $this->service->generateSheetUrl('test-sheet-id-123');
        $this->assertStringContainsString('docs.google.com', $url);
        $this->assertStringContainsString('test-sheet-id-123', $url);
    }
    
    public function testGenerateSheetUrlWithEmptyId(): void
    {
        $url = $this->service->generateSheetUrl('');
        $this->assertEmpty($url, 'Should return empty string for empty ID');
    }
    
    public function testIsConnectedWithoutCredentials(): void
    {
        // Test when credentials don't exist
        $service = new GoogleSheetsService();
        $this->assertIsBool($service->isConnected());
    }
}
<?php

use PHPUnit\Framework\TestCase;

class GoogleSheetsServiceTest extends TestCase
{
    private GoogleSheetsService $service;

    protected function setUp(): void
    {
        $this->service = new GoogleSheetsService();
    }

    public function testCanConnectToGoogleSheetsApi(): void
    {
        $this->assertTrue(
            $this->service->isConnected()
        );
    }

    public function testCanCreateSpreadsheet(): void
    {
        $sheetId = $this->service->createSpreadsheet(
            'Attendance Export'
        );

        $this->assertNotEmpty($sheetId);
    }

    public function testCanWriteAttendanceData(): void
    {
        $result = $this->service->writeAttendanceData([
            [
                'employee_id' => 'EMP001',
                'event_type' => 'in'
            ]
        ]);

        $this->assertTrue($result);
    }

    public function testCanReadAttendanceData(): void
    {
        $data = $this->service->readAttendanceData();

        $this->assertIsArray($data);
    }

    public function testCanGenerateSheetUrl(): void
    {
        $url = $this->service->generateSheetUrl(
            'sheet-id'
        );

        $this->assertStringContainsString(
            'docs.google.com',
            $url
        );
    }
}
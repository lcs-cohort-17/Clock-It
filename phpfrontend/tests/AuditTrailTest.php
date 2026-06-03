<?php

use PHPUnit\Framework\TestCase;

class AuditTrailTest extends TestCase
{
    private string $content;

    protected function setUp(): void
    {
        $this->content = file_get_contents(
            __DIR__ . '/../phpfrontend/src/views/admin/attendance_log.php'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE
    |--------------------------------------------------------------------------
    */

    public function testClockEventsToggleExists(): void
    {
        $this->assertStringContainsString(
            'Clock Events',
            $this->content
        );
    }

    public function testAuditTrailToggleExists(): void
    {
        $this->assertStringContainsString(
            'Audit Trail',
            $this->content
        );
    }

    public function testBootstrapButtonGroupExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'btn-group')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AUDIT TRAIL TABLE
    |--------------------------------------------------------------------------
    */

    public function testAuditTrailTableExists(): void
    {
        $this->assertStringContainsString(
            'table',
            $this->content
        );
    }

    public function testTimestampColumnExists(): void
    {
        $this->assertStringContainsString(
            'Timestamp',
            $this->content
        );
    }

    public function testAdminNameColumnExists(): void
    {
        $this->assertStringContainsString(
            'Admin Name',
            $this->content
        );
    }

    public function testActionColumnExists(): void
    {
        $this->assertStringContainsString(
            'Action',
            $this->content
        );
    }

    public function testDetailsColumnExists(): void
    {
        $this->assertStringContainsString(
            'Details',
            $this->content
        );
    }

    public function testOldValueColumnExists(): void
    {
        $this->assertStringContainsString(
            'Old Value',
            $this->content
        );
    }

    public function testNewValueColumnExists(): void
    {
        $this->assertStringContainsString(
            'New Value',
            $this->content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BOOTSTRAP TABLE
    |--------------------------------------------------------------------------
    */

    public function testBootstrapTableExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'table')
            &&
            str_contains($this->content, 'table-striped')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT CSV
    |--------------------------------------------------------------------------
    */

    public function testExportCsvButtonExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'Export CSV')
        );
    }

    public function testBootstrapButtonUsedForExport(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'btn')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    */

    public function testAuditApiEndpointExists(): void
    {
        $this->assertStringContainsString(
            '/api/admin/attendance/audit',
            $this->content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SORTING
    |--------------------------------------------------------------------------
    */

    public function testUsesAlpineJs(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'x-data')
            ||
            str_contains($this->content, 'x-model')
            ||
            str_contains($this->content, '@click')
        );
    }

    public function testTimestampSortingExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'sort')
            ||
            str_contains($this->content, 'timestamp')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCEPTANCE CRITERIA
    |--------------------------------------------------------------------------
    */

    public function testToggleRequirementMet(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'Clock Events')
            &&
            str_contains($this->content, 'Audit Trail')
        );
    }

    public function testAuditTrailRequirementMet(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'Timestamp')
            &&
            str_contains($this->content, 'Admin Name')
            &&
            str_contains($this->content, 'Action')
        );
    }

    public function testExportRequirementMet(): void
    {
        $this->assertStringContainsString(
            'Export CSV',
            $this->content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEFINITION OF DONE
    |--------------------------------------------------------------------------
    */

    public function testDefinitionOfDoneCoverage(): void
    {
        $required = [
            'Clock Events',
            'Audit Trail',
            'Timestamp',
            'Admin Name',
            'Action',
            'Export CSV',
            '/api/admin/attendance/audit'
        ];

        foreach ($required as $item) {
            $this->assertStringContainsString(
                $item,
                $this->content
            );
        }
    }
}
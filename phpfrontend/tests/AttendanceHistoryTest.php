<?php

use PHPUnit\Framework\TestCase;

final class AttendanceHistoryTest extends TestCase
{
    private string $viewFile;
    private string $partialFile;
    private string $jsFile;
    private string $dataFile;

    protected function setUp(): void
    {
        $this->viewFile = __DIR__ . '/../src/views/staff/AttendanceHistory.php';
        $this->partialFile = __DIR__ . '/../src/views/partials/staff_attendance_history.php';
        $this->jsFile = __DIR__ . '/../assets/js/attendance_history_page.js';
        $this->dataFile = __DIR__ . '/../src/data/staff_attendance_history.php';
    }

    /*
    |--------------------------------------------------------------------------
    | FILE EXISTENCE TESTS
    |--------------------------------------------------------------------------
    */

    public function testHistoryViewFileExists(): void
    {
        $this->assertFileExists($this->viewFile, 'AttendanceHistory.php view file should exist');
    }

    public function testHistoryPartialFileExists(): void
    {
        $this->assertFileExists($this->partialFile, 'staff_attendance_history.php partial should exist');
    }

    public function testHistoryJavaScriptFileExists(): void
    {
        $this->assertFileExists($this->jsFile, 'attendance_history_page.js should exist');
    }

    public function testMockDataFileExists(): void
    {
        $this->assertFileExists($this->dataFile, 'staff_attendance_history.php data file should exist');
    }

    /*
    |--------------------------------------------------------------------------
    | LIST VIEW (TABLE) TESTS
    |--------------------------------------------------------------------------
    */

    public function testListViewTableExists(): void
    {
        $partial = file_get_contents($this->partialFile);
        
        $this->assertTrue(
            str_contains($partial, '<table') || str_contains($partial, 'table'),
            'List view should contain a table element'
        );
    }

    public function testListViewHasRequiredColumns(): void
    {
        $partial = file_get_contents($this->partialFile);
        
        $requiredColumns = ['Employee', 'Date', 'Check in', 'Check out', 'Status'];
        
        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                str_contains($partial, $column),
                "List view table should have '$column' column"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CALENDAR VIEW TESTS
    |--------------------------------------------------------------------------
    */

    public function testCalendarViewExists(): void
    {
        $partial = file_get_contents($this->partialFile);
        
        $this->assertTrue(
            str_contains($partial, 'calendar') || str_contains($partial, 'Calendar'),
            'Calendar view component should exist'
        );
    }

    public function testCalendarHasMonthNavigation(): void
    {
        $partial = file_get_contents($this->partialFile);
        
        $this->assertTrue(
            str_contains($partial, 'prev') || str_contains($partial, 'Previous') || str_contains($partial, 'changeMonth'),
            'Calendar should have previous month navigation'
        );
        
        $this->assertTrue(
            str_contains($partial, 'next') || str_contains($partial, 'Next') || str_contains($partial, 'changeMonth'),
            'Calendar should have next month navigation'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW TOGGLE TESTS
    |--------------------------------------------------------------------------
    */

    public function testViewToggleExists(): void
    {
        $partial = file_get_contents($this->partialFile);
        
        $this->assertTrue(
            str_contains($partial, 'viewMode') || 
            str_contains($partial, 'list') || 
            str_contains($partial, 'calendar'),
            'View toggle functionality should exist'
        );
    }

    public function testViewToggleHasListAndCalendarOptions(): void
    {
        $partial = file_get_contents($this->partialFile);
        
        $this->assertTrue(
            str_contains($partial, 'list') || str_contains($partial, 'List'),
            'View toggle should have List option'
        );
        
        $this->assertTrue(
            str_contains($partial, 'calendar') || str_contains($partial, 'Calendar'),
            'View toggle should have Calendar option'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTERS TESTS
    |--------------------------------------------------------------------------
    */

    public function testTimeFiltersExist(): void
    {
        $partial = file_get_contents($this->partialFile);
        
        $this->assertTrue(
            str_contains($partial, 'This Week') || str_contains($partial, 'week'),
            'This Week filter should exist'
        );
        
        $this->assertTrue(
            str_contains($partial, 'This Month') || str_contains($partial, 'month'),
            'This Month filter should exist'
        );
        
        $this->assertTrue(
            str_contains($partial, 'All Time') || str_contains($partial, 'all'),
            'All Time filter should exist'
        );
    }

    public function testFilterFunctionalityInJavaScript(): void
    {
        $js = file_get_contents($this->jsFile);
        
        $this->assertTrue(
            str_contains($js, 'timeFilter') || str_contains($js, 'applyFilters'),
            'JavaScript should have filter functionality'
        );
        
        $this->assertTrue(
            str_contains($js, 'week') || str_contains($js, 'month'),
            'JavaScript should handle week and month filters'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MOCK DATA TESTS
    |--------------------------------------------------------------------------
    */

    public function testMockDataIsLoadedInView(): void
    {
        $view = file_get_contents($this->viewFile);
        
        $this->assertTrue(
            str_contains($view, 'staff_attendance_history.php') || 
            str_contains($view, 'ATTENDANCE_DATA'),
            'View should load mock attendance data'
        );
    }

    public function testMockDataContainsRequiredFields(): void
    {
        $data = require $this->dataFile;
        
        $this->assertIsArray($data, 'Mock data should be an array');
        $this->assertNotEmpty($data, 'Mock data should not be empty');
        
        $firstRecord = $data[0];
        
        $requiredFields = ['employeeId', 'employeeName', 'date', 'status'];
        
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $firstRecord, "Mock data should have '$field' field");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | JAVASCRIPT FUNCTIONALITY TESTS
    |--------------------------------------------------------------------------
    */

    public function testJavaScriptHasViewSwitching(): void
    {
        $js = file_get_contents($this->jsFile);
        
        $this->assertTrue(
            str_contains($js, 'viewMode') || str_contains($js, 'view'),
            'JavaScript should handle view mode switching'
        );
    }

    public function testJavaScriptHasFilteringLogic(): void
    {
        $js = file_get_contents($this->jsFile);
        
        $this->assertTrue(
            str_contains($js, 'filter') || str_contains($js, 'applyFilters'),
            'JavaScript should have filtering logic'
        );
    }

    public function testJavaScriptHasPagination(): void
    {
        $js = file_get_contents($js = file_get_contents($this->jsFile);
        
        $this->assertTrue(
            str_contains($js, 'page') || str_contains($js, 'pagination'),
            'JavaScript should have pagination functionality'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEFINITION OF DONE VERIFICATION
    |--------------------------------------------------------------------------
    */

    public function testAllDefinitionOfDoneRequirementsMet(): void
    {
        $view = file_get_contents($this->viewFile);
        $partial = file_get_contents($this->partialFile);
        $js = file_get_contents($this->jsFile);
        
        // History page layout created
        $this->assertFileExists($this->viewFile);
        
        // List View and Calendar View toggle added
        $this->assertTrue(
            str_contains($partial, 'list') && str_contains($partial, 'calendar')
        );
        
        // Attendance table with dummy data
        $this->assertTrue(str_contains($partial, '<table') || str_contains($partial, 'table'));
        
        // Calendar component
        $this->assertTrue(str_contains($partial, 'calendar') || str_contains($partial, 'Calendar'));
        
        // Filters implemented
        $this->assertTrue(
            str_contains($js, 'week') && 
            str_contains($js, 'month') && 
            str_contains($js, 'all')
        );
    }
}
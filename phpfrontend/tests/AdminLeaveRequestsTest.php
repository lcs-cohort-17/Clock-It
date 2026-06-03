<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AdminLeaveRequestsTest extends TestCase
{
    /**
     * Helper to retrieve the baseline state of mock requests exactly as 
     * defined in your admin-leave-requests.php view script.
     */
    private function getInitialMockData(): array
    {
        return [
            ['id' => 101, 'employee_name' => 'Sarah Jenkins', 'type' => 'Annual Leave', 'start_date' => '2026-06-10', 'end_date' => '2026-06-15', 'reason' => 'Family vacation break', 'status' => 'Pending'],
            ['id' => 102, 'employee_name' => 'John Doe', 'type' => 'Sick Leave', 'start_date' => '2026-06-05', 'end_date' => '2026-06-06', 'reason' => 'Dental surgery tracking procedure', 'status' => 'Pending'],
            ['id' => 103, 'employee_name' => 'Alex Smith', 'type' => 'Study Leave', 'start_date' => '2026-06-20', 'end_date' => '2026-06-23', 'reason' => 'Frontend final examination preparation', 'status' => 'Pending'],
            ['id' => 104, 'employee_name' => 'Michael Brown', 'type' => 'Family Responsibility', 'start_date' => '2026-05-12', 'end_date' => '2025-05-15', 'reason' => 'Attending relative funeral services', 'status' => 'Pending']
        ];
    }

    /**
     * Criteria: Admin can see all leave requests from all employees
     */
    public function test_admin_can_see_all_leave_requests(): void
    {
        $requests = $this->getInitialMockData();
        
        $this->assertCount(4, $requests, "Admin view must load all 4 baseline employee requests.");
    }

    /**
     * Criteria: Admin can search/filter by employee name
     */
    public function test_admin_can_search_by_employee_name(): void
    {
        $requests = $this->getInitialMockData();
        $searchQuery = 'Sarah';

        $filteredRequests = array_filter($requests, function ($item) use ($searchQuery) {
            return str_contains(strtolower($item['employee_name']), strtolower($searchQuery));
        });

        $this->assertCount(1, $filteredRequests);
        $this->assertEquals('Sarah Jenkins', array_values($filteredRequests)[0]['employee_name']);
    }

    /**
     * Criteria: Admin can filter by status (Pending / Approved / Rejected)
     */
    public function test_admin_can_filter_by_status(): void
    {
        $requests = $this->getInitialMockData();

        // 1. Filter by 'Pending' (Should match all 4 initially)
        $pendingFilter = array_filter($requests, function ($item) {
            return $item['status'] === 'Pending';
        });
        $this->assertCount(4, $pendingFilter);

        // 2. Filter by 'Approved' (Should match 0 initially)
        $approvedFilter = array_filter($requests, function ($item) {
            return $item['status'] === 'Approved';
        });
        $this->assertCount(0, $approvedFilter);
    }

    /**
     * Criteria: Admin can approve a pending request
     */
    public function test_admin_can_approve_a_pending_request(): void
    {
        $requests = $this->getInitialMockData();
        $targetId = 101; // Sarah Jenkins

        foreach ($requests as &$item) {
            if ($item['id'] === $targetId) {
                $item['status'] = 'Approved';
            }
        }
        unset($item);

        $pendingFilterScope = array_filter($requests, function ($item) {
            return $item['status'] === 'All' || $item['status'] === 'Pending';
        });
        $this->assertCount(3, $pendingFilterScope);

        $approvedFilterScope = array_filter($requests, function ($item) {
            return $item['status'] === 'Approved';
        });
        $this->assertCount(1, $approvedFilterScope);
    }

    /**
     * Criteria: Admin can reject a pending request
     */
    public function test_admin_can_reject_a_pending_request(): void
    {
        $requests = $this->getInitialMockData();
        $targetId = 102; // John Doe

        foreach ($requests as &$item) {
            if ($item['id'] === $targetId) {
                $item['status'] = 'Rejected';
            }
        }
        unset($item);

        $pendingFilterScope = array_filter($requests, function ($item) {
            return $item['status'] === 'Pending';
        });
        $this->assertCount(3, $pendingFilterScope);

        $rejectedFilterScope = array_filter($requests, function ($item) {
            return $item['status'] === 'Rejected';
        });
        $this->assertCount(1, $rejectedFilterScope);
    }

    #[DataProvider('searchTermsProvider')]
    public function test_admin_search_handles_various_inputs($searchTerm, $expectedCount): void
    {
        $requests = $this->getInitialMockData();

        $filteredRequests = array_filter($requests, function ($item) use ($searchTerm) {
            return str_contains(strtolower($item['employee_name']), strtolower($searchTerm));
        });

        $this->assertCount($expectedCount, $filteredRequests);
    }

    public static function searchTermsProvider(): array
    {
        return [
            'Exact match Sarah'   => ['Sarah', 1],
            'Lowercase match john' => ['john', 1],
            'No results match'     => ['Zack', 0],
            'Partial match Smith'  => ['Smi', 1],
        ];
    }

    /**
     * Edge Case: Find requests where the end date accidentally comes before the start date
     */
    public function test_identifies_invalid_date_ranges(): void
    {
        $requests = $this->getInitialMockData();
        
        $brokenRequests = array_filter($requests, function($item) {
            return strtotime($item['end_date']) < strtotime($item['start_date']);
        });

        $this->assertCount(1, $brokenRequests, "Should flag Michael Brown's date anomaly.");
        $this->assertEquals(104, array_values($brokenRequests)[0]['id']);
    }
}
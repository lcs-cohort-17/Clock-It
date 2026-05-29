<?php
 
use PHPUnit\Framework\TestCase;
 
require_once __DIR__ . '/../../src/types/LeaveInterface.php';
require_once __DIR__ . '/../../src/utils/LeaveValidator.php';
 
class LeaveValidatorTest extends TestCase {
 
    private function futureDate(int $daysAhead): string {
        return date('Y-m-d', strtotime("+{$daysAhead} days"));
    }

    private function futureDateTime(int $daysAhead, string $time): string {
        return date('Y-m-d', strtotime("+{$daysAhead} days")) . ' ' . $time;
    }
 
    // checks if valid submissions pass through
    public function test_validate_submit_passes(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'annual',
            'start_date'   => $this->futureDate(30),
            'end_date'     => $this->futureDate(35),
            'reason'       => 'Family holiday',
        ]);
        $this->assertEmpty($errors);
    }
 
    // sick leave is a valid request_type — previously only annual was tested
    public function test_validate_submit_passes_for_sick_leave(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'sick',
            'start_date'   => $this->futureDate(5),
            'end_date'     => $this->futureDate(7),
            'reason'       => 'Doctor appointment',
        ]);
        $this->assertEmpty($errors);
    }
 
    // checks invalid submissions
    public function test_invalid_submit_fails(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'holiday',
            'start_date'   => $this->futureDate(30),
            'end_date'     => $this->futureDate(35),
            'reason'       => 'Vacation',
        ]);
        $this->assertArrayHasKey('request_type', $errors);
        $this->assertContains('request_type must be leave, sick, annual, unpaid or other', $errors['request_type']);
    }
 
    // missing request_type must be caught on its own
    public function test_missing_request_type_fails(): void {
        $errors = LeaveValidator::validateSubmit([
            'start_date' => $this->futureDate(30),
            'end_date'   => $this->futureDate(35),
            'reason'     => 'Vacation',
        ]);
        $this->assertArrayHasKey('request_type', $errors);
    }
 
    // missing start_date must be caught on its own
    public function test_missing_start_date_fails(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'annual',
            'end_date'     => $this->futureDate(35),
            'reason'       => 'Vacation',
        ]);
        $this->assertArrayHasKey('start_date', $errors);
    }
 
    // missing end_date must be caught on its own
    public function test_missing_end_date_fails(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'annual',
            'start_date'   => $this->futureDate(30),
            'reason'       => 'Vacation',
        ]);
        $this->assertArrayHasKey('end_date', $errors);
    }
 
    // missing reason must be caught on its own
    public function test_missing_reason_fails(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'annual',
            'start_date'   => $this->futureDate(30),
            'end_date'     => $this->futureDate(35),
        ]);
        $this->assertArrayHasKey('reason', $errors);
    }
 
    // reject empty reason
    public function test_empty_reason_fails(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'annual',
            'start_date'   => $this->futureDate(30),
            'end_date'     => $this->futureDate(35),
            'reason'       => '',
        ]);
        $this->assertArrayHasKey('reason', $errors);
        $this->assertContains('Must contain reason', $errors['reason']);
    }
 
    // incorrect date structure
    public function test_date_format(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'sick',
            'start_date'   => $this->futureDate(35),
            'end_date'     => $this->futureDate(30),
            'reason'       => 'flu',
        ]);
        $this->assertArrayHasKey('end_date', $errors);
        $this->assertContains('End date must be on or after start_date', $errors['end_date']);
    }
 
    // staff cannot back-date a new leave request
    public function test_past_start_date_fails(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'annual',
            'start_date'   => '2020-01-01',
            'end_date'     => '2020-01-05',
            'reason'       => 'Old vacation',
        ]);
        $this->assertArrayHasKey('start_date', $errors);
    }
 
    // approve status
    public function test_valid_update_passes(): void {
        $errors = LeaveValidator::validateStatusUpdate(['status' => 'approved']);
        $this->assertEmpty($errors);
    }
 
    // invalid status checker
    public function test_invalid_status_passes(): void {
        $errors = LeaveValidator::validateStatusUpdate(['status' => 'accepted']);
        $this->assertArrayHasKey('status', $errors);
        $this->assertContains('Invalid status it must be approved,rejected or pending', $errors['status']);
    }
 
    // calendar query
    public function test_valid_calendar_query_passes(): void {
        $errors = LeaveValidator::validateCalendarQuery(['month' => '5', 'year' => '2026']);
        $this->assertEmpty($errors);
    }
 
    // rejects invalid month values
    public function test_invalid_month_fails(): void {
        $errors = LeaveValidator::validateCalendarQuery(['month' => '13']);
        $this->assertArrayHasKey('month', $errors);
        $this->assertContains('month must be between 1 and 12', $errors['month']);
    }
 
    // rejects invalid year values
    public function test_invalid_year_fails(): void {
        $errors = LeaveValidator::validateCalendarQuery(['month' => '6', 'year' => '0']);
        $this->assertArrayHasKey('year', $errors);
    }

    // datetime payloads should also validate when the ticket uses date/time fields
    public function test_validate_submit_passes_for_datetime_fields(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'sick',
            'start_date'   => $this->futureDateTime(1, '09:00'),
            'end_date'     => $this->futureDateTime(1, '17:00'),
            'reason'       => 'Doctor appointment',
        ]);
        $this->assertEmpty($errors);
    }

    // future end date/time must be rejected if it is not in the future
    public function test_past_end_date_fails(): void {
        $errors = LeaveValidator::validateSubmit([
            'request_type' => 'annual',
            'start_date'   => $this->futureDate(30),
            'end_date'     => '2020-01-01',
            'reason'       => 'Old vacation',
        ]);
        $this->assertArrayHasKey('end_date', $errors);
        $this->assertContains('End date must be in the future', $errors['end_date']);
    }

    // admin tests past dates
    // allows admin to change dates of already approved sick/leaves to adjust for extensions and so on
    public function test_update_leave_date(): void {
        $errors = LeaveValidator::validateUpdateLeave([
            'start_date' => '2024-01-01',
            'end_date'   => '2024-01-05',
        ]);
        $this->assertEmpty($errors);
    }
 
    // admin update must still reject end_date before start_date
    public function test_update_leave_end_before_start_fails(): void {
        $errors = LeaveValidator::validateUpdateLeave([
            'start_date' => '2024-01-10',
            'end_date'   => '2024-01-05',
        ]);
        $this->assertArrayHasKey('end_date', $errors);
    }
}

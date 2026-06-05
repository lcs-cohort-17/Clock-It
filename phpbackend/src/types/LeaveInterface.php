<?php

class Types
{
    public const SICK = 'sick';
    public const ANNUAL = 'annual';
    public const UNPAID = 'unpaid';
    public const OTHER = 'other';

    // Mirrors: z.enum(['leave', 'sick', 'annual', 'unpaid', 'other'])
    // Keeping the allowed values in one place makes validation easier to maintain.
    public const ALL = [
        self::SICK,
        self::ANNUAL,
        self::UNPAID,
        self::OTHER,
    ];
}

class RequestStatus
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    // Mirrors: z.enum(['approved', 'rejected', 'pending'])
    // The controller and validator both rely on these canonical status values.
    public const ALL = [
        self::PENDING,
        self::APPROVED,
        self::REJECTED,
    ];
}

// Leave request
// This documents the array shape returned by the model for a single request.
class LeaveRequestType
{
    public string $id;
    public string $user_id;
    public string $type;
    public string $start_date;
    public string $end_date;
    public string $reason;
    public string $status;
    public string $created_at;
    public string $updated_at;
}
// export interface AttendanceRecord extends LeaveRequest
// Attendance rows extend leave requests with profile information for the list view.
class AttendanceRecord extends LeaveRequestType
{
    public string $first_name;
    public string $last_name;
    public string $email;
}

// interface Calendar
// Calendar data is grouped by day so the frontend can render one bucket per date.
class CalendarType
{
    public string $date;
    /** @var array<int, array<string, mixed>> */
    public array $requests;
}

// Model contract used by the controller tests and the database layer.
interface LeaveRequestModel
{
    public function findActiveUser(string $userId): ?array;

    public function insert(string $userId, array $payload): array;

    public function findById(string $leaveId): ?array;

    public function updateStatus(string $leaveId, string $status): ?array;

    public function fetchCalendar(
        string $userId,
        string $role,
        ?int $month = null,
        ?int $year = null
    ): array;

    public function getLeave(?string $userId = null, ?string $role = null): array;

    public function updateLeave(string $leaveId, array $payload): ?array;
}

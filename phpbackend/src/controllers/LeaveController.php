<?php

class LeaveController
{
    public function __construct(private LeaveRequestModel $model)
    {
    }

    // POST /api/leave
    //Insert the leave request
    // Return 201 when the request is created successfully
    public function submitLeave(array $auth, array $body): void
    {
        try {
            $userId = $auth['userId'] ?? null;
            if ($userId === null || $userId === '') {
                $this->respond(401, ['message' => 'Unauthorized']);
                return;
            }

            $errors = LeaveValidator::validateSubmit($body);
            if ($errors !== []) {
                $this->respond(400, [
                    'message' => 'Validation failed',
                    'errors' => $errors,
                ]);
                return;
            }

            $profile = $this->model->findActiveProfile((string) $userId);
            if ($profile === null || empty($profile['is_active'])) {
                // 403 because the user cannot submit if the account is missing or inactive
                $this->respond(403, ['message' => 'User not found or inactive']);
                return;
            }

            $leave = $this->model->insert((string) $profile['id'], $body);
            if ($leave === [] || $leave === null) {
                // 500 means the request was valid but persistence failed
                $this->respond(500, ['message' => 'Insert failed, no data returned']);
                return;
            }

            $this->respond(201, [
                'message' => 'Leave request submitted',
                'data' => $leave,
            ]);
        } catch (Throwable $exception) {
            $this->respond(500, [
                'message' => 'Internal Server Error',
                'error' => $exception->getMessage(),
            ]);
        }
    }

    // PATCH /api/leaves/:id/status
    // Admin-only action:
    // 1. Require auth
    // 2. Block non-admin users with 403
    // 3. Validate the requested status
    // 4. Confirm the leave request exists
    // 5. Update status and return 200
    public function updateLeaveStatus(array $auth, string $leaveId, array $body): void
    {
        try {
            $userId = $auth['userId'] ?? null;
            if ($userId === null || $userId === '') {
                $this->respond(401, ['message' => 'Unauthorized']);
                return;
            }

            if (($auth['role'] ?? null) !== 'admin') {
                $this->respond(403, ['message' => 'Forbidden']);
                return;
            }

            $errors = LeaveValidator::validateStatusUpdate($body);
            if ($errors !== []) {
                $this->respond(400, [
                    'message' => 'Validation failed',
                    'errors' => $errors,
                ]);
                return;
            }

            $leave = $this->model->findById($leaveId);
            if ($leave === null) {
                $this->respond(404, ['message' => 'Leave request not found']);
                return;
            }

            $updated = $this->model->updateStatus($leaveId, (string) $body['status']);
            if ($updated === [] || $updated === null) {
                // 500 means the update operation ran but the database layer did not return a row
                $this->respond(500, ['message' => 'Update failed, no data returned']);
                return;
            }

            $this->respond(200, [
                'message' => 'Leave status updated',
                'data' => $updated,
            ]);
        } catch (Throwable $exception) {
            $this->respond(500, [
                'message' => 'Internal Server Error',
                'error' => $exception->getMessage(),
            ]);
        }
    }

    // GET /api/leaves/getCalendar
    // The controller returns records grouped by date so the frontend can render a calendar view easily.
    public function getCalendar(array $auth, array $query): void
    {
        try {
            $userId = $auth['userId'] ?? null;
            if ($userId === null || $userId === '') {
                $this->respond(401, ['message' => 'Unauthorized']);
                return;
            }

            $errors = LeaveValidator::validateCalendarQuery($query);
            if ($errors !== []) {
                $this->respond(400, [
                    'message' => 'Validation failed',
                    'errors' => $errors,
                ]);
                return;
            }

            $profileId = $this->resolveProfileIdForAuth($auth);
            if ($profileId === null) {
                $this->respond(403, ['message' => 'User not found or inactive']);
                return;
            }

            $calendar = $this->model->fetchCalendar(
                $profileId,
                (string) ($auth['role'] ?? 'staff'),
                array_key_exists('month', $query) ? (int) $query['month'] : null,
                array_key_exists('year', $query) ? (int) $query['year'] : null
            );

            if ($calendar === null) {
                // 500 means the query itself was acceptable, but fetching data failed
                $this->respond(500, ['message' => 'Failed to fetch calendar data']);
                return;
            }

            $this->respond(200, $this->groupCalendarByDate($calendar));
        } catch (Throwable $exception) {
            $this->respond(500, [
                'message' => 'Internal Server Error',
                'error' => $exception->getMessage(),
            ]);
        }
    }

    // GET /api/leaves/getLeave
    // Used by the attendance page as a list-style data source.
    public function getLeave(array $auth, array $query = []): void
    {
        try {
            $userId = $auth['userId'] ?? null;
            if ($userId === null || $userId === '') {
                $this->respond(401, ['message' => 'Unauthorized']);
                return;
            }

            $profileId = $this->resolveProfileIdForAuth($auth);
            if ($profileId === null) {
                $this->respond(403, ['message' => 'User not found or inactive']);
                return;
            }

            $leave = $this->model->getLeave(
                $profileId,
                (string) ($auth['role'] ?? 'staff')
            );

            if ($leave === null) {
                // 500 indicates a backend/database issue while building the attendance list
                $this->respond(500, ['message' => 'Failed to fetch attendance records']);
                return;
            }

            $this->respond(200, $leave);
        } catch (Throwable $exception) {
            $this->respond(500, [
                'message' => 'Internal Server Error',
                'error' => $exception->getMessage(),
            ]);
        }
    }

    // PATCH /api/leaves/:id/updateRequest
    // Admin-only update for editing the leave request itself, not only the status.
    public function updateLeave(array $auth, string $leaveId, array $body): void
    {
        try {
            $userId = $auth['userId'] ?? null;
            if ($userId === null || $userId === '') {
                $this->respond(401, ['message' => 'Unauthorized']);
                return;
            }

            if (($auth['role'] ?? null) !== 'admin') {
                // Keep this endpoint aligned with the admin-only edit workflow.
                $this->respond(403, ['message' => 'Forbidden']);
                return;
            }

            $errors = LeaveValidator::validateUpdateLeave($body);
            if ($errors !== []) {
                $this->respond(400, [
                    'message' => 'Validation failed',
                    'errors' => $errors,
                ]);
                return;
            }

            $existing = $this->model->findById($leaveId);
            if ($existing === null) {
                // 404 is appropriate here because the request id is valid but does not exist
                $this->respond(404, ['message' => 'Leave request not found']);
                return;
            }

            $updated = $this->model->updateLeave($leaveId, $body);
            if ($updated === null) {
                // 500 means the update operation itself failed or returned nothing useful
                $this->respond(500, ['message' => 'Update failed, no data returned']);
                return;
            }

            $this->respond(200, [
                'message' => 'Leave request updated',
                'data' => $updated,
            ]);
        } catch (Throwable $exception) {
            $this->respond(500, [
                'message' => 'Internal Server Error',
                'error' => $exception->getMessage(),
            ]);
        }
    }

    // Small helper to keep every response consistent.
    private function respond(int $status, array $payload): void
    {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    // Convert flat rows into a calendar-friendly structure grouped by YYYY-MM-DD.
    private function groupCalendarByDate(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $key = $this->extractCalendarDate($row);
            $grouped[$key][] = $row;
        }

        ksort($grouped);

        return $grouped;
    }

    private function extractCalendarDate(array $row): string
    {
        $value = $row['start_date'] ?? null;
        if (!is_string($value) || $value === '') {
            return 'unknown';
        }

        return substr($value, 0, 10);
    }

    private function resolveProfileIdForAuth(array $auth): ?string
    {
        $userId = $auth['userId'] ?? null;
        if ($userId === null || $userId === '') {
            return null;
        }

        if (($auth['role'] ?? null) === 'admin') {
            return (string) $userId;
        }

        $profile = $this->model->findActiveProfile((string) $userId);
        if ($profile === null || empty($profile['is_active'])) {
            return null;
        }

        return (string) $profile['id'];
    }
}

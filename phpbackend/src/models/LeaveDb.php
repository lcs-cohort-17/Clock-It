<?php

class LeaveDb
{
    public function __construct(private ?PDO $pdo = null)
    {
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        if ($this->pdo === null) {
            throw new RuntimeException('No PDO connection has been configured for LeaveDb.');
        }

        return $this->pdo->prepare($query, $options);
    }
}

class LeaveDbModel implements LeaveRequestModel
{
    public function __construct(private LeaveDb $connection)
    {
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function findActiveProfile(string $userId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, is_active FROM profiles WHERE id = :id AND is_active = 1 LIMIT 1'
        );
        $statement->execute(['id' => $userId]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function insert(string $profileId, array $payload): array
    {
        $leaveId = $this->generateUuid();
        $statement = $this->connection->prepare(
            'INSERT INTO leave_requests (
                id,
                profile_id,
                request_type,
                start_date,
                end_date,
                reason,
                status
            ) VALUES (
                :id,
                :profile_id,
                :request_type,
                :start_date,
                :end_date,
                :reason,
                :status
            )'
        );
        $statement->execute([
            'id' => $leaveId,
            'profile_id' => $profileId,
            'request_type' => $payload['request_type'] ?? null,
            'start_date' => $payload['start_date'] ?? null,
            'end_date' => $payload['end_date'] ?? null,
            'reason' => $payload['reason'] ?? null,
            'status' => RequestStatus::PENDING,
        ]);

        return $this->findById($leaveId) ?? [];
    }

    public function findById(string $leaveId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM leave_requests WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $leaveId]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function updateStatus(string $leaveId, string $status): ?array
    {
        $statement = $this->connection->prepare(
            'UPDATE leave_requests SET status = :status WHERE id = :id'
        );
        $statement->execute([
            'id' => $leaveId,
            'status' => $status,
        ]);

        return $this->findById($leaveId);
    }

    public function fetchCalendar(
        string $userId,
        string $role,
        ?int $month = null,
        ?int $year = null
    ): array {
        if ($role === 'admin') {
            $statement = $this->connection->prepare(
                'SELECT * FROM leave_requests'
            );
            $statement->execute();
        } else {
            $statement = $this->connection->prepare(
                'SELECT * FROM leave_requests WHERE profile_id = :profile_id'
            );
            $statement->execute([
                'profile_id' => $userId,
            ]);
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLeave(?string $userId = null, ?string $role = null): array
    {
        $baseQuery = '
            SELECT
                lr.id,
                lr.profile_id,
                p.first_name,
                p.last_name,
                p.email,
                lr.request_type,
                lr.start_date,
                lr.end_date,
                lr.reason,
                lr.status,
                lr.created_at,
                lr.updated_at
            FROM leave_requests lr
            INNER JOIN profiles p ON p.id = lr.profile_id
        ';

        if ($role === 'admin') {
            $statement = $this->connection->prepare($baseQuery . ' ORDER BY lr.created_at DESC');
            $statement->execute();
        } else {
            $statement = $this->connection->prepare($baseQuery . ' WHERE lr.profile_id = :profile_id ORDER BY lr.created_at DESC');
            $statement->execute([
                'profile_id' => $userId,
            ]);
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateLeave(string $leaveId, array $payload): ?array
    {
        $statement = $this->connection->prepare(
            'UPDATE leave_requests
             SET start_date = :start_date,
                 end_date = :end_date,
                 reason = :reason
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $leaveId,
            'start_date' => $payload['start_date'] ?? null,
            'end_date' => $payload['end_date'] ?? null,
            'reason' => $payload['reason'] ?? null,
        ]);

        return $this->findById($leaveId);
    }
}

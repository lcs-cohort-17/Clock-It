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

    public function findActiveUser(string $userId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT user_id, role, is_active FROM users WHERE user_id = :user_id AND is_active = 1 LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function insert(string $userId, array $payload): array
    {
        $leaveId = $this->generateUuid();
        $statement = $this->connection->prepare(
            'INSERT INTO leave_requests (
                id,
                user_id,
                type,
                start_date,
                end_date,
                reason,
                status
            ) VALUES (
                :id,
                :user_id,
                :type,
                :start_date,
                :end_date,
                :reason,
                :status
            )'
        );
        $statement->execute([
            'id' => $leaveId,
            'user_id' => $userId,
            'type' => $payload['type'] ?? null,
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
        $params = [];
        if ($role === 'admin') {
            $query = 'SELECT * FROM leave_requests';
        } else {
            $query = 'SELECT * FROM leave_requests WHERE user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if ($month !== null && $year !== null) {
            $query .= ($role === 'admin' ? ' WHERE' : ' AND') . ' MONTH(start_date) = :month AND YEAR(start_date) = :year';
            $params['month'] = $month;
            $params['year'] = $year;
        }

        $statement = $this->connection->prepare($query);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLeave(?string $userId = null, ?string $role = null): array
    {
        $baseQuery = '
            SELECT
                lr.id,
                lr.user_id,
                u.first_name,
                u.last_name,
                u.email,
                lr.type,
                lr.start_date,
                lr.end_date,
                lr.reason,
                lr.status,
                lr.created_at,
                lr.updated_at
            FROM leave_requests lr
            INNER JOIN users u ON u.user_id = lr.user_id
        ';

        if ($role === 'admin') {
            $statement = $this->connection->prepare($baseQuery . ' ORDER BY lr.created_at DESC');
            $statement->execute();
        } else {
            $statement = $this->connection->prepare($baseQuery . ' WHERE lr.user_id = :user_id ORDER BY lr.created_at DESC');
            $statement->execute([
                'user_id' => $userId,
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

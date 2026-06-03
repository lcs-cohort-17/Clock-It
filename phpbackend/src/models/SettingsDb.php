<?php

class SettingsDb
{
    public function __construct(private ?PDO $pdo = null) {}

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        if ($this->pdo === null) {
            throw new RuntimeException('No PDO connection has been configured for SettingsDb.');
        }

        return $this->pdo->prepare($query, $options);
    }
}

class SettingsDbModel implements SettingsModel
{
    public function __construct(private SettingsDb $connection) {}

    public function findActiveUser(string $userId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT user_id, role, is_active FROM users WHERE user_id = :user_id AND is_active = 1 LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function getAllSettings(): array
    {
        $statement = $this->connection->prepare(
            'SELECT `key`, `value` FROM settings'
        );
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $row) {
            $map[$row['key']] = $row['value'];
        }

        return $map;
    }

    public function getSetting(string $key): ?string
    {
        $statement = $this->connection->prepare(
            'SELECT `value` FROM settings WHERE `key` = :key LIMIT 1'
        );
        $statement->execute(['key' => $key]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row['value'];
    }

    public function upsertSetting(string $key, string $value): bool
    {
        $statement = $this->connection->prepare(
            'INSERT INTO settings (`key`, `value`)
     VALUES (:key, :value)
     ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );

        return $statement->execute(['key' => $key, 'value' => $value]);
    }

    public function purgeAttendance(int $days): int
    {
        $statement = $this->connection->prepare(
            'DELETE FROM attendance
     WHERE timestamp < DATE_SUB(NOW(), INTERVAL :days DAY)'
        );
        $statement->bindValue(':days', $days, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount();
    }
}

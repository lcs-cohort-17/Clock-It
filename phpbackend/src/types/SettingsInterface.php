<?php
 
interface SettingsModel
{
    public function findActiveUser(string $userId): ?array;
    public function getAllSettings(): array;
    public function getSetting(string $key): ?string;
    public function upsertSetting(string $key, string $value): bool;
    public function purgeAttendance(int $days): int;
}
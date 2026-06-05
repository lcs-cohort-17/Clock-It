<?php

declare(strict_types=1);

class LeaveValidator
{
    private const TYPES = Types::ALL;
    private const STATUSES = RequestStatus::ALL;

    public static function validateSubmit(array $payload): array
    {
        $errors = [];

        $type = strtolower(trim((string) ($payload['type'] ?? '')));
        if (!in_array($type, self::TYPES, true)) {
            self::addError($errors, 'type', 'Type must be sick, annual, unpaid, or other.');
        }

        self::validateDateRange($payload, $errors, false);

        $reason = trim((string) ($payload['reason'] ?? ''));
        if ($reason === '') {
            self::addError($errors, 'reason', 'Reason is required.');
        } elseif (mb_strlen($reason) > 1000) {
            self::addError($errors, 'reason', 'Reason may not be longer than 1000 characters.');
        }

        return $errors;
    }

    public static function validateStatusUpdate(array $payload): array
    {
        $errors = [];
        $status = strtolower(trim((string) ($payload['status'] ?? '')));

        if (!in_array($status, self::STATUSES, true)) {
            self::addError($errors, 'status', 'Status must be pending, approved, or rejected.');
        }

        return $errors;
    }

    public static function validateCalendarQuery(array $query): array
    {
        $errors = [];

        if (array_key_exists('month', $query) && $query['month'] !== '') {
            $month = filter_var($query['month'], FILTER_VALIDATE_INT);
            if ($month === false || $month < 1 || $month > 12) {
                self::addError($errors, 'month', 'Month must be between 1 and 12.');
            }
        }

        if (array_key_exists('year', $query) && $query['year'] !== '') {
            $year = filter_var($query['year'], FILTER_VALIDATE_INT);
            if ($year === false || $year < 1900) {
                self::addError($errors, 'year', 'Year must be a valid year.');
            }
        }

        return $errors;
    }

    public static function validateUpdateLeave(array $payload): array
    {
        $errors = [];
        self::validateDateRange($payload, $errors, true);

        $reason = trim((string) ($payload['reason'] ?? ''));
        if ($reason === '') {
            self::addError($errors, 'reason', 'Reason is required.');
        } elseif (mb_strlen($reason) > 1000) {
            self::addError($errors, 'reason', 'Reason may not be longer than 1000 characters.');
        }

        return $errors;
    }

    private static function validateDateRange(array $payload, array &$errors, bool $allowPastDates): void
    {
        $format = self::resolveDateFormat($payload);

        $startValue = $payload['start_date'] ?? null;
        $endValue   = $payload['end_date'] ?? null;

        $start = self::toDateTimeImmutable($startValue, $format);
        $end   = self::toDateTimeImmutable($endValue, $format);

        if ($start === null) {
            self::addError($errors, 'start_date', 'Start date must be a valid date.');
        }

        if ($end === null) {
            self::addError($errors, 'end_date', 'End date must be a valid date.');
        }

        if ($start === null || $end === null) {
            return;
        }

        if (!$allowPastDates) {
            $now = new DateTimeImmutable('now');

            if ($format === 'Y-m-d') {
                $today = new DateTimeImmutable($now->format('Y-m-d'));
                if ($start <= $today) {
                    self::addError($errors, 'start_date', 'Start date must be in the future.');
                }
                if ($end <= $today) {
                    self::addError($errors, 'end_date', 'End date must be in the future.');
                }
            } elseif ($start <= $now || $end <= $now) {
                if ($start <= $now) {
                    self::addError($errors, 'start_date', 'Start date/time must be in the future.');
                }
                if ($end <= $now) {
                    self::addError($errors, 'end_date', 'End date/time must be in the future.');
                }
            }
        }

        if ($end < $start) {
            self::addError($errors, 'end_date', 'End date must be the same as or after the start date.');
        }
    }

    private static function resolveDateFormat(array $payload): string
    {
        foreach (['start_date', 'end_date'] as $field) {
            $value = $payload[$field] ?? null;
            if (!is_string($value)) {
                continue;
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                return 'Y-m-d H:i:s';
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
                return 'Y-m-d H:i';
            }
        }

        return 'Y-m-d';
    }

    private static function toDateTimeImmutable(mixed $value, string $format): ?DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat($format, $value);
        if ($date === false) {
            return null;
        }

        return $date->format($format) === $value ? $date : null;
    }

    private static function addError(array &$errors, string $field, string $message): void
    {
        $errors[$field] ??= [];
        $errors[$field][] = $message;
    }
}
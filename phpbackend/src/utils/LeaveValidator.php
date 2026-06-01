<?php

class LeaveValidator
{
    private const TYPES = Types::ALL;
    private const STATUSES = RequestStatus::ALL;

    // Validate the request payload before the controller touches the database.
    // This keeps bad data from ever reaching the model layer.
    public static function validateSubmit(array $payload): array
    {
        $errors = [];

        $type = $payload['type'] ?? null;
        if (!in_array($type, self::TYPES, true)) {
            self::addError(
                $errors,
                'type',
                "type must be \r\n         sick, annual, unpaid or other"
            );
        }

        self::validateDateRange($payload, $errors, false);

        $reason = trim((string) ($payload['reason'] ?? ''));
        if ($reason === '') {
            self::addError($errors, 'reason', 'Must contain reason');
        }

        return $errors;
    }

    // Status updates are restricted to approved/rejected/pending.
    // The controller uses this before allowing an admin action to continue.
    public static function validateStatusUpdate(array $payload): array
    {
        $errors = [];
        $status = $payload['status'] ?? null;

        if (!in_array($status, self::STATUSES, true)) {
            self::addError(
                $errors,
                'status',
                'Invalid status it must be approved,rejected or pending'
            );
        }

        return $errors;
    }

    // Calendar query validation keeps month/year filters safe and predictable.
    public static function validateCalendarQuery(array $query): array
    {
        $errors = [];

        if (array_key_exists('month', $query)) {
            $month = filter_var($query['month'], FILTER_VALIDATE_INT);
            if ($month === false || $month < 1 || $month > 12) {
                self::addError($errors, 'month', 'month must be between 1 and 12');
            }
        }

        if (array_key_exists('year', $query)) {
            $year = filter_var($query['year'], FILTER_VALIDATE_INT);
            if ($year === false || $year < 1900) {
                self::addError($errors, 'year', 'year must be a valid year');
            }
        }

        return $errors;
    }

    // Admin updates may shift dates, so we validate the date range again here.
    public static function validateUpdateLeave(array $payload): array
    {
        $errors = [];
        self::validateDateRange($payload, $errors, true);

        return $errors;
    }

    // Shared date-range checker.
    // Accepts either date-only fields or datetime fields depending on payload shape.
    private static function validateDateRange(array $payload, array &$errors, bool $allowPastDates): void
    {
        [$startField, $endField, $format] = self::resolveDateFields($payload);

        $startValue = $payload[$startField] ?? null;
        $endValue = $payload[$endField] ?? null;

        if (!self::isValidDateValue($startValue, $format)) {
            self::addError($errors, $startField, sprintf('%s must be a valid %s value', $startField, $format));
        }

        if (!self::isValidDateValue($endValue, $format)) {
            self::addError($errors, $endField, sprintf('%s must be a valid %s value', $endField, $format));
        }

        if (!self::isValidDateValue($startValue, $format) || !self::isValidDateValue($endValue, $format)) {
            return;
        }

        $start = self::toDateTimeImmutable((string) $startValue, $format);
        $end = self::toDateTimeImmutable((string) $endValue, $format);

        if (!$allowPastDates) {
            $now = new DateTimeImmutable('now');
            // For date-only, compare against today at 00:00; reject today or past
            if ($format === 'Y-m-d') {
                $today = new DateTimeImmutable($now->format('Y-m-d'));
                if ($start <= $today) {
                    self::addError($errors, $startField, 'Start date must be in the future');
                }
                if ($end <= $today) {
                    self::addError($errors, $endField, 'End date must be in the future');
                }
            } else {
                // For datetime, reject now or past
                if ($start <= $now) {
                    self::addError($errors, $startField, 'Start date/time must be in the future');
                }
                if ($end <= $now) {
                    self::addError($errors, $endField, 'End date/time must be in the future');
                }
            }
        }

        // Allow same-day leaves: end_date >= start_date (only error if end < start)
        if ($end < $start) {
            self::addError($errors, $endField, 'End date must be after start_date');
        }
    }

    // If datetime keys are present, the validator switches to datetime mode.
    private static function resolveDateFields(array $payload): array
    {
        return ['start_date', 'end_date', self::resolveDateFormat($payload)];
    }

    private static function resolveDateFormat(array $payload): string
    {
        foreach (['start_date', 'end_date'] as $field) {
            $value = $payload[$field] ?? null;
            if (is_string($value) && str_contains($value, ':')) {
                return 'Y-m-d H:i';
            }
        }

        return 'Y-m-d';
    }

    // Returns true only when the value parses cleanly in the expected format.
    private static function isValidDateValue(mixed $value, string $format): bool
    {
        return self::toDateTimeImmutable($value, $format) !== null;
    }

    // Normalizes the date string into a DateTimeImmutable object for comparisons.
    private static function toDateTimeImmutable(mixed $value, string $format): ?DateTimeImmutable
    {
        if (!is_string($value)) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat($format, $value);

        if ($date === false) {
            return null;
        }

        return $date->format($format) === $value ? $date : null;
    }

    // Error buckets are keyed by field name so the frontend can display messages per input.
    private static function addError(array &$errors, string $field, string $message): void
    {
        if (!array_key_exists($field, $errors)) {
            $errors[$field] = [];
        }

        $errors[$field][] = $message;
    }
}

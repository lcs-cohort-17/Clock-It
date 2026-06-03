<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailAlertDb;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

class EmailAlertService
{
    public function __construct(private EmailAlertDb $alertsDb)
    {
    }

    public function runDailyAlerts(?DateTimeImmutable $now = null): array
    {
        $now = $now ?? new DateTimeImmutable('now', $this->timezone());
        $workDate = $now->format('Y-m-d');

        if (!$this->isWorkDay($now)) {
            $message = "No scheduled work day for {$workDate}. Daily attendance alerts skipped.";

            return [
                'success' => true,
                'skipped' => true,
                'message' => $message,
                'emailSent' => false,
                'alertStored' => false,
            ];
        }

        $lateAfter = $this->env('LATE_AFTER_TIME', '09:00');
        $lateArrivals = $this->alertsDb->fetchLateArrivals($workDate, $lateAfter);
        $noShows = $this->alertsDb->fetchNoShows($workDate);
        $body = $this->buildEmailBody($workDate, $lateArrivals, $noShows);
        $subject = "Clock It daily attendance alerts - {$workDate}";
        $createdAt = $now->format('Y-m-d H:i:s');

        $summaryStored = $this->alertsDb->storeDailyAlert($subject, $body, [
            'work_date' => $workDate,
            'late_after' => $lateAfter,
            'late_arrivals' => $lateArrivals,
            'no_shows' => $noShows,
        ], $createdAt);
        $alertRowsStored = $this->alertsDb->storeAttendanceAlerts($workDate, $lateArrivals, $noShows, $createdAt);

        $emailSent = $this->sendEmail($subject, $body);

        return [
            'success' => true,
            'skipped' => false,
            'workDate' => $workDate,
            'lateArrivalsCount' => count($lateArrivals),
            'noShowsCount' => count($noShows),
            'emailSent' => $emailSent,
            'alertStored' => $summaryStored || $alertRowsStored > 0,
            'summaryStored' => $summaryStored,
            'alertRowsStored' => $alertRowsStored,
            'message' => $body,
        ];
    }

    public function buildEmailBody(string $workDate, array $lateArrivals, array $noShows): string
    {
        $lines = ["Daily attendance summary for {$workDate}", ''];

        if ($lateArrivals === [] && $noShows === []) {
            $lines[] = 'No late arrivals or no-shows today.';
            return implode(PHP_EOL, $lines);
        }

        $lines[] = 'Late arrivals:';
        if ($lateArrivals === []) {
            $lines[] = '- None';
        } else {
            foreach ($lateArrivals as $staff) {
                $lines[] = '- ' . $this->formatStaffLine($staff, 'clock_in_time');
            }
        }

        $lines[] = '';
        $lines[] = 'No-shows:';
        if ($noShows === []) {
            $lines[] = '- None';
        } else {
            foreach ($noShows as $staff) {
                $lines[] = '- ' . $this->formatStaffLine($staff);
            }
        }

        return implode(PHP_EOL, $lines);
    }

    private function sendEmail(string $subject, string $body): bool
    {
        if (strtolower($this->env('SEND_ALERT_EMAILS', 'false')) !== 'true') {
            return false;
        }

        $to = $this->env('ADMIN_ALERT_EMAIL');
        if ($to === '') {
            throw new RuntimeException('ADMIN_ALERT_EMAIL is required when SEND_ALERT_EMAILS=true');
        }

        $from = $this->env('ALERT_EMAIL_FROM', 'no-reply@clockit.local');
        $headers = "From: {$from}\r\nContent-Type: text/plain; charset=UTF-8";

        return @mail($to, $subject, $body, $headers);
    }

    private function isWorkDay(DateTimeImmutable $date): bool
    {
        $configuredDays = array_map('trim', explode(',', $this->env('WORK_DAYS', '1,2,3,4,5')));
        return in_array($date->format('N'), $configuredDays, true);
    }

    private function timezone(): DateTimeZone
    {
        return new DateTimeZone($this->env('APP_TIMEZONE', 'Africa/Johannesburg'));
    }

    private function env(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    private function formatStaffLine(array $staff, ?string $timeKey = null): string
    {
        $name = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));
        $employeeId = $staff['employee_id'] ?? 'unknown';
        $label = $name !== '' ? "{$name} ({$employeeId})" : $employeeId;

        if ($timeKey !== null && !empty($staff[$timeKey])) {
            return "{$label} at {$staff[$timeKey]}";
        }

        return $label;
    }
}

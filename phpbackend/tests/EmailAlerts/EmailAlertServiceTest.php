<?php

declare(strict_types=1);

namespace Tests\EmailAlerts;

use App\Models\EmailAlertDb;
use App\Services\EmailAlertService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EmailAlertServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_TIMEZONE'] = 'Africa/Johannesburg';
        $_ENV['WORK_DAYS'] = '1,2,3,4,5';
        $_ENV['LATE_AFTER_TIME'] = '09:00';
        $_ENV['SEND_ALERT_EMAILS'] = 'false';
    }

    public function test_build_email_body_lists_late_arrivals_and_no_shows(): void
    {
        $service = new EmailAlertService($this->createMock(EmailAlertDb::class));

        $body = $service->buildEmailBody(
            '2026-06-01',
            [[
                'first_name' => 'Sarah',
                'last_name' => 'Mthembu',
                'employee_id' => 'S-101',
                'clock_in_time' => '2026-06-01 09:17:00',
            ]],
            [[
                'first_name' => 'John',
                'last_name' => 'Doe',
                'employee_id' => 'S-102',
            ]]
        );

        $this->assertStringContainsString('Late arrivals:', $body);
        $this->assertStringContainsString('Sarah Mthembu (S-101)', $body);
        $this->assertStringContainsString('No-shows:', $body);
        $this->assertStringContainsString('John Doe (S-102)', $body);
    }

    public function test_build_email_body_reports_no_issues(): void
    {
        $service = new EmailAlertService($this->createMock(EmailAlertDb::class));

        $body = $service->buildEmailBody('2026-06-01', [], []);

        $this->assertStringContainsString('No late arrivals or no-shows today.', $body);
    }

    public function test_run_daily_alerts_skips_non_work_days(): void
    {
        $_ENV['WORK_DAYS'] = '1,2,3,4,5';

        $db = $this->createMock(EmailAlertDb::class);
        $db->expects($this->never())->method('fetchLateArrivals');
        $db->expects($this->never())->method('fetchNoShows');
        $db->expects($this->never())->method('storeDailyAlert');

        $service = new EmailAlertService($db);
        $result = $service->runDailyAlerts(new DateTimeImmutable('2026-06-06 10:00:00'));

        $this->assertTrue($result['success']);
        $this->assertTrue($result['skipped']);
        $this->assertFalse($result['emailSent']);
        $this->assertFalse($result['alertStored']);
    }

    public function test_run_daily_alerts_fetches_data_and_stores_alert(): void
    {
        $db = $this->createMock(EmailAlertDb::class);
        $db->expects($this->once())
            ->method('fetchLateArrivals')
            ->with('2026-06-01', '09:00')
            ->willReturn([['first_name' => 'Sarah', 'last_name' => 'Mthembu', 'employee_id' => 'S-101']]);

        $db->expects($this->once())
            ->method('fetchNoShows')
            ->with('2026-06-01')
            ->willReturn([]);

        $db->expects($this->once())
            ->method('storeDailyAlert')
            ->willReturn(true);
        $db->expects($this->once())
            ->method('storeAttendanceAlerts')
            ->with(
                '2026-06-01',
                [['first_name' => 'Sarah', 'last_name' => 'Mthembu', 'employee_id' => 'S-101']],
                [],
                '2026-06-01 10:00:00'
            )
            ->willReturn(1);

        $service = new EmailAlertService($db);
        $result = $service->runDailyAlerts(new DateTimeImmutable('2026-06-01 10:00:00'));

        $this->assertTrue($result['success']);
        $this->assertFalse($result['skipped']);
        $this->assertSame(1, $result['lateArrivalsCount']);
        $this->assertSame(0, $result['noShowsCount']);
        $this->assertTrue($result['alertStored']);
        $this->assertSame(1, $result['alertRowsStored']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\EmailAlerts;

use App\Models\EmailAlertDb;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class EmailAlertDbTest extends TestCase
{
    public function test_fetch_late_arrivals_uses_work_date_and_cutoff(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->once())
            ->method('execute')
            ->with([
                ':work_date' => '2026-06-01',
                ':late_after' => '2026-06-01 09:00:00',
            ])
            ->willReturn(true);
        $stmt->method('fetchAll')->willReturn([['employee_id' => 'S-101']]);
        $pdo->method('prepare')->willReturn($stmt);

        $db = new EmailAlertDb($pdo);
        $result = $db->fetchLateArrivals('2026-06-01', '09:00');

        $this->assertSame('S-101', $result[0]['employee_id']);
    }

    public function test_fetch_no_shows_uses_work_date(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->once())
            ->method('execute')
            ->with([':work_date' => '2026-06-01'])
            ->willReturn(true);
        $stmt->method('fetchAll')->willReturn([['employee_id' => 'S-102']]);
        $pdo->method('prepare')->willReturn($stmt);

        $db = new EmailAlertDb($pdo);
        $result = $db->fetchNoShows('2026-06-01');

        $this->assertSame('S-102', $result[0]['employee_id']);
    }

    public function test_store_daily_alert_returns_true_when_insert_succeeds(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->never())->method('prepare');

        $db = new EmailAlertDb($pdo);

        $this->assertTrue($db->storeDailyAlert(
            'Daily alert',
            'No late arrivals or no-shows today.',
            ['late_arrivals' => [], 'no_shows' => []],
            '2026-06-01 10:00:00'
        ));
    }

    public function test_store_attendance_alerts_creates_late_and_no_show_rows(): void
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->exactly(4))->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn(['count' => 0]);
        $pdo->expects($this->exactly(4))
            ->method('prepare')
            ->with($this->logicalOr(
                $this->stringContains('SELECT COUNT(*) AS count'),
                $this->stringContains('INSERT INTO alerts')
            ))
            ->willReturn($stmt);

        $db = new EmailAlertDb($pdo);
        $stored = $db->storeAttendanceAlerts(
            '2026-06-01',
            [['profile_id' => '1', 'first_name' => 'Sarah', 'last_name' => 'Mthembu', 'employee_id' => 'S-101']],
            [['profile_id' => '2', 'first_name' => 'John', 'last_name' => 'Doe', 'employee_id' => 'S-102']],
            '2026-06-01 10:00:00'
        );

        $this->assertSame(2, $stored);
    }
}

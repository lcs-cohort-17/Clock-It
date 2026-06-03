<?php

namespace Tests\AdminDashboard;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/models/AdminDashboardDb.php';

class FakeStatementModel extends \PDOStatement
{
    private ?int $count;

    private array $rows;

    public function __construct(?int $count = null, array $rows = [])
    {
        $this->count = $count;
        $this->rows = $rows;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        return $this->count;
    }

    public function fetchAll(int $mode = \PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->rows;
    }
}

class FakePDOModel extends \PDO
{
    private array $results;

    public int $queryCount = 0;
    public array $calledQueries = [];

    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): \PDOStatement|false
    {
        $this->calledQueries[] = $query;

        if (!array_key_exists($this->queryCount, $this->results)) {
            throw new \RuntimeException('No more query results configured');
        }

        $result = $this->results[$this->queryCount++];

        if (($result['error'] ?? null) !== null) {
            throw new \RuntimeException($result['error']);
        }

        return new FakeStatementModel($result['count'] ?? null, $result['rows'] ?? []);
    }
}

class AdminDashboardModelTest extends TestCase
{
    private static array $defaultCounts = [
        ['count' => 5, 'error' => null],
        ['count' => 12, 'error' => null],
        ['count' => 3, 'error' => null],
        ['count' => 20, 'error' => null],
    ];

    private function buildModel(array $results): array
    {
        $db = new FakePDOModel($results);
        $model = new \AdminDashboardModel($db);

        return ['model' => $model, 'db' => $db];
    }

    public function test_maps_counts_to_named_fields_and_icons(): void
    {
        $helpers = $this->buildModel(self::$defaultCounts);
        $stats = $helpers['model']->fetchStats();

        $this->assertSame(['value' => 5, 'icon' => 'people'], $stats['currentlyOnsite']);
        $this->assertSame(['value' => 12, 'icon' => 'login'], $stats['totalClockedInToday']);
        $this->assertSame(['value' => 3, 'icon' => 'sync_problem'], $stats['pendingSync']);
        $this->assertSame(['value' => 20, 'icon' => 'event'], $stats['totalEventsToday']);
    }

    public function test_falls_back_to_zero_on_null_counts(): void
    {
        $helpers = $this->buildModel([
            ['count' => null, 'error' => null],
            ['count' => null, 'error' => null],
            ['count' => null, 'error' => null],
            ['count' => null, 'error' => null],
        ]);

        $stats = $helpers['model']->fetchStats();

        $this->assertSame(0, $stats['currentlyOnsite']['value']);
        $this->assertSame(0, $stats['totalClockedInToday']['value']);
        $this->assertSame(0, $stats['pendingSync']['value']);
        $this->assertSame(0, $stats['totalEventsToday']['value']);
    }

    public function test_stats_queries_match_typescript_model_targets(): void
    {
        $helpers = $this->buildModel(self::$defaultCounts);
        $helpers['model']->fetchStats();

        $queries = $helpers['db']->calledQueries;

        $this->assertCount(4, $queries);
        $this->assertStringContainsString('FROM sessions', $queries[0]);
        $this->assertStringContainsString('clock_out_time IS NULL', $queries[0]);

        $this->assertStringContainsString('FROM attendance_logs', $queries[1]);
        $this->assertStringContainsString("event_type = 'in'", $queries[1]);
        $this->assertStringContainsString('event_time >=', $queries[1]);
        $this->assertStringContainsString('event_time <', $queries[1]);

        $this->assertStringContainsString('FROM attendance_logs', $queries[2]);
        $this->assertStringContainsString("sync_status = 'pending'", $queries[2]);

        $this->assertStringContainsString('FROM attendance_logs', $queries[3]);
        $this->assertStringContainsString('event_time >=', $queries[3]);
        $this->assertStringContainsString('event_time <', $queries[3]);
    }

    public function test_error_messages_are_collected_and_concatenated(): void
    {
        $helpers = $this->buildModel([
            ['count' => null, 'error' => 'Error A'],
            ['count' => null, 'error' => 'Error B'],
            ['count' => 0, 'error' => null],
            ['count' => 0, 'error' => null],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error A | Error B');

        $helpers['model']->fetchStats();
    }

    public function test_fetch_recent_activity_matches_typescript_paging_and_shape(): void
    {
        $helpers = $this->buildModel([
            [
                'rows' => [[
                    'profile_id' => 10,
                    'event_time' => '2025-05-19 10:00:00',
                    'event_type' => 'in',
                    'sync_status' => 'pending',
                    'device_info' => 'front desk',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]],
            ],
        ]);

        $rows = $helpers['model']->fetchRecentActivity(2, 10);

        $this->assertStringContainsString('FROM attendance_logs al', $helpers['db']->calledQueries[0]);
        $this->assertStringContainsString('ORDER BY al.event_time DESC', $helpers['db']->calledQueries[0]);
        $this->assertStringContainsString('LIMIT 10 OFFSET 10', $helpers['db']->calledQueries[0]);
        $this->assertSame('John', $rows[0]['profiles']['first_name']);
        $this->assertSame('Doe', $rows[0]['profiles']['last_name']);
        $this->assertArrayNotHasKey('first_name', $rows[0]);
    }

    public function test_fetch_currently_onsite_matches_typescript_ordering_and_shape(): void
    {
        $helpers = $this->buildModel([
            [
                'rows' => [[
                    'profile_id' => 10,
                    'event_time' => '2025-05-19 10:00:00',
                    'location' => 'HQ',
                    'event_type' => 'in',
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                ]],
            ],
        ]);

        $rows = $helpers['model']->fetchCurrentlyOnsite();

        $this->assertStringContainsString('FROM attendance_logs al', $helpers['db']->calledQueries[0]);
        $this->assertStringContainsString('ORDER BY al.event_time DESC', $helpers['db']->calledQueries[0]);
        $this->assertSame('HQ', $rows[0]['location']);
        $this->assertSame('Jane', $rows[0]['profiles']['first_name']);
        $this->assertSame('Smith', $rows[0]['profiles']['last_name']);
    }
}

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
    // 💡 UPDATED: Reduced to exactly 3 mock results to match the 3 optimized queries
    private static array $defaultCounts = [
        ['count' => 5, 'error' => null],   // Onsite query
        ['count' => 12, 'error' => null],  // Clock ins query
        ['count' => 20, 'error' => null],  // Total events query
    ];

    private function buildModel(array $results): array
    {
        $db = new FakePDOModel($results);
        $model = new \AdminDashboardModel($db);

        return ['model' => $model, 'db' => $db];
    }

    // 💡 UPDATED: Renamed and rewritten to check for new flat integer layout
    public function test_maps_counts_to_flat_named_fields(): void
    {
        $helpers = $this->buildModel(self::$defaultCounts);
        $stats = $helpers['model']->fetchStats();

        $this->assertSame(5, $stats['currentlyOnsite']);
        $this->assertSame(12, $stats['totalClockedInToday']);
        $this->assertSame(0, $stats['pendingSync']); // Verifies fallback constant compliance
        $this->assertSame(20, $stats['totalEventsToday']);
    }

    // 💡 UPDATED: Assertions direct-mapped to raw integers falling back to 0
    public function test_falls_back_to_zero_on_null_counts(): void
    {
        $helpers = $this->buildModel([
            ['count' => null, 'error' => null],
            ['count' => null, 'error' => null],
            ['count' => null, 'error' => null],
        ]);

        $stats = $helpers['model']->fetchStats();

        $this->assertSame(0, $stats['currentlyOnsite']);
        $this->assertSame(0, $stats['totalClockedInToday']);
        $this->assertSame(0, $stats['pendingSync']);
        $this->assertSame(0, $stats['totalEventsToday']);
    }

    // 💡 UPDATED: Query tracking updated to exactly 3 executions and asserts proper syntax targets
    public function test_stats_queries_match_model_targets(): void
    {
        $helpers = $this->buildModel(self::$defaultCounts);
        $helpers['model']->fetchStats();

        $queries = $helpers['db']->calledQueries;

        $this->assertCount(3, $queries);
        
        // Query 1: Active Sessions
        $this->assertStringContainsString('FROM sessions', $queries[0]);
        $this->assertStringContainsString('clock_out_time IS NULL', $queries[0]);

        // Query 2: Total Clock-ins Today
        $this->assertStringContainsString('FROM attendance_logs', $queries[1]);
        $this->assertStringContainsString("event_type = 'in'", $queries[1]);

        // Query 3: Total Logs Today
        $this->assertStringContainsString('FROM attendance_logs', $queries[2]);
        $this->assertStringNotContainsString("event_type =", $queries[2]);
    }

    // 💡 UPDATED: Matches 3 query pipeline count
    public function test_error_messages_are_collected_and_concatenated(): void
    {
        $helpers = $this->buildModel([
            ['count' => null, 'error' => 'Error A'],
            ['count' => null, 'error' => 'Error B'],
            ['count' => 0, 'error' => null],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error A | Error B');

        $helpers['model']->fetchStats();
    }

    // 💡 UPDATED: Target nesting sub-array shifted from 'profiles' to 'users'
    public function test_fetch_recent_activity_matches_paging_and_shape(): void
    {
        $helpers = $this->buildModel([
            [
                'rows' => [[
                    'user_id' => 10,
                    'event_time' => '2026-06-02 10:00:00',
                    'event_type' => 'in',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'role' => 'Staff'
                ]],
            ],
        ]);

        $rows = $helpers['model']->fetchRecentActivity(2, 10);

        $this->assertStringContainsString('FROM attendance_logs al', $helpers['db']->calledQueries[0]);
        $this->assertStringContainsString('ORDER BY al.event_time DESC', $helpers['db']->calledQueries[0]);
        $this->assertStringContainsString('LIMIT 10 OFFSET 10', $helpers['db']->calledQueries[0]);
        
        // Confirms restructuring behavior
        $this->assertSame('John', $rows[0]['users']['first_name']);
        $this->assertSame('Doe', $rows[0]['users']['last_name']);
        $this->assertSame('Staff', $rows[0]['users']['role']);
        $this->assertArrayNotHasKey('first_name', $rows[0]);
    }

    // 💡 UPDATED: Aligned sql targets to track 'sessions s' table structures and 'users' mappings
    public function test_fetch_currently_onsite_matches_ordering_and_shape(): void
    {
        $helpers = $this->buildModel([
            [
                'rows' => [[
                    'user_id' => 10,
                    'event_time' => '2026-06-02 10:00:00',
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'role' => 'Admin'
                ]],
            ],
        ]);

        $rows = $helpers['model']->fetchCurrentlyOnsite();

        $this->assertStringContainsString('FROM sessions s', $helpers['db']->calledQueries[0]);
        $this->assertStringContainsString('ORDER BY s.clock_in_time DESC', $helpers['db']->calledQueries[0]);
        
        $this->assertSame('Jane', $rows[0]['users']['first_name']);
        $this->assertSame('Smith', $rows[0]['users']['last_name']);
        $this->assertSame('Admin', $rows[0]['users']['role']);
    }
}
<?php

namespace Tests\AdminDashboard;

use PHPUnit\Framework\TestCase;

class FakeStatement
{
    private int $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function fetchColumn(): int
    {
        return $this->count;
    }
}

class FakeMySQLClient
{
    private array $results;
    public int $queryCount = 0;

    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function query(string $sql): FakeStatement
    {
        if (!array_key_exists($this->queryCount, $this->results)) {
            throw new \RuntimeException('No more query results configured');
        }

        $result = $this->results[$this->queryCount++];

        if ($result['error'] !== null) {
            throw new \RuntimeException($result['error']);
        }

        return new FakeStatement((int) $result['count']);
    }
}

class AdminDashboardController
{
    private FakeMySQLClient $db;
    private int $cacheTtlMs;
    private ?array $cache = null;
    private int $cacheExpiresAtMs = 0;
    private \Closure $clock;

    public function __construct(FakeMySQLClient $db, int $cacheTtlMs = 5000, ?callable $clock = null)
    {
        $this->db = $db;
        $this->cacheTtlMs = $cacheTtlMs;
        $this->clock = $clock !== null
            ? \Closure::fromCallable($clock)
            : static fn (): int => (int) floor(microtime(true) * 1000);
    }

    public function stats(): array
    {
        $now = ($this->clock)();

        if ($this->cache !== null && $now < $this->cacheExpiresAtMs) {
            return [
                'status' => 200,
                'body' => $this->cache,
            ];
        }

        try {
            $errors = [];
            
            // 💡 UPDATED: Now executing exactly 3 optimized queries (Removed the $pendingSync query block)
            $currentlyOnsite = $this->tryFetchCount('SELECT COUNT(*) AS count FROM sessions WHERE clock_out_time IS NULL', $errors);
            $totalClockedInToday = $this->tryFetchCount("SELECT COUNT(*) AS count FROM attendance_logs WHERE event_type = 'in' AND event_time >= CURDATE() AND event_time < DATE_ADD(CURDATE(), INTERVAL 1 DAY)", $errors);
            $totalEventsToday = $this->tryFetchCount("SELECT COUNT(*) AS count FROM attendance_logs WHERE event_time >= CURDATE() AND event_time < DATE_ADD(CURDATE(), INTERVAL 1 DAY)", $errors);

            if (!empty($errors)) {
                throw new \RuntimeException(implode(' | ', $errors));
            }

            // 💡 UPDATED: Flattened data payload to matching format
            $body = [
                'currentlyOnsite' => $currentlyOnsite,
                'totalClockedInToday' => $totalClockedInToday,
                'pendingSync' => 0,
                'totalEventsToday' => $totalEventsToday,
            ];

            $this->cache = $body;
            $this->cacheExpiresAtMs = $now + $this->cacheTtlMs;

            return [
                'status' => 200,
                'body' => $body,
            ];
        } catch (\Throwable $exception) {
            $this->cache = null;
            $this->cacheExpiresAtMs = 0;

            return [
                'status' => 500,
                'body' => ['error' => $exception->getMessage()],
            ];
        }
    }

    private function fetchCount(string $sql): int
    {
        return $this->db->query($sql)->fetchColumn();
    }

    private function tryFetchCount(string $sql, array &$errors): int
    {
        try {
            return $this->fetchCount($sql);
        } catch (\Throwable $exception) {
            $errors[] = $exception->getMessage();
            return 0;
        }
    }
}

class AdminDashboardControllerTest extends TestCase
{
    private function buildController(array $results, int &$nowMs): array
    {
        $db = new FakeMySQLClient($results);
        $controller = new AdminDashboardController($db, 5000, function () use (&$nowMs) {
            return $nowMs;
        });

        return ['controller' => $controller, 'db' => $db];
    }

    public function test_cache_warm_does_not_query_mysql_on_second_request_within_ttl(): void
    {
        $nowMs = 0;
        $helpers = $this->buildController([
            ['count' => 5, 'error' => null],
            ['count' => 10, 'error' => null],
            ['count' => 15, 'error' => null],
            ['count' => 99, 'error' => null],
            ['count' => 99, 'error' => null],
            ['count' => 99, 'error' => null],
        ], $nowMs);

        $controller = $helpers['controller'];
        $db = $helpers['db'];

        $controller->stats();
        $nowMs += 1000;
        $controller->stats();

        // 💡 UPDATED: Expects 3 queries on cache warm
        $this->assertSame(3, $db->queryCount);
    }

    public function test_cache_hit_returns_identical_data(): void
    {
        $nowMs = 0;
        $helpers = $this->buildController([
            ['count' => 5, 'error' => null],
            ['count' => 10, 'error' => null],
            ['count' => 15, 'error' => null],
        ], $nowMs);

        $controller = $helpers['controller'];

        $first = $controller->stats();
        $nowMs += 1000;
        $second = $controller->stats();

        $this->assertSame($first['body'], $second['body']);
    }

    public function test_serves_cached_data_up_to_but_not_including_the_ttl_boundary(): void
    {
        $nowMs = 0;
        $helpers = $this->buildController([
            ['count' => 5, 'error' => null],
            ['count' => 10, 'error' => null],
            ['count' => 15, 'error' => null],
        ], $nowMs);

        $controller = $helpers['controller'];
        $db = $helpers['db'];

        $controller->stats();
        $nowMs += 1000;
        $nowMs += 3999;
        $controller->stats();

        // 💡 UPDATED: Expects 3 queries on cache hit boundary
        $this->assertSame(3, $db->queryCount);
    }

    public function test_re_fetches_from_mysql_after_ttl_expires(): void
    {
        $nowMs = 0;
        $results = [
            ['count' => 5, 'error' => null],
            ['count' => 10, 'error' => null],
            ['count' => 15, 'error' => null],
            ['count' => 50, 'error' => null],
            ['count' => 60, 'error' => null],
            ['count' => 70, 'error' => null],
        ];

        $helpers = $this->buildController($results, $nowMs);
        $controller = $helpers['controller'];
        $db = $helpers['db'];

        $first = $controller->stats();
        // 💡 UPDATED: Flat assertion
        $this->assertSame(5, $first['body']['currentlyOnsite']);

        $nowMs += 5001;
        $nowMs += 1000;

        $second = $controller->stats();
        // 💡 UPDATED: Flat assertion
        $this->assertSame(50, $second['body']['currentlyOnsite']);
        // 💡 UPDATED: Expects 6 total queries (3 + 3)
        $this->assertSame(6, $db->queryCount);
    }

    public function test_reflects_updated_counts_after_ttl_expires(): void
    {
        $nowMs = 0;
        $results = [
            ['count' => 5, 'error' => null],
            ['count' => 10, 'error' => null],
            ['count' => 15, 'error' => null],
            ['count' => 100, 'error' => null],
            ['count' => 200, 'error' => null],
            ['count' => 400, 'error' => null],
        ];

        $helpers = $this->buildController($results, $nowMs);
        $controller = $helpers['controller'];

        $controller->stats();
        $nowMs += 5001;
        $nowMs += 1000;

        $response = $controller->stats();

        // 💡 UPDATED: Direct flat key evaluations
        $this->assertSame(100, $response['body']['currentlyOnsite']);
        $this->assertSame(200, $response['body']['totalClockedInToday']);
        $this->assertSame(0, $response['body']['pendingSync']);
        $this->assertSame(400, $response['body']['totalEventsToday']);
    }

    public function test_failed_responses_are_not_cached_and_retry_on_next_request(): void
    {
        $nowMs = 0;
        $results = [
            ['count' => null, 'error' => 'transient failure'],
            ['count' => null, 'error' => 'transient failure'],
            ['count' => null, 'error' => 'transient failure'],
            ['count' => 7, 'error' => null],
            ['count' => 0, 'error' => null],
            ['count' => 0, 'error' => null],
        ];

        $helpers = $this->buildController($results, $nowMs);
        $controller = $helpers['controller'];
        $db = $helpers['db'];

        $first = $controller->stats();
        $this->assertSame(500, $first['status']);

        $nowMs += 1000;

        $second = $controller->stats();
        $this->assertSame(200, $second['status']);
        // 💡 UPDATED: Direct flat key evaluation
        $this->assertSame(7, $second['body']['currentlyOnsite']);
        // 💡 UPDATED: Expects 6 queries (3 failed + 3 successful retries)
        $this->assertSame(6, $db->queryCount);
    }

    public function test_keeps_retrying_on_every_failed_request(): void
    {
        $nowMs = 0;
        $results = [
            ['count' => null, 'error' => 'fail'],
            ['count' => null, 'error' => 'fail'],
            ['count' => null, 'error' => 'fail'],
            ['count' => null, 'error' => 'fail'],
            ['count' => null, 'error' => 'fail'],
            ['count' => null, 'error' => 'fail'],
        ];

        $helpers = $this->buildController($results, $nowMs);
        $controller = $helpers['controller'];
        $db = $helpers['db'];

        $first = $controller->stats();
        $this->assertSame(500, $first['status']);

        $nowMs += 1000;

        $second = $controller->stats();
        $this->assertSame(500, $second['status']);
        // 💡 UPDATED: Expects 6 queries total across both failures (3 + 3)
        $this->assertSame(6, $db->queryCount);
    }
}

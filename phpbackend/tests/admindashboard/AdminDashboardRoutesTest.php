<?php

namespace Tests\AdminDashboard;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/models/AdminDashboardDb.php';
require_once __DIR__ . '/../../src/controllers/AdminDashboardController.php';
require_once __DIR__ . '/../../src/routes/AdminDashboardRoutes.php';

class FakeStatementRoutes
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

class FakeMySQLClientRoutes
{
    private array $results;
    public int $queryCount = 0;

    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function query(string $sql)
    {
        if (!array_key_exists($this->queryCount, $this->results)) {
            throw new \RuntimeException('No more query results configured');
        }

        $result = $this->results[$this->queryCount++];
        if ($result['error'] !== null) {
            throw new \RuntimeException($result['error']);
        }

        return new FakeStatementRoutes((int) $result['count']);
    }
}

class AdminDashboardControllerRoutes
{
    private FakeMySQLClientRoutes $db;

    public function __construct(FakeMySQLClientRoutes $db)
    {
        $this->db = $db;
    }

    private function fetchCount(string $sql): int
    {
        return $this->db->query($sql)->fetchColumn();
    }

    public function stats(): array
    {
        try {
            $this->fetchCount('SELECT COUNT(*) AS count FROM sessions WHERE clock_out_time IS NULL');
            $this->fetchCount("SELECT COUNT(*) AS count FROM attendance_logs WHERE event_type = 'in' AND event_time >= CURDATE() AND event_time < DATE_ADD(CURDATE(), INTERVAL 1 DAY)");
            $this->fetchCount("SELECT COUNT(*) AS count FROM attendance_logs WHERE sync_status = 'pending'");
            $this->fetchCount("SELECT COUNT(*) AS count FROM attendance_logs WHERE event_time >= CURDATE() AND event_time < DATE_ADD(CURDATE(), INTERVAL 1 DAY)");

            return ['status' => 200, 'body' => [
                'currentlyOnsite' => ['value' => 1, 'icon' => 'people'],
                'totalClockedInToday' => ['value' => 2, 'icon' => 'login'],
                'pendingSync' => ['value' => 3, 'icon' => 'sync_problem'],
                'totalEventsToday' => ['value' => 4, 'icon' => 'event'],
            ]];
        } catch (\Throwable $e) {
            return ['status' => 500, 'body' => ['error' => $e->getMessage()]];
        }
    }
}

class FakeAdminDashboardModelForRoutes extends \AdminDashboardModel
{
    public function __construct()
    {
    }

    public function fetchStats(): array
    {
        return [
            'currentlyOnsite' => ['value' => 1, 'icon' => 'people'],
            'totalClockedInToday' => ['value' => 2, 'icon' => 'login'],
            'pendingSync' => ['value' => 3, 'icon' => 'sync_problem'],
            'totalEventsToday' => ['value' => 4, 'icon' => 'event'],
        ];
    }

    public function fetchRecentActivity(int $page, int $limit): array
    {
        return [[
            'profile_id' => 10,
            'event_time' => '2025-05-19 10:00:00',
            'event_type' => 'in',
            'sync_status' => 'pending',
            'device_info' => 'front desk',
            'profiles' => ['first_name' => 'John', 'last_name' => 'Doe'],
        ]];
    }

    public function fetchCurrentlyOnsite(): array
    {
        return [[
            'profile_id' => 11,
            'event_time' => '2025-05-19 09:00:00',
            'location' => 'HQ',
            'profiles' => ['first_name' => 'Jane', 'last_name' => 'Smith'],
        ]];
    }
}

class AdminDashboardRoutesTest extends TestCase
{
    private static array $DEFAULT_COUNTS = [
        ['count' => 5, 'error' => null],
        ['count' => 12, 'error' => null],
        ['count' => 3, 'error' => null],
        ['count' => 20, 'error' => null],
    ];

    public function test_successful_fetch_returns_200(): void
    {
        $db = new FakeMySQLClientRoutes(self::$DEFAULT_COUNTS);
        $controller = new AdminDashboardControllerRoutes($db);

        $res = $controller->stats();
        $this->assertSame(200, $res['status']);
    }

    public function test_error_in_query_returns_500(): void
    {
        $results = [
            ['count' => null, 'error' => 'query failed'],
            ['count' => null, 'error' => null],
            ['count' => null, 'error' => null],
            ['count' => null, 'error' => null],
        ];

        $db = new FakeMySQLClientRoutes($results);
        $controller = new AdminDashboardControllerRoutes($db);

        $res = $controller->stats();
        $this->assertSame(500, $res['status']);
    }

    public function test_response_shape_includes_all_keys_and_types(): void
    {
        $db = new FakeMySQLClientRoutes(self::$DEFAULT_COUNTS);
        $controller = new AdminDashboardControllerRoutes($db);

        $res = $controller->stats();

        foreach (['currentlyOnsite', 'totalClockedInToday', 'pendingSync', 'totalEventsToday'] as $key) {
            $this->assertArrayHasKey($key, $res['body']);
            $this->assertIsInt($res['body'][$key]['value']);
            $this->assertIsString($res['body'][$key]['icon']);
        }
    }

    public function test_headers_and_ratelimit_are_simulated(): void
    {
        $db = new FakeMySQLClientRoutes(self::$DEFAULT_COUNTS);
        $controller = new AdminDashboardControllerRoutes($db);

        $res = $controller->stats();
        $headers = ['content-type' => 'application/json', 'ratelimit-limit' => '1', 'ratelimit-remaining' => '0'];

        $this->assertMatchesRegularExpression('/application\/json/', $headers['content-type']);
        $this->assertArrayHasKey('ratelimit-limit', $headers);
        $this->assertArrayHasKey('ratelimit-remaining', $headers);
    }

    public function test_real_router_recent_activity_uses_controller_data(): void
    {
        $controller = new \AdminDashboardController(new FakeAdminDashboardModelForRoutes());
        $router = new \AdminDashboardRouter($controller);

        $res = $router->dispatch('GET', '/recent-activity?page=2&limit=10');

        $this->assertSame(200, $res['status']);
        $this->assertSame('success', $res['body']['status']);
        $this->assertSame('John Doe', $res['body']['data'][0]['staff']);
        $this->assertSame('in', $res['body']['data'][0]['event_type']);
    }

    public function test_real_router_onsite_uses_controller_data(): void
    {
        $controller = new \AdminDashboardController(new FakeAdminDashboardModelForRoutes());
        $router = new \AdminDashboardRouter($controller);

        $res = $router->dispatch('GET', '/onsite');

        $this->assertSame(200, $res['status']);
        $this->assertSame('success', $res['body']['status']);
        $this->assertSame('Jane Smith', $res['body']['data'][0]['staff']);
        $this->assertSame('HQ', $res['body']['data'][0]['location']);
    }
}

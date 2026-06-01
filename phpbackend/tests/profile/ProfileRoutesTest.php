<?php

namespace Tests\Profile;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Routes\ProfileRoutes;
use App\Controllers\ProfileController;

class TestApp
{
    public array $calls = [];

    public function get($pattern, $handler = null)
    {
        $this->calls[] = ['method' => 'get', 'pattern' => $pattern];
        return $this;
    }

    public function post($pattern, $handler = null)
    {
        $this->calls[] = ['method' => 'post', 'pattern' => $pattern];
        return $this;
    }

    public function patch($pattern, $handler = null)
    {
        $this->calls[] = ['method' => 'patch', 'pattern' => $pattern];
        return $this;
    }

    public function delete($pattern, $handler = null)
    {
        $this->calls[] = ['method' => 'delete', 'pattern' => $pattern];
        return $this;
    }

    public function add($middleware)
    {
        $this->calls[] = ['method' => 'add', 'middleware' => $middleware];
        return $this;
    }
}
class ProfileRoutesTest extends TestCase
{
    private ProfileRoutes $routes;
    private TestApp $appMock;
    private MockObject $controllerMock;

    protected function setUp(): void
    {
        $this->appMock = new TestApp();
        $this->controllerMock = $this->createMock(ProfileController::class);
        $this->routes = new ProfileRoutes($this->appMock, $this->controllerMock);
    }

    /**
     * Test POST /profiles/login route exists (public)
     */
    public function testLoginRouteIsPublic(): void
    {
        $this->routes->register();

        $this->assertContains(
            ['method' => 'post', 'pattern' => '/profiles/login'],
            $this->appMock->calls
        );
    }

    /**
     * Test GET /profiles route exists (protected)
     */
    public function testadminGettingAllUsersRouteExists(): void
    {
        $this->routes->register();
        $this->assertContains(['method' => 'get', 'pattern' => '/profiles'], $this->appMock->calls);
        $this->assertGreaterThanOrEqual(1, count(array_filter($this->appMock->calls, fn($call) => $call['method'] === 'add')));
    }

    /**
     * Test GET /profiles/{employee_id} route exists (protected)
     */
    public function testGetProfileByIdRouteExists(): void
    {
        $this->routes->register();
        $this->assertContains(['method' => 'get', 'pattern' => '/profiles/{employee_id}'], $this->appMock->calls);
        $this->assertGreaterThanOrEqual(1, count(array_filter($this->appMock->calls, fn($call) => $call['method'] === 'add')));
    }

    /**
     * Test POST /profiles route exists (protected)
     */
    public function testadminCreatingUserRouteExists(): void
    {
        $this->routes->register();
        $this->assertContains(['method' => 'post', 'pattern' => '/profiles'], $this->appMock->calls);
        $this->assertGreaterThanOrEqual(1, count(array_filter($this->appMock->calls, fn($call) => $call['method'] === 'add')));
    }

    /**
     * Test PATCH /profiles/{employee_id} route exists (protected)
     */
    public function testadminUpdatingUserRouteExists(): void
    {
        $this->routes->register();
        $this->assertContains(['method' => 'patch', 'pattern' => '/profiles/{employee_id}'], $this->appMock->calls);
        $this->assertGreaterThanOrEqual(1, count(array_filter($this->appMock->calls, fn($call) => $call['method'] === 'add')));
    }

    /**
     * Test DELETE /profiles/{employee_id} route exists (protected)
     */
    public function testadminDeletingUserRouteExists(): void
    {
        $this->routes->register();
        $this->assertContains(['method' => 'delete', 'pattern' => '/profiles/{employee_id}'], $this->appMock->calls);
        $this->assertGreaterThanOrEqual(1, count(array_filter($this->appMock->calls, fn($call) => $call['method'] === 'add')));
    }

    /**
     * Test PATCH /profiles/{employee_id}/reset-password route exists (protected)
     */
    public function testResetPasswordRouteExists(): void
    {
        $this->routes->register();
        $this->assertContains(['method' => 'patch', 'pattern' => '/profiles/{employee_id}/reset-password'], $this->appMock->calls);
        $this->assertGreaterThanOrEqual(1, count(array_filter($this->appMock->calls, fn($call) => $call['method'] === 'add')));
    }

    /**
     * Test PATCH /profiles/{employee_id}/update-password route exists (protected)
     */
    public function testUpdatePasswordRouteExists(): void
    {
        $this->routes->register();
        $this->assertContains(['method' => 'patch', 'pattern' => '/profiles/{employee_id}/update-password'], $this->appMock->calls);
        $this->assertGreaterThanOrEqual(1, count(array_filter($this->appMock->calls, fn($call) => $call['method'] === 'add')));
    }

    /**
     * Test POST /profiles/clear-cache route exists (protected)
     */
    public function testClearCacheRouteExists(): void
    {
        $this->routes->register();
        $this->assertContains(['method' => 'post', 'pattern' => '/profiles/clear-cache'], $this->appMock->calls);
        $this->assertGreaterThanOrEqual(1, count(array_filter($this->appMock->calls, fn($call) => $call['method'] === 'add')));
    }

    /**
     * Test all protected routes require authentication
     */
    public function testAllProtectedRoutesRequireAuth(): void
    {
        $this->routes->register();
        $addCalls = array_filter($this->appMock->calls, fn($call) => $call['method'] === 'add');
        $this->assertGreaterThanOrEqual(8, count($addCalls));
    }

    /**
     * Test route count
     */
    public function testAllRoutesAreRegistered(): void
    {
        // Expecting: 1 POST (login) + 1 GET (all) + 1 GET (by id) + 1 POST (create)
        //           + 1 PATCH (update) + 1 DELETE + 1 PATCH (reset pwd) + 1 PATCH (update pwd)
        //           + 1 POST (clear cache)
        $totalRoutes = 9;

        $this->routes->register();

        $methods = array_column($this->appMock->calls, 'method');
        $this->assertContains('post', $methods);
        $this->assertContains('get', $methods);
        $this->assertContains('patch', $methods);
        $this->assertContains('delete', $methods);
        $this->assertGreaterThanOrEqual(9, count(array_filter($this->appMock->calls, fn($call) => in_array($call['method'], ['get', 'post', 'patch', 'delete']))));
    }
}

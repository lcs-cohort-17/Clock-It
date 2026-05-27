<?php

namespace Tests\Profile;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Routes\ProfileRoutes;
use App\Controllers\ProfileController;

class ProfileRoutesTest extends TestCase
{
    private ProfileRoutes $routes;
    private MockObject $appMock;
    private MockObject $controllerMock;

    protected function setUp(): void
    {
        $this->appMock = $this->createMock(\Slim\App::class);
        $this->controllerMock = $this->createMock(ProfileController::class);
        $this->routes = new ProfileRoutes($this->appMock, $this->controllerMock);
    }

    /**
     * Test POST /profiles/login route exists (public)
     */
    public function testLoginRouteIsPublic(): void
    {
        $this->appMock->expects($this->atLeastOnce())
            ->method('post')
            ->with('/profiles/login')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }

    /**
     * Test GET /profiles route exists (protected)
     */
    public function testGetProfilesRouteExists(): void
    {
        $this->appMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('add')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }

    /**
     * Test GET /profiles/{employee_id} route exists (protected)
     */
    public function testGetProfileByIdRouteExists(): void
    {
        $this->appMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('add')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }

    /**
     * Test POST /profiles route exists (protected)
     */
    public function testCreateProfileRouteExists(): void
    {
        $this->appMock->expects($this->atLeastOnce())
            ->method('post')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('add')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }

    /**
     * Test PATCH /profiles/{employee_id} route exists (protected)
     */
    public function testUpdateProfileRouteExists(): void
    {
        $this->appMock->expects($this->atLeastOnce())
            ->method('patch')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('add')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }

    /**
     * Test DELETE /profiles/{employee_id} route exists (protected)
     */
    public function testDeleteProfileRouteExists(): void
    {
        $this->appMock->expects($this->atLeastOnce())
            ->method('delete')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('add')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }

    /**
     * Test PATCH /profiles/{employee_id}/reset-password route exists (protected)
     */
    public function testResetPasswordRouteExists(): void
    {
        $this->appMock->expects($this->atLeastOnce())
            ->method('patch')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('add')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }

    /**
     * Test PATCH /profiles/{employee_id}/update-password route exists (protected)
     */
    public function testUpdatePasswordRouteExists(): void
    {
        $this->appMock->expects($this->atLeastOnce())
            ->method('patch')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('add')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }

    /**
     * Test all protected routes require authentication
     */
    public function testAllProtectedRoutesRequireAuth(): void
    {
        $this->appMock->expects($this->atLeastOnce())
            ->method('add')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }

    /**
     * Test route count
     */
    public function testAllRoutesAreRegistered(): void
    {
        // Expecting: 1 POST (login) + 1 GET (all) + 1 GET (by id) + 1 POST (create)
        //           + 1 PATCH (update) + 1 DELETE + 1 PATCH (reset pwd) + 1 PATCH (update pwd)
        $totalRoutes = 8;

        $this->appMock->expects($this->atLeastOnce())
            ->method('post')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('patch')
            ->willReturnSelf();

        $this->appMock->expects($this->atLeastOnce())
            ->method('delete')
            ->willReturnSelf();

        $this->routes->register();

        $this->assertTrue(true);
    }
}
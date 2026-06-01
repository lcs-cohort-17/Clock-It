<?php

use PHPUnit\Framework\TestCase;

class GoogleSheetsRoutesTest extends TestCase
{
    public function testStatusRouteExists(): void
    {
        $routes = require __DIR__ . '/../../routes/google.php';

        $this->assertArrayHasKey(
            'GET /api/admin/sheets/status',
            $routes
        );
    }

    public function testExportRouteExists(): void
    {
        $routes = require __DIR__ . '/../../routes/google.php';

        $this->assertArrayHasKey(
            'POST /api/admin/sheets/export',
            $routes
        );
    }

    public function testSyncRouteExists(): void
    {
        $routes = require __DIR__ . '/../../routes/google.php';

        $this->assertArrayHasKey(
            'POST /api/admin/sheets/sync',
            $routes
        );
    }

    public function testDisconnectRouteExists(): void
    {
        $routes = require __DIR__ . '/../../routes/google.php';

        $this->assertArrayHasKey(
            'POST /api/admin/sheets/disconnect',
            $routes
        );
    }
} 
<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

final class HeaderTest extends TestCase
{
    public function testCanonicalHeaderHasMobileAndThemeToggles(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/phpfrontend/src/views/partials/header.php');

        $this->assertStringContainsString('data-sidebar-toggle', $content);
        $this->assertStringContainsString('data-theme-toggle', $content);
        $this->assertStringContainsString('bi bi-list', $content);
    }
}

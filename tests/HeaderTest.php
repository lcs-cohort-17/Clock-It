<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    private string $headerFile;
    private string $content;

    protected function setUp(): void
    {
        $this->headerFile = dirname(__DIR__) . '/Header.php';
        $this->content    = file_get_contents($this->headerFile);
    }

    /*
    |--------------------------------------------------------------------------
    | FILE EXISTS
    |--------------------------------------------------------------------------
    */

    public function testHeaderFileExists(): void
    {
        $this->assertFileExists($this->headerFile);
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER STRUCTURE
    |--------------------------------------------------------------------------
    */

    public function testHeaderElementExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'class="header"') ||
            str_contains($this->content, 'class="top-bar"') ||
            str_contains($this->content, '<header')
        );
    }

    public function testHeaderHasUserInfo(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'Sarah') ||
            str_contains($this->content, 'user-name') ||
            str_contains($this->content, 'user-info')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE TOGGLE
    |--------------------------------------------------------------------------
    */

    public function testMobileMenuToggleExists(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'fa-bars') ||
            str_contains($this->content, 'navbar-toggler') ||
            str_contains($this->content, 'sidebar-toggle') ||
            str_contains($this->content, 'menu-toggle')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    public function testHeaderUsesFlexLayout(): void
    {
        $this->assertTrue(
            str_contains($this->content, 'd-flex') ||
            str_contains($this->content, 'flex-') ||
            str_contains($this->content, 'justify-content')
        );
    }
}

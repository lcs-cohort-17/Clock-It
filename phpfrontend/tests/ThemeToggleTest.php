<?php

use PHPUnit\Framework\TestCase;

class ThemeToggleTest extends TestCase
{
    private string $dashboardPath;
    private string $headerPath;

    protected function setUp(): void
    {
        $this->dashboardPath =
            __DIR__ . '/../src/views/staff/staff-dashboard.php';

        $this->headerPath =
            __DIR__ . '/../src/views/partials/header.php';
    }

    /*
    |--------------------------------------------------------------------------
    | FILES EXIST
    |--------------------------------------------------------------------------
    */

    public function testDashboardFileExists()
    {
        $this->assertFileExists($this->dashboardPath);
    }

    public function testHeaderFileExists()
    {
        $this->assertFileExists($this->headerPath);
    }

    /*
    |--------------------------------------------------------------------------
    | GET FILE CONTENTS
    |--------------------------------------------------------------------------
    */

    private function getCombinedContent(): string
    {
        return
            file_get_contents($this->dashboardPath)
            .
            file_get_contents($this->headerPath);
    }

    /*
    |--------------------------------------------------------------------------
    | THEME TOGGLE EXISTS
    |--------------------------------------------------------------------------
    */

    public function testThemeToggleExists()
    {
        $content = $this->getCombinedContent();

        $this->assertTrue(
            str_contains($content, 'darkMode') ||
            str_contains($content, 'theme') ||
            str_contains($content, 'toggle')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLICK FUNCTIONALITY EXISTS
    |--------------------------------------------------------------------------
    */

    public function testToggleHasClickFunctionality()
    {
        $content = $this->getCombinedContent();

        $this->assertTrue(
            str_contains($content, '@click') ||
            str_contains($content, 'onclick') ||
            str_contains($content, 'addEventListener')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DARK MODE CLASSES EXIST
    |--------------------------------------------------------------------------
    */

    public function testDarkModeClassesExist()
    {
        $content = $this->getCombinedContent();

        $this->assertTrue(
            str_contains($content, 'dark:bg') ||
            str_contains($content, 'dark:text') ||
            str_contains($content, ':class')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LOCAL STORAGE EXISTS
    |--------------------------------------------------------------------------
    */

    public function testLocalStorageExists()
    {
        $content = $this->getCombinedContent();

        $this->assertStringContainsString(
            'localStorage',
            $content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SYSTEM PREFERENCE SUPPORT
    |--------------------------------------------------------------------------
    */

    public function testSystemPreferenceSupportExists()
    {
        $content = $this->getCombinedContent();

        $this->assertTrue(
            str_contains($content, 'prefers-color-scheme') ||

            str_contains($content, 'matchMedia')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUN / MOON ICONS EXIST
    |--------------------------------------------------------------------------
    */

    public function testSunOrMoonIconsExist()
    {
        $content = $this->getCombinedContent();

        $this->assertTrue(
            str_contains($content, 'sun') ||
            str_contains($content, 'moon') ||
            str_contains($content, '☀') ||
            str_contains($content, '🌙')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BODY HAS DARK MODE CLASSES
    |--------------------------------------------------------------------------
    */

    public function testBodyContainsDarkModeClasses()
    {
        $content = file_get_contents($this->dashboardPath);

        $this->assertTrue(
            str_contains($content, 'dark:bg') &&
            str_contains($content, 'dark:text')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE CLASSES EXIST
    |--------------------------------------------------------------------------
    */

    public function testResponsiveClassesExist()
    {
        $content = $this->getCombinedContent();

        $this->assertTrue(
            str_contains($content, 'sm:') ||
            str_contains($content, 'md:') ||
            str_contains($content, 'lg:')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GRID RESPONSIVENESS EXISTS
    |--------------------------------------------------------------------------
    */

    public function testResponsiveGridExists()
    {
        $content = file_get_contents($this->dashboardPath);

        $this->assertTrue(
            str_contains($content, 'grid-cols-1') &&
            str_contains($content, 'sm:grid-cols-2')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ALPINE JS LOADED
    |--------------------------------------------------------------------------
    */

    public function testAlpineJsLoaded()
    {
        $content = file_get_contents($this->dashboardPath);

        $this->assertTrue(
            str_contains($content, 'alpinejs') ||
            str_contains($content, 'x-data')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TAILWIND LOADED
    |--------------------------------------------------------------------------
    */

    public function testTailwindLoaded()
    {
        $content = file_get_contents($this->dashboardPath);

        $this->assertStringContainsString(
            'tailwindcss',
            $content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VIEWPORT META TAG EXISTS
    |--------------------------------------------------------------------------
    */

    public function testViewportMetaTagExists()
    {
        $content = file_get_contents($this->dashboardPath);

        $this->assertStringContainsString(
            'viewport',
            $content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSITION EFFECTS EXIST
    |--------------------------------------------------------------------------
    */

    public function testTransitionClassesExist()
    {
        $content = file_get_contents($this->dashboardPath);

        $this->assertTrue(
            str_contains($content, 'transition') ||
            str_contains($content, 'duration-')
        );
    }
}
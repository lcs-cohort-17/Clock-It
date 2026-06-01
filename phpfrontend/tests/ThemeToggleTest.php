<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ThemeToggleTest extends TestCase
{
    private string $headerPath;
    private string $layoutPath;
    private string $scriptPath;

    protected function setUp(): void
    {
        $this->headerPath =
            __DIR__ . '/../src/views/partials/header.php';

        $this->layoutPath =
            __DIR__ . '/../src/views/layouts/app.php';

        $this->scriptPath =
            dirname(__DIR__, 2) . '/public/assets/js/theme.js';
    }

    private function getContent(): string
    {
        return
            file_get_contents($this->headerPath)
            .
            file_get_contents($this->layoutPath)
            .
            file_get_contents($this->scriptPath);
    }

    /*
    |--------------------------------------------------------------------------
    | FILES EXIST
    |--------------------------------------------------------------------------
    */

    public function testHeaderExists(): void
    {
        $this->assertFileExists($this->headerPath);
    }

    public function testLayoutExists(): void
    {
        $this->assertFileExists($this->layoutPath);
    }

    /*
    |--------------------------------------------------------------------------
    | THEME TOGGLE EXISTS
    |--------------------------------------------------------------------------
    */

    public function testThemeToggleExists(): void
    {
        $content = $this->getContent();

        $this->assertTrue(
            str_contains($content, 'darkMode')
            ||
            str_contains($content, 'theme')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOGGLE IS CLICKABLE
    |--------------------------------------------------------------------------
    */

    public function testToggleHasClickHandler(): void
    {
        $content = $this->getContent();

        $this->assertTrue(
            str_contains($content, '@click')
            ||
            str_contains($content, 'onclick')
            ||
            str_contains($content, "addEventListener('click'")
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LOCAL STORAGE EXISTS
    |--------------------------------------------------------------------------
    */

    public function testLocalStorageExists(): void
    {
        $content = $this->getContent();

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

    public function testSystemPreferenceSupportExists(): void
    {
        $content = $this->getContent();

        $this->assertTrue(
            str_contains($content, 'prefers-color-scheme')
            ||
            str_contains($content, 'matchMedia')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUN / MOON ICONS EXIST
    |--------------------------------------------------------------------------
    */

    public function testSunMoonIconsExist(): void
    {
        $content = $this->getContent();

        $this->assertTrue(
            str_contains($content, '🌞')
            ||
            str_contains($content, '🌙')
            ||
            str_contains($content, 'sun')
            ||
            str_contains($content, 'moon')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ALPINE JS EXISTS
    |--------------------------------------------------------------------------
    */

    public function testThemeScriptExists(): void
    {
        $this->assertFileExists($this->scriptPath);
    }

    /*
    |--------------------------------------------------------------------------
    | BOOTSTRAP EXISTS
    |--------------------------------------------------------------------------
    */

    public function testUsesBootstrap(): void
    {
        $content = file_get_contents($this->layoutPath);

        $this->assertStringContainsString(
            'bootstrap',
            strtolower($content)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VIEWPORT EXISTS
    |--------------------------------------------------------------------------
    */

    public function testViewportMetaTagExists(): void
    {
        $content = file_get_contents($this->layoutPath);

        $this->assertStringContainsString(
            'viewport',
            $content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE BOOTSTRAP CLASSES EXIST
    |--------------------------------------------------------------------------
    */

    public function testResponsiveBootstrapExists(): void
    {
        $content = $this->getContent();

        $this->assertTrue(
            str_contains($content, 'container')
            ||
            str_contains($content, 'container-fluid')
            ||
            str_contains($content, 'row')
            ||
            str_contains($content, 'col-')
            ||
            str_contains($content, 'd-flex')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NO TAILWIND
    |--------------------------------------------------------------------------
    */

    public function testDoesNotUseTailwind(): void
    {
        $content = $this->getContent();

        $tailwindClasses = [
            'dark:bg',
            'dark:text',
            'grid-cols',
            'flex-col',
            'bg-gray',
            'text-gray',
            'sm:',
            'md:',
            'lg:',
            'tailwindcss'
        ];

        foreach ($tailwindClasses as $class) {

            $this->assertFalse(
                str_contains($content, $class),
                "Tailwind class found: {$class}"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | THEME PERSISTENCE
    |--------------------------------------------------------------------------
    */

    public function testThemePersistenceExists(): void
    {
        $content = $this->getContent();

        $this->assertTrue(
            str_contains($content, 'setItem')
            &&
            str_contains($content, 'getItem')
        );
    }
}

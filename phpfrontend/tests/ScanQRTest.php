<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ScanQRTest extends TestCase
{
    private string $viewPath;
    private string $layoutPath;

    protected function setUp(): void
    {
        $this->viewPath = __DIR__ . '/../src/views/staff/scanqr.php';
        $this->layoutPath = __DIR__ . '/../src/views/layouts/app.php';
    }

    private function viewContent(): string
    {
        return file_get_contents($this->viewPath);
    }

    private function combinedContent(): string
    {
        return file_get_contents($this->viewPath)
            . file_get_contents($this->layoutPath);
    }

    public function testScanQrPageExists(): void
    {
        $this->assertFileExists($this->viewPath);
    }

    public function testScanQrPageHasTitle(): void
    {
        $content = strtolower($this->viewContent());

        $this->assertTrue(
            str_contains($content, 'scan qr') ||
            str_contains($content, 'qr scanner') ||
            str_contains($content, 'scan attendance')
        );
    }

    public function testQrScannerComponentExists(): void
    {
        $content = strtolower($this->combinedContent());

        $this->assertTrue(
            str_contains($content, 'qr') &&
            (
                str_contains($content, 'scanner') ||
                str_contains($content, 'html5-qrcode') ||
                str_contains($content, 'qr-reader') ||
                str_contains($content, 'camera')
            )
        );
    }

    public function testScanActionExists(): void
    {
        $content = strtolower($this->combinedContent());

        $this->assertTrue(
            str_contains($content, 'scan') &&
            (
                str_contains($content, '@click') ||
                str_contains($content, 'onclick') ||
                str_contains($content, 'startscan') ||
                str_contains($content, 'startscanner')
            )
        );
    }

    public function testDummyDataExists(): void
    {
        $content = strtolower($this->combinedContent());

        $this->assertTrue(
            str_contains($content, 'dummy') ||
            str_contains($content, 'mock') ||
            str_contains($content, 'demo') ||
            str_contains($content, 'test')
        );
    }

    public function testSuccessModalExists(): void
    {
        $content = strtolower($this->viewContent());

        $this->assertTrue(
            str_contains($content, 'modal') &&
            (
                str_contains($content, 'success') ||
                str_contains($content, 'clocked')
            )
        );
    }

    public function testModalShowsClockInStatus(): void
    {
        $content = strtolower($this->combinedContent());

        $this->assertTrue(
            str_contains($content, 'clock in') ||
            str_contains($content, 'clock-in') ||
            str_contains($content, 'clocked in')
        );
    }

    public function testModalShowsClockOutStatus(): void
    {
        $content = strtolower($this->combinedContent());

        $this->assertTrue(
            str_contains($content, 'clock out') ||
            str_contains($content, 'clock-out') ||
            str_contains($content, 'clocked out')
        );
    }

    public function testBootstrapModalIsUsed(): void
    {
        $content = $this->combinedContent();

        $this->assertTrue(
            str_contains($content, 'modal fade') ||
            str_contains($content, 'data-bs-toggle') ||
            str_contains($content, 'bootstrap.Modal')
        );
    }

    public function testUsesBootstrapAndCssOnly(): void
    {
        $content = $this->combinedContent();

        $this->assertTrue(
            str_contains($content, 'container') ||
            str_contains($content, 'row') ||
            str_contains($content, 'col-') ||
            str_contains($content, 'btn') ||
            str_contains($content, 'card')
        );
    }

    public function testUsesAlpineOrJavaScript(): void
    {
        $content = $this->combinedContent();

        $this->assertTrue(
            str_contains($content, 'x-data') ||
            str_contains($content, '@click') ||
            str_contains($content, '<script') ||
            str_contains($content, '.js')
        );
    }

    public function testResponsiveLayoutExists(): void
    {
        $content = $this->combinedContent();

        $this->assertTrue(
            str_contains($content, 'container-fluid') ||
            str_contains($content, 'row') ||
            str_contains($content, 'col-md') ||
            str_contains($content, 'col-lg') ||
            str_contains($content, 'img-fluid')
        );
    }

    public function testNoTailwindClassesUsed(): void
    {
        $content = $this->combinedContent();

        $tailwindClasses = [
            'bg-slate',
            'text-slate',
            'bg-gray',
            'text-gray',
            'grid-cols',
            'rounded-xl',
            'p-6',
            'md:',
            'lg:',
            'dark:'
        ];

        foreach ($tailwindClasses as $class) {
            $this->assertFalse(
                str_contains($content, $class),
                "Tailwind class found: {$class}"
            );
        }
    }

    public function testDefinitionOfDoneIsRepresented(): void
    {
        $content = strtolower($this->combinedContent());

        $hasLayout =
            str_contains($content, 'scan qr') ||
            str_contains($content, 'qr scanner');

        $hasScanner =
            str_contains($content, 'scanner') ||
            str_contains($content, 'qr-reader') ||
            str_contains($content, 'camera');

        $hasModal =
            str_contains($content, 'modal');

        $hasDummyFlow =
            str_contains($content, 'dummy') ||
            str_contains($content, 'mock') ||
            str_contains($content, 'demo');

        $this->assertTrue($hasLayout);
        $this->assertTrue($hasScanner);
        $this->assertTrue($hasModal);
        $this->assertTrue($hasDummyFlow);
    }
}
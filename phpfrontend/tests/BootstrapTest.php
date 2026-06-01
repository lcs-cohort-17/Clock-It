<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BootstrapTest extends TestCase
{
    public function testQrViewFunctionsAreLoaded(): void
    {
        self::assertTrue(function_exists('qr_generate'));
        self::assertTrue(function_exists('render_create_qr_form'));
        self::assertTrue(function_exists('render_qr_dropdown_button'));
        self::assertTrue(function_exists('render_qr_code_list_item'));
        self::assertTrue(function_exists('render_qr_modal'));
        self::assertTrue(function_exists('render_qr_generator_layout'));
    }
}

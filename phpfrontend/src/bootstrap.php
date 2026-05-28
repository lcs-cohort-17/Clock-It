<?php

declare(strict_types=1);

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists($composerAutoload)) {
    require $composerAutoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'ClockIt\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        $path = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($path)) {
            require $path;
        }
    });
}

require_once __DIR__ . '/helpers.php';

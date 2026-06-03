<?php
$docRoot = __DIR__ . '/src/public';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = $docRoot . $uri;

if ($uri !== '/router.php' && file_exists($file)) {
    return false;
}

require $docRoot . '/index.php';

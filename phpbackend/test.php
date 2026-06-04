<?php
require_once __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/src/config/Google.php';
echo $config['credentials_path'] . "\n";
echo file_exists($config['credentials_path']) ? 'FOUND' : 'NOT FOUND';
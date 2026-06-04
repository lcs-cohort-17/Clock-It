<?php
// test-clock.php
session_start();

$_SESSION['user_id'] = '22734395-5e8d-11f1-896c-00e16e76e3e8';  // Active Admin User

echo "✅ Session set for user: " . $_SESSION['user_id'] . "<br><br>";
echo "🔄 Calling Clock Endpoint...<br><br>";

$_SERVER['REQUEST_URI'] = '/api/attendance/clock';
$_SERVER['REQUEST_METHOD'] = 'POST';

require_once __DIR__ . '/index.php';
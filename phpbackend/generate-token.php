<?php

//creates json web tokens for testing
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * PATH FIX: Resolves the exact .env path context.
 * If this script sits in your main project root directory alongside index.php, use __DIR__.
 * If this script sits in a sub-folder (like /bin or /scripts), keep the fallback directory depth.
 */
$envPath = __DIR__;
if (!file_exists($envPath . '/.env') && file_exists(__DIR__ . '/../.. .env')) {
    $envPath = __DIR__ . '/../..';
}

$dotenv = Dotenv::createImmutable($envPath);
$dotenv->load();

$secret = $_ENV['JWT_SECRET'] ?? null;

if (!$secret) {
    echo "❌ JWT_SECRET not found in .env\n";
    exit(1);
}

if ($argc !== 5) {
    echo "❌ Wrong number of arguments.\n\n";
    echo "Usage:\n";
    echo " php generate-token.php <userId> <email> <role> <employee_id>\n\n";
    echo "Example:\n";
    echo " php generate-token.php \"78cbf754-5d9b-11f1-896c-001e676e63e8\" \"tommy@lifechoices.com\" \"staff\" \"S-001\"\n";
    exit(1);
}

$userId = $argv[1];
$email = $argv[2];
$role = $argv[3];
$employeeId = $argv[4];

// ---
// Validate role — must match what your auth middleware expects
// ---
if (!in_array($role, ['admin', 'staff'])) {
    echo "❌ Invalid role '{$role}'. Must be 'admin' or 'staff'.\n";
    exit(1);
}

$header = base64url_encode(json_encode([
    'typ' => 'JWT',
    'alg' => 'HS256',
]));

$now = time();
$expires = $now + (60 * 60 * 2); // 2 hours from now

$payload = base64url_encode(json_encode([
    'user_id'      => $userId,
    'email'        => $email,
    'role'         => $role,
    'employee_id'  => $employeeId,
    'iat'          => $now,
    'exp'          => $expires,
]));

$signature = base64url_encode(
    hash_hmac('sha256', "{$header}.{$payload}", $secret, true)
);

// Combine all three parts into the final JWT
$token = "{$header}.{$payload}.{$signature}";

// ---
// OUTPUT
// Prints the token with the Bearer prefix ready to paste into Thunder Client
// ---
echo "\n✅ Token generated successfully\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Bearer {$token}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\nDetails:\n";
echo " user_id: {$userId}\n";
echo " email: {$email}\n";
echo " role: {$role}\n";
echo " employee_id: {$employeeId}\n";
echo " issued: " . date('Y-m-d H:i:s', $now) . "\n";
echo " expires: " . date('Y-m-d H:i:s', $expires) . "\n\n";

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

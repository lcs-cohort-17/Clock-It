<?php

function fetch_users_from_api(): array
{
    $token = $_SESSION['auth_token'] ?? null;
    
    if (!$token) {
        return [];
    }
    
    $apiUrl = 'http://localhost:8000/api/admin/users';
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return [];
    }
    
    $data = json_decode($response, true);
    
    return $data['data'] ?? [];
}

function transform_user(array $apiUser): array
{
    return [
        'id' => $apiUser['user_id'] ?? '',
        'name' => trim(($apiUser['first_name'] ?? '') . ' ' . ($apiUser['last_name'] ?? '')),
        'email' => $apiUser['email'] ?? '',
        'employeeId' => $apiUser['employee_id'] ?? '',
        'role' => ucfirst($apiUser['role'] ?? 'staff'),
        'status' => ($apiUser['is_active'] ?? 1) == 1 ? 'Active' : 'Inactive',
    ];
}

function initials(string $name): string
{
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper($part[0]);
        }
    }
    return $initials ?: '?';
}

// RENAMED: was get_current_user(), now get_logged_in_user()
function get_logged_in_user(): array
{
    // First try session
    $sessionUser = $_SESSION['current_user'] ?? null;
    
    if ($sessionUser && is_array($sessionUser)) {
        return [
            'name' => trim(($sessionUser['first_name'] ?? '') . ' ' . ($sessionUser['last_name'] ?? '')),
            'email' => $sessionUser['email'] ?? '',
            'role' => $sessionUser['role'] ?? 'staff',
            'employee_id' => $sessionUser['employee_id'] ?? '',
        ];
    }
    
    // Try to get from API using token from localStorage is not possible in PHP
    // Just return a placeholder - the sidebar will be updated by Alpine later
    
    return [
        'name' => 'Admin User',
        'email' => 'admin@clockit.com',
        'role' => 'admin',
        'employee_id' => '',
    ];
}
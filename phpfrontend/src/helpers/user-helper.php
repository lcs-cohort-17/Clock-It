<?php

function initials($name)
{
    $parts = explode(" ", trim($name));

    $initials = "";

    foreach ($parts as $part) {
        $initials .= strtoupper($part[0]);
    }

    return substr($initials, 0, 2);
}

function generateEmployeeId($role, $users)
{
    $prefix = $role === "Admin" ? "A" : "S";

    $existing = array_filter(
        $users,
        fn($user) => $user['role'] === $role
    );

    $baseNumber = $role === "Admin"
        ? 1
        : 101;

    return $prefix . "-" . str_pad(
        $baseNumber + count($existing),
        3,
        "0",
        STR_PAD_LEFT
    );
}

function generatePassword($length = 10)
{
    $chars =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    $password = "";

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }

    return $password;
}
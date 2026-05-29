<?php
// password_logic.php

function validatePasswordChange($currentPassword, $newPassword, $confirmPassword) {

    $hasUpperCase   = preg_match('/[A-Z]/', $newPassword);
    $hasLowerCase   = preg_match('/[a-z]/', $newPassword);
    $hasNumber      = preg_match('/[0-9]/', $newPassword);
    $hasSpecialChar = preg_match('/[^A-Za-z0-9]/', $newPassword);
    $hasMinLength   = strlen($newPassword) >= 8;

    // Empty fields
    if (
        empty($currentPassword) ||
        empty($newPassword) ||
        empty($confirmPassword)
    ) {
        return [
            'success' => false,
            'error' => 'Please fill in all fields'
        ];
    }

    // Password strength
    if (
        !$hasUpperCase ||
        !$hasLowerCase ||
        !$hasNumber ||
        !$hasSpecialChar ||
        !$hasMinLength
    ) {
        return [
            'success' => false,
            'error' => 'Password must include uppercase, lowercase, number, special character and minimum 8 characters'
        ];
    }

    // Password match
    if ($newPassword !== $confirmPassword) {
        return [
            'success' => false,
            'error' => 'Passwords do not match'
        ];
    }

    // Old password cannot equal new password
    if ($currentPassword === $newPassword) {
        return [
            'success' => false,
            'error' => 'New password cannot be the same as old password'
        ];
    }

    // Success
    return [
        'success' => true,
        'message' => 'Password updated successfully'
    ];
}
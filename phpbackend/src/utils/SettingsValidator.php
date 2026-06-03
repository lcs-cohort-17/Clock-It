<?php
 
class SettingsValidator
{
    private static array $allowedKeys = [
        'session_timeout_minutes',
        'data_retention_days',
    ];
 
    public static function validateUpdate(array $body): array
    {
        $errors = [];
 
        $hasAllowed = false;
        foreach (self::$allowedKeys as $key) {
            if (!array_key_exists($key, $body)) {
                continue;
            }
 
            $hasAllowed = true;
            $value = $body[$key];
 
            if (!is_numeric($value)) {
                $errors[$key] = "$key must be a number";
                continue;
            }
 
            if ((int) $value <= 0) {
                $errors[$key] = "$key must be a positive integer greater than zero";
            }
        }
 
        if (!$hasAllowed) {
            $errors['body'] = 'At least one of: ' . implode(', ', self::$allowedKeys) . ' is required';
        }
 
        return $errors;
    }
 
    public static function validatePurge(array $body): array
    {
        $errors = [];
 
        // confirm must be present and exactly true (boolean)
        if (!array_key_exists('confirm', $body) || $body['confirm'] !== true) {
            $errors['confirm'] = 'confirm must be true';
        }
 
        // days is optional but if provided must be a positive integer
        if (array_key_exists('days', $body)) {
            $days = $body['days'];
 
            if (!is_numeric($days)) {
                $errors['days'] = 'days must be a number';
            } elseif ((int) $days <= 0) {
                $errors['days'] = 'days must be a positive integer greater than zero';
            }
        }
 
        return $errors;
    }
}
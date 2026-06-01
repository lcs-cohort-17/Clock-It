<?php

class SyncFrequencyValidator
{
    public static function isValid(string $frequency): bool
    {
        // TDD: This will fail the tests - implement validation logic
        // Should accept: 15m, 1h, 24h
        // Should reject: negative values, random strings
        return false;
    }
}

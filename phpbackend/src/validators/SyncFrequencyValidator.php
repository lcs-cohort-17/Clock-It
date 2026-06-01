<?php
// src/validators/SyncFrequencyValidator.php

class SyncFrequencyValidator
{
    private const VALID_FORMATS = [
        '15m', '30m', '1h', '2h', '6h', '12h', '24h'
    ];

    public static function isValid(string $frequency): bool
    {
        return in_array($frequency, self::VALID_FORMATS, true);
    }
}
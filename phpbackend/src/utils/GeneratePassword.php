<?php

namespace App\Utils;

class GeneratePassword
{
    /**
     * Generate a random password of 8 characters
     */
    public static function generate(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $password = '';
        $charsLength = strlen($chars);
        
        for ($i = 0; $i < 8; $i++) {
            $password .= $chars[rand(0, $charsLength - 1)];
        }
        
        return $password;
    }
}

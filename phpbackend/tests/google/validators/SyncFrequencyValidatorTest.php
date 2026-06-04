<?php

use PHPUnit\Framework\TestCase;

class SyncFrequencyValidatorTest extends TestCase
{
    public function testAccepts15Minutes(): void
    {
        $this->assertTrue(
            SyncFrequencyValidator::isValid('15m')
        );
    }

    public function testAccepts1Hour(): void
    {
        $this->assertTrue(
            SyncFrequencyValidator::isValid('1h')
        );
    }

    public function testAccepts24Hours(): void
    {
        $this->assertTrue(
            SyncFrequencyValidator::isValid('24h')
        );
    }

    public function testRejectsRandomString(): void
    {
        $this->assertFalse(
            SyncFrequencyValidator::isValid('banana')
        );
    }

    public function testRejectsNegativeValues(): void
    {
        $this->assertFalse(
            SyncFrequencyValidator::isValid('-5m')
        );
    }
}
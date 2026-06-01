<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/views/staff/password/password_logic.php';

class PasswordTest extends TestCase
{
    public function testShowsErrorWhenFieldsAreEmpty()
    {
        $result = validatePasswordChange('', '', '');

        $this->assertFalse($result['success']);

        $this->assertStringContainsString(
            'Please fill in all fields',
            $result['error']
        );
    }

    public function testShowsErrorWhenPasswordIsWeak()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'abc',
            'abc'
        );

        $this->assertFalse($result['success']);

        $this->assertStringContainsString(
            'Password must include uppercase',
            $result['error']
        );
    }

    public function testShowsErrorWhenPasswordsDoNotMatch()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'NewPass123!',
            'WrongPass123!'
        );

        $this->assertFalse($result['success']);

        $this->assertStringContainsString(
            'Passwords do not match',
            $result['error']
        );
    }

    public function testShowsSuccessMessageForValidPasswordUpdate()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'NewPass123!',
            'NewPass123!'
        );

        $this->assertTrue($result['success']);

        $this->assertStringContainsString(
            'Password updated successfully',
            $result['message']
        );
    }

    public function testPasswordWithoutUppercaseFails()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'newpass123!',
            'newpass123!'
        );

        $this->assertFalse($result['success']);
    }

    public function testPasswordWithoutNumberFails()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'NewPassword!',
            'NewPassword!'
        );

        $this->assertFalse($result['success']);
    }

    public function testPasswordWithoutSpecialCharacterFails()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'NewPass123',
            'NewPass123'
        );

        $this->assertFalse($result['success']);
    }

    public function testPasswordTooShortFails()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'Ab1!',
            'Ab1!'
        );

        $this->assertFalse($result['success']);
    }

    public function testOldAndNewPasswordCannotBeSame()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'OldPass123!',
            'OldPass123!'
        );

        $this->assertFalse($result['success']);
    }
}

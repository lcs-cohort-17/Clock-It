<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/views/staff/password/password_logic.php';

class PasswordLogicTest extends TestCase
{
    public function testReturnsErrorWhenFieldsAreEmpty()
    {
        $result = validatePasswordChange('', '', '');

        $this->assertFalse($result['success']);
        $this->assertEquals(
            'Please fill in all fields',
            $result['error']
        );
    }

    public function testReturnsErrorWhenPasswordIsWeak()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'abc',
            'abc'
        );

        $this->assertFalse($result['success']);

        $this->assertEquals(
            'Password must include uppercase, lowercase, number, special character and minimum 8 characters',
            $result['error']
        );
    }

    public function testReturnsErrorWhenPasswordsDoNotMatch()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'NewPass123!',
            'WrongPass123!'
        );

        $this->assertFalse($result['success']);

        $this->assertEquals(
            'Passwords do not match',
            $result['error']
        );
    }

    public function testReturnsErrorWhenOldAndNewPasswordsAreSame()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'OldPass123!',
            'OldPass123!'
        );

        $this->assertFalse($result['success']);

        $this->assertEquals(
            'New password cannot be the same as old password',
            $result['error']
        );
    }

    public function testReturnsSuccessForValidPasswordChange()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'NewPass123!',
            'NewPass123!'
        );

        $this->assertTrue($result['success']);

        $this->assertEquals(
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

    public function testPasswordWithoutLowercaseFails()
    {
        $result = validatePasswordChange(
            'OldPass123!',
            'NEWPASS123!',
            'NEWPASS123!'
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
}
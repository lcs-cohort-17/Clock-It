<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Profile/ProfileService.php';

class ProfileServiceTest extends TestCase
{
    private $service;

    protected function setUp(): void
    {
        $this->service = new ProfileService();
    }

    public function testFlashMessageIsCleared()
    {
        $session = ['flash' => 'Profile updated'];

        $result = $this->service->handleFlash($session);

        $this->assertEquals('Profile updated', $result);
        $this->assertArrayNotHasKey('flash', $session);
    }

    public function testPasswordUpdateSuccess()
    {
        $result = $this->service->updatePassword(
            'OldPass1!',
            'NewStrongPass1!',
            'NewStrongPass1!'
        );

        $this->assertTrue($result['success']);
    }

    public function testPasswordUpdateFailure()
    {
        $result = $this->service->updatePassword(
            '',
            '',
            ''
        );

        $this->assertFalse($result['success']);
    }
}
<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Controllers\ProfileController;
use Models\Profile;
use Types\ApiResponse;

class ProfileControllerTest extends TestCase
{
    private MockObject $modelMock;
    private ProfileController $controller;

    protected function setUp(): void
    {
        $this->modelMock  = $this->createMock(Profile::class);
        $this->controller = new ProfileController($this->modelMock);
    }

    // ─── Helper: capture JSON output ─────────────────────────────
    private function capture(callable $fn): array
    {
        ob_start();
        $fn();
        $output = ob_get_clean();
        return json_decode($output, true) ?? [];
    }

    // ─── GET ALL ─────────────────────────────────────────────────
    public function test_getProfiles_returns_200_with_all_profiles(): void
    {
        $this->modelMock->method('getProfiles')->willReturn(ApiResponse::ok([
            ['id' => '1', 'first_name' => 'Joshua', 'employee_id' => 'S-005', 'role' => 'staff', 'is_active' => 1, 'email' => 'j@gmail.com', 'password' => 'hashed'],
            ['id' => '2', 'first_name' => 'Sarah',  'employee_id' => 'A-010', 'role' => 'admin', 'is_active' => 1, 'email' => 's@gmail.com', 'password' => 'hashed'],
        ]));

        $body = $this->capture(fn () => $this->controller->getProfiles());

        $this->assertTrue($body['success']);
        $this->assertCount(2, $body['data']);
    }

    public function test_getProfiles_returns_400_on_model_failure(): void
    {
        $this->modelMock->method('getProfiles')->willReturn(ApiResponse::fail('Database error'));

        $body = $this->capture(fn () => $this->controller->getProfiles());

        $this->assertFalse($body['success']);
    }

    // ─── CREATE ──────────────────────────────────────────────────
    public function test_createProfile_returns_201_with_plain_password(): void
    {
        $this->modelMock->method('createProfile')->willReturn(ApiResponse::ok([
            'id' => '1', 'first_name' => 'Joshua', 'last_name' => 'Jacobs',
            'employee_id' => 'S-005', 'role' => 'staff', 'is_active' => 1,
            'email' => 'j@gmail.com', 'password' => 'Xk9mP2qR'
        ]));

        $body = $this->capture(fn () => $this->controller->createProfile([
            'first_name' => 'Joshua', 'last_name' => 'Jacobs',
            'employee_id' => 'S-005', 'role' => 'staff', 'email' => 'j@gmail.com'
        ]));

        $this->assertTrue($body['success']);
        $this->assertEquals(8, strlen($body['data']['password']));
        $this->assertDoesNotMatchRegularExpression('/^\$2[ay]\$/', $body['data']['password']);
        $this->assertEquals('Joshua', $body['data']['first_name']);
    }

    public function test_createProfile_returns_400_when_fields_missing(): void
    {
        $body = $this->capture(fn () => $this->controller->createProfile(['first_name' => 'Joshua']));

        $this->assertFalse($body['success']);
        $this->assertEquals('All fields are required', $body['error']);
    }

    public function test_createProfile_returns_400_on_model_error(): void
    {
        $this->modelMock->method('createProfile')
            ->willReturn(ApiResponse::fail('Staff employee_id must start with S-'));

        $body = $this->capture(fn () => $this->controller->createProfile([
            'first_name' => 'Joshua', 'last_name' => 'Jacobs',
            'employee_id' => 'A-005', 'role' => 'staff', 'email' => 'j@gmail.com'
        ]));

        $this->assertFalse($body['success']);
        $this->assertEquals('Staff employee_id must start with S-', $body['error']);
    }

    // ─── LOGIN ───────────────────────────────────────────────────
    public function test_loginProfile_returns_200_with_token_on_success(): void
    {
        $hashedPassword = password_hash('Xk9mP2qR', PASSWORD_BCRYPT);

        $this->modelMock->method('loginProfile')->willReturn(ApiResponse::ok([
            'id' => '1', 'first_name' => 'joshua', 'last_name' => 'Jacobs',
            'employee_id' => 'S-005', 'role' => 'staff', 'is_active' => 1,
            'email' => 'j@gmail.com', 'password' => $hashedPassword
        ]));

        $this->modelMock->method('getProfileById')->willReturn(ApiResponse::ok([
            'id' => '1', 'first_name' => 'joshua', 'employee_id' => 'S-005',
            'email' => 'j@gmail.com', 'role' => 'staff', 'is_active' => 1
        ]));

        $_ENV['JWT_SECRET'] = 'test-secret';

        $body = $this->capture(fn () => $this->controller->loginProfile([
            'email' => 'j@gmail.com', 'password' => 'Xk9mP2qR'
        ]));

        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('token', $body);
        $this->assertEquals('Joshua', $body['first_name']); // capitalized
        $this->assertArrayNotHasKey('password', $body['user']);
    }

    public function test_loginProfile_returns_401_on_wrong_password(): void
    {
        $this->modelMock->method('loginProfile')->willReturn(ApiResponse::ok([
            'id' => '1', 'email' => 'j@gmail.com', 'is_active' => 1,
            'password' => password_hash('correctpassword', PASSWORD_BCRYPT),
            'role' => 'staff', 'first_name' => 'Joshua', 'last_name' => 'Jacobs', 'employee_id' => 'S-005'
        ]));

        $body = $this->capture(fn () => $this->controller->loginProfile([
            'email' => 'j@gmail.com', 'password' => 'wrongpassword'
        ]));

        $this->assertFalse($body['success']);
        $this->assertEquals('Invalid email or password', $body['error']);
    }

    public function test_loginProfile_returns_401_when_email_not_found(): void
    {
        $this->modelMock->method('loginProfile')->willReturn(ApiResponse::fail('User not found'));

        $body = $this->capture(fn () => $this->controller->loginProfile([
            'email' => 'ghost@gmail.com', 'password' => 'anything'
        ]));

        $this->assertFalse($body['success']);
        $this->assertEquals('Invalid email or password', $body['error']);
    }

    public function test_loginProfile_returns_400_when_fields_missing(): void
    {
        $body = $this->capture(fn () => $this->controller->loginProfile(['email' => 'j@gmail.com']));

        $this->assertFalse($body['success']);
        $this->assertEquals('Email and password are required', $body['error']);
    }

    // ─── UPDATE ──────────────────────────────────────────────────
    public function test_updateProfile_returns_200_on_success(): void
    {
        $this->modelMock->method('updateProfile')->willReturn(ApiResponse::ok([
            'id' => '1', 'first_name' => 'Siza', 'last_name' => 'Mpafa',
            'employee_id' => 'S-007', 'role' => 'staff', 'is_active' => 1, 'email' => 'siza@gmail.com'
        ]));

        $body = $this->capture(fn () => $this->controller->updateProfile('S-007', ['first_name' => 'Siza']));

        $this->assertTrue($body['success']);
    }

    public function test_updateProfile_returns_400_on_model_failure(): void
    {
        $this->modelMock->method('updateProfile')->willReturn(ApiResponse::fail('Update failed'));

        $body = $this->capture(fn () => $this->controller->updateProfile('S-007', ['first_name' => 'Ghost']));

        $this->assertFalse($body['success']);
        $this->assertEquals('Update failed', $body['error']);
    }

    // ─── DELETE ──────────────────────────────────────────────────
    public function test_deleteProfile_returns_200_on_soft_delete(): void
    {
        $this->modelMock->method('deleteProfile')
            ->willReturn(new ApiResponse(true, null, null, 'profile deleted successfully'));

        $body = $this->capture(fn () => $this->controller->deleteProfile('S-007'));

        $this->assertTrue($body['success']);
        $this->assertEquals('profile deleted successfully', $body['message']);
    }

    public function test_deleteProfile_returns_400_on_failure(): void
    {
        $this->modelMock->method('deleteProfile')->willReturn(ApiResponse::fail('Delete failed'));

        $body = $this->capture(fn () => $this->controller->deleteProfile('S-007'));

        $this->assertFalse($body['success']);
        $this->assertEquals('Delete failed', $body['error']);
    }

    // ─── RESET PASSWORD ──────────────────────────────────────────
    public function test_resetPassword_returns_200_with_plain_text_password(): void
    {
        $this->modelMock->method('resetPassword')->willReturn(ApiResponse::ok([
            'id' => '1', 'employee_id' => 'S-007', 'password' => 'Nq7rT2mX',
            'first_name' => 'Siza', 'last_name' => 'Mpafa', 'role' => 'staff', 'is_active' => 1, 'email' => 'siza@gmail.com'
        ]));

        $body = $this->capture(fn () => $this->controller->resetPassword('S-007'));

        $this->assertTrue($body['success']);
        $this->assertEquals(8, strlen($body['data']['password']));
    }

    public function test_resetPassword_returns_400_on_failure(): void
    {
        $this->modelMock->method('resetPassword')->willReturn(ApiResponse::fail('Reset failed'));

        $body = $this->capture(fn () => $this->controller->resetPassword('S-007'));

        $this->assertFalse($body['success']);
        $this->assertEquals('Reset failed', $body['error']);
    }

    // ─── UPDATE PASSWORD ─────────────────────────────────────────
    public function test_updatePassword_returns_200_on_success(): void
    {
        $hashedOld = password_hash('IUsW0l4r', PASSWORD_BCRYPT);

        $this->modelMock->method('loginProfile')->willReturn(ApiResponse::ok([
            'id' => '1', 'employee_id' => 'S-300', 'password' => $hashedOld,
            'email' => 'staff@clockit.com', 'role' => 'staff', 'is_active' => 1,
            'first_name' => 'Official', 'last_name' => 'Staff'
        ]));

        $this->modelMock->method('updatePassword')->willReturn(ApiResponse::ok([
            'id' => '1', 'employee_id' => 'S-300', 'password' => '$2y$10$newhashedpassword'
        ]));

        $body = $this->capture(fn () => $this->controller->updatePassword(
            'S-300',
            ['oldPassword' => 'IUsW0l4r', 'newPassword' => 'newsecurepass'],
            ['email' => 'staff@clockit.com']
        ));

        $this->assertTrue($body['success']);
        $this->assertEquals('Password updated successfully', $body['message']);
    }

    public function test_updatePassword_returns_400_when_fields_missing(): void
    {
        $body = $this->capture(fn () => $this->controller->updatePassword(
            'S-300',
            ['oldPassword' => 'IUsW0l4r'],
            ['email' => 'staff@clockit.com']
        ));

        $this->assertFalse($body['success']);
        $this->assertEquals('Old password and new password are required', $body['error']);
    }

    public function test_updatePassword_returns_401_when_old_password_wrong(): void
    {
        $this->modelMock->method('loginProfile')->willReturn(ApiResponse::ok([
            'id' => '1', 'employee_id' => 'S-300', 'password' => password_hash('correctpass', PASSWORD_BCRYPT),
            'email' => 'staff@clockit.com', 'role' => 'staff', 'is_active' => 1,
            'first_name' => 'Official', 'last_name' => 'Staff'
        ]));

        $body = $this->capture(fn () => $this->controller->updatePassword(
            'S-300',
            ['oldPassword' => 'wrongpassword', 'newPassword' => 'newpass'],
            ['email' => 'staff@clockit.com']
        ));

        $this->assertFalse($body['success']);
        $this->assertEquals('Old password is incorrect', $body['error']);
    }

    public function test_updatePassword_returns_404_when_user_not_found(): void
    {
        $this->modelMock->method('loginProfile')->willReturn(ApiResponse::fail('User not found'));

        $body = $this->capture(fn () => $this->controller->updatePassword(
            'X-999',
            ['oldPassword' => 'anything', 'newPassword' => 'newpass'],
            ['email' => 'ghost@clockit.com']
        ));

        $this->assertFalse($body['success']);
        $this->assertEquals('User not found', $body['error']);
    }

    // ─── GET BY ID ───────────────────────────────────────────────
    public function test_getProfileById_returns_200_without_password(): void
    {
        $this->modelMock->method('getProfileById')->willReturn(ApiResponse::ok([
            'id' => '1', 'first_name' => 'Sarah', 'last_name' => 'Johnson',
            'employee_id' => 'S-006', 'email' => 'sarah@company.com',
            'role' => 'staff', 'is_active' => 1, 'password' => 'hashed'
        ]));

        $body = $this->capture(fn () => $this->controller->getProfileById('S-006'));

        $this->assertTrue($body['success']);
        $this->assertEquals('Sarah', $body['data']['first_name']);
        $this->assertArrayNotHasKey('password', $body['data']);
    }

    public function test_getProfileById_returns_400_when_not_found(): void
    {
        $this->modelMock->method('getProfileById')->willReturn(ApiResponse::fail('Profile not found'));

        $body = $this->capture(fn () => $this->controller->getProfileById('X-999'));

        $this->assertFalse($body['success']);
        $this->assertEquals('Profile not found', $body['error']);
    }
  
   public function testClearCacheReturns200ForLoggedInUser(): void
    {
        $mockResponse = [
            'success' => true,
            'message' => 'Cache cleared successfully for S-005'
        ];

        $this->profileDbMock->expects($this->once())
            ->method('clearCache')
            ->with('S-005')
            ->willReturn($mockResponse);

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())
            ->method('getAttribute')
            ->with('user')
            ->willReturn(['employee_id' => 'S-005']);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('withStatus')
            ->with(200)
            ->willReturnSelf();

        $responseMock->expects($this->once())
            ->method('withJson')
            ->with($mockResponse)
            ->willReturn($responseMock);

        $result = $this->controller->clearCache($requestMock, $responseMock);

        $this->assertNotNull($result);
    }

    public function testClearCacheReturns401WhenUserNotLoggedIn(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->expects($this->once())
            ->method('getAttribute')
            ->with('user')
            ->willReturn(null);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('withStatus')
            ->with(401)
            ->willReturnSelf();

        $responseMock->expects($this->once())
            ->method('withJson')
            ->with([
                'success' => false,
                'error' => 'User must be logged in to clear cache'
            ])
            ->willReturn($responseMock);

        $result = $this->controller->clearCache($requestMock, $responseMock);

        $this->assertNotNull($result);
    }
}

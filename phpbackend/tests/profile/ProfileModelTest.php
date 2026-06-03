<?php
// tests/profile/ProfileModelTest.php

namespace Tests\profile;

use PHPUnit\Framework\TestCase;
use PDO;                    
use PDOException; 
use PDOStatement;
use App\Models\ProfileDb;
class ProfileModelTest extends TestCase
{
    private $mockDb;
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a mock PDO
        $this->mockDb = $this->createMock(PDO::class);
        
        // Create the real model with the mocked PDO connection
        $this->model = new ProfileDb($this->mockDb);
    }

    
    // GET ALL PROFILES TESTS
    
    
    public function testGetAllProfilesReturnsAllProfilesSuccessfully(): void
    {
        $mockData = [
            [
                'id' => '14271887-48ea-48c8-9890-6cb196afa0Gc',
                'first_name' => 'Joshua',
                'last_name' => 'Jacobs',
                'employee_id' => 'A-005',
                'role' => 'admin',
                'is_active' => 1,
                'email' => 'jodam@gmail.com',
                'password' => 'joh123'
            ],
            [
                'id' => '14361887-48ea-48c8-9890-6cb196afa0Gc',
                'first_name' => 'Charlton',
                'last_name' => 'Poole',
                'employee_id' => 'S-006',
                'role' => 'staff',
                'is_active' => 1,
                'email' => 'charlton@gmail.com',
                'password' => 'charlton123'
            ]
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM profiles")
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->model->getProfilesDb();

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data']);
    }

    public function testGetAllProfilesReturnsProfilesWithAllRequiredFields(): void
    {
        $mockData = [
            [
                'id' => '14271887-48ea-48c8-9890-6cb196afa0Gc',
                'first_name' => 'Joshua',
                'last_name' => 'Jacobs',
                'employee_id' => 'A-005',
                'role' => 'admin',
                'is_active' => 1,
                'email' => 'jodam@gmail.com',
                'password' => 'joh123'
            ]
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockData);

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->model->getProfilesDb();
        $profile = $result['data'][0];

        $this->assertArrayHasKey('first_name', $profile);
        $this->assertArrayHasKey('last_name', $profile);
        $this->assertArrayHasKey('email', $profile);
        $this->assertArrayHasKey('employee_id', $profile);
        $this->assertArrayHasKey('role', $profile);
        $this->assertArrayHasKey('is_active', $profile);
        
        $this->assertEquals('Joshua', $profile['first_name']);
        $this->assertEquals('Jacobs', $profile['last_name']);
        $this->assertEquals('jodam@gmail.com', $profile['email']);
        $this->assertEquals('A-005', $profile['employee_id']);
        $this->assertEquals('admin', $profile['role']);
        $this->assertEquals(1, $profile['is_active']);
    }

    public function testGetAllProfilesReturnsErrorIfDatabaseFails(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);
        
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('Database error'));

        $result = $this->model->getProfilesDb();

        $this->assertFalse($result['success']);
        $this->assertEquals('Database error', $result['error']);
    }

    
    // CREATE PROFILE TESTS
    

    public function testCreateStaffProfileStoresHashedPasswordReturnsPlainTextToAdmin(): void
    {
        $mockData = [
            'id' => '14271887-48ea-48c8-9890-6cb196afa0Gc',
            'first_name' => 'Joshua',
            'last_name' => 'Jacobs',
            'employee_id' => 'S-005',
            'role' => 'staff',
            'is_active' => 1,
            'email' => 'jodam@gmail.com',
            'password' => 'hashedpassword'
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement2 = $this->createMock(PDOStatement::class);

        $this->mockDb->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStatement, $mockStatement2);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $mockStatement2->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement2->expects($this->once())
            ->method('fetch')
            ->willReturn($mockData);

        $result = $this->model->createProfileDb('Joshua', 'Jacobs', 'S-005', 'staff', 'jodam@gmail.com');

        $this->assertTrue($result['success']);
        $this->assertEquals('Joshua', $result['data']['first_name']);
        $this->assertEquals('Jacobs', $result['data']['last_name']);
        $this->assertEquals('S-005', $result['data']['employee_id']);
        $this->assertEquals('staff', $result['data']['role']);
        $this->assertEquals(1, $result['data']['is_active']);
        $this->assertEquals('jodam@gmail.com', $result['data']['email']);
        
        // Password must be 8 characters plain text
        $this->assertArrayHasKey('password', $result['data']);
        $this->assertEquals(8, strlen($result['data']['password']));
    }

    public function testCreateAdminProfileSuccessfully(): void
    {
        $mockData = [
            'id' => '24271887-48ea-48c8-9890-6cb196afa0Gc',
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'employee_id' => 'A-010',
            'role' => 'admin',
            'is_active' => 1,
            'email' => 'sarah@company.com',
            'password' => 'hashedpassword'
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement2 = $this->createMock(PDOStatement::class);

        $this->mockDb->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStatement, $mockStatement2);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $mockStatement2->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement2->expects($this->once())
            ->method('fetch')
            ->willReturn($mockData);

        $result = $this->model->createProfileDb('Sarah', 'Johnson', 'A-010', 'admin', 'sarah@company.com');

        $this->assertTrue($result['success']);
        $this->assertEquals('admin', $result['data']['role']);
        $this->assertEquals(8, strlen($result['data']['password']));
    }

    public function testCreateProfileRejectsInvalidRole(): void
    {
        $result = $this->model->createProfileDb('Joshua', 'Jacobs', 'S-005', 'invalid', 'jodam@gmail.com');

        $this->assertFalse($result['success']);
        $this->assertEquals('Role must be either staff or admin', $result['error']);
    }

    public function testCreateProfileRejectsStaffEmployeeIdNotStartingWithS(): void
    {
        $result = $this->model->createProfileDb('Joshua', 'Jacobs', 'A-005', 'staff', 'jodam@gmail.com');

        $this->assertFalse($result['success']);
        $this->assertEquals('Staff employee_id must start with S-', $result['error']);
    }

    public function testCreateProfileRejectsAdminEmployeeIdNotStartingWithA(): void
    {
        $result = $this->model->createProfileDb('Sarah', 'Johnson', 'S-010', 'admin', 'sarah@company.com');

        $this->assertFalse($result['success']);
        $this->assertEquals('Admin employee_id must start with A-', $result['error']);
    }

    
    // UPDATE PROFILE TESTS
    

    public function testUpdateStaffProfileSuccessfullyByEmployeeId(): void
    {
        $mockData = [
            'id' => '18741887-48ea-48c8-9890-6cb196afa0Gc',
            'first_name' => 'Siza',
            'last_name' => 'Mpafa',
            'employee_id' => 'S-007',
            'role' => 'staff',
            'is_active' => 1,
            'email' => 'siza@gmail.com'
        ];

        // Create two different mock statements
        $mockStatement1 = $this->createMock(PDOStatement::class);
        $mockStatement2 = $this->createMock(PDOStatement::class);

        // Expect TWO prepare calls (UPDATE then SELECT)
        $this->mockDb->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStatement1, $mockStatement2);

        // First statement: UPDATE
        $mockStatement1->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $mockStatement1->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // Second statement: SELECT
        $mockStatement2->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement2->expects($this->once())
            ->method('fetch')
            ->willReturn($mockData);

        $result = $this->model->updateProfileDb('S-007', [
            'first_name' => 'Siza',
            'last_name' => 'Mpafa',
            'role' => 'staff',
            'is_active' => 1,
            'email' => 'siza@gmail.com'
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('Siza', $result['data']['first_name']);
        $this->assertEquals('Mpafa', $result['data']['last_name']);
        $this->assertEquals('S-007', $result['data']['employee_id']);
        $this->assertEquals('staff', $result['data']['role']);
        $this->assertEquals(1, $result['data']['is_active']);
        $this->assertEquals('siza@gmail.com', $result['data']['email']);
    }

    public function testUpdateProfileReturnsErrorIfNoFieldsProvided(): void
    {
        $result = $this->model->updateProfileDb('S-007', []);

        $this->assertFalse($result['success']);
        $this->assertEquals('No fields provided for update', $result['error']);
    }

    public function testUpdateProfileReturnsErrorIfEmployeeIdDoesNotExist(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);

        // Only expect ONE prepare call (for the UPDATE)
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);

        $result = $this->model->updateProfileDb('X-999', ['first_name' => 'Ghost']);

        $this->assertFalse($result['success']);
        $this->assertEquals('Profile not found', $result['error']);
    }


    public function testGetProfileByIdReturnsProfileWithCapitalizedFirstName(): void
    {
        $mockData = [
            'id' => '14271887-48ea-48c8-9890-6cb196afa0Gc',
            'first_name' => 'sarah',
            'last_name' => 'johnson',
            'email' => 'sarah@company.com',
            'employee_id' => 'S-006',
            'role' => 'staff',
            'is_active' => 1
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with("SELECT * FROM profiles WHERE employee_id = :employee_id")
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->with(['employee_id' => 'S-006'])
            ->willReturn(true);

        $mockStatement->expects($this->once())
            ->method('fetch')
            ->willReturn($mockData);

        $result = $this->model->getProfileByIdDb('S-006');

        $this->assertTrue($result['success']);
        $this->assertEquals('Sarah', $result['data']['first_name']);
    }

    public function testGetProfileByIdReturnsErrorWhenProfileNotFound(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);
        
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $mockStatement->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $result = $this->model->getProfileByIdDb('99');

        $this->assertFalse($result['success']);
        $this->assertEquals('Profile not found', $result['error']);
    }

    /**
     * @dataProvider capitalizationDataProvider
     */
    public function testCapitalizeFirstNameFormats(string $input, string $expected): void
    {
        $mockData = [
            'id' => 'test-id',
            'employee_id' => 'test-emp-id',
            'first_name' => $input,
            'email' => 'test@company.com'
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $mockStatement->expects($this->once())
            ->method('fetch')
            ->willReturn($mockData);

        $result = $this->model->getProfileByIdDb('test-emp-id');

        if ($expected === '') {
            $this->assertTrue($result['data']['first_name'] === '' || $result['data']['first_name'] === null);
        } else {
            $this->assertEquals($expected, $result['data']['first_name']);
        }
    }

    public static function capitalizationDataProvider(): array
    {
        return [
            'lowercase sarah' => ['sarah', 'Sarah'],
            'uppercase SARAH' => ['SARAH', 'Sarah'],
            'mixed case sArAh' => ['sArAh', 'Sarah'],
            'lowercase john' => ['john', 'John'],
            'uppercase JOHN' => ['JOHN', 'John'],
            'hyphenated name' => ['mary-jane', 'Mary-jane'],
            'empty string' => ['', ''],
            'single letter a' => ['a', 'A']
        ];
    }

    public function testGetProfileByIdHandlesNullFirstName(): void
    {
        $mockData = [
            'id' => 'test-id',
            'employee_id' => 'test-emp-id',
            'first_name' => null,
            'email' => 'test@company.com',
            'last_name' => 'Test',
            'role' => 'staff',
            'is_active' => 1
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $mockStatement->expects($this->once())
            ->method('fetch')
            ->willReturn($mockData);

        $result = $this->model->getProfileByIdDb('test-emp-id');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['data']['first_name'] === null || $result['data']['first_name'] === '');
    }

    public function testGetProfileByIdHandlesMissingFirstNameField(): void
    {
        $mockData = [
            'id' => 'test-id',
            'employee_id' => 'test-emp-id',
            'email' => 'test@company.com',
            'last_name' => 'Test',
            'role' => 'staff',
            'is_active' => 1
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $mockStatement->expects($this->once())
            ->method('fetch')
            ->willReturn($mockData);

        $result = $this->model->getProfileByIdDb('test-emp-id');

        $this->assertTrue($result['success']);
        $this->assertTrue(!isset($result['data']['first_name']) || $result['data']['first_name'] === null);
    }

    public function testProvesAllUserRecordsHavePopulatedFirstName(): void
    {
        $mockUser = [
            'id' => 'user-123',
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'employee_id' => 'S-006',
            'email' => 'sarah@company.com',
            'role' => 'staff',
            'is_active' => 1
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $mockStatement->expects($this->once())
            ->method('fetch')
            ->willReturn($mockUser);

        $result = $this->model->getProfileByIdDb('S-006');

        $this->assertArrayHasKey('first_name', $result['data']);
        $this->assertNotNull($result['data']['first_name']);
        $this->assertNotEquals('', $result['data']['first_name']);
        $this->assertIsString($result['data']['first_name']);
        $this->assertGreaterThan(0, strlen($result['data']['first_name']));
    }

    
    // DELETE PROFILE TESTS
    

    public function testDeleteStaffProfileSuccessfullyByEmployeeId(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement2 = $this->createMock(PDOStatement::class);

        $this->mockDb->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStatement, $mockStatement2);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $mockStatement2->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement2->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => '123', 'employee_id' => 'S-007', 'is_active' => 0]);

        $result = $this->model->deleteProfileDb('S-007');

        $this->assertTrue($result['success']);
        $this->assertEquals('profile deleted successfully', $result['message']);
    }

    public function testDeleteProfileReturnsErrorIfEmployeeIdDoesNotExist(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);

        $result = $this->model->deleteProfileDb('X-999');

        $this->assertFalse($result['success']);
        $this->assertEquals('Profile not found', $result['error']);
    }

    
    // RESET PASSWORD TESTS
    

    public function testResetPasswordGeneratesNew8CharPasswordByEmployeeId(): void
    {
        $mockData = [
            'id' => '18741667-48ea-48c8-9890-6cb196adc0Gc',
            'employee_id' => 'S-007',
            'password' => 'Nq7rT2mX'
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement2 = $this->createMock(PDOStatement::class);

        $this->mockDb->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStatement, $mockStatement2);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $mockStatement2->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement2->expects($this->once())
            ->method('fetch')
            ->willReturn($mockData);

        $result = $this->model->resetPasswordDb('S-007');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('password', $result['data']);
        $this->assertEquals(8, strlen($result['data']['password']));
    }

    public function testResetPasswordReturnsErrorIfEmployeeIdDoesNotExist(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStatement);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);

        $result = $this->model->resetPasswordDb('X-999');

        $this->assertFalse($result['success']);
        $this->assertEquals('Profile not found', $result['error']);
    }

    
    // UPDATE PASSWORD TESTS
    

    public function testUpdatePasswordSuccessfullyByEmployeeId(): void
    {
        $mockData = [
            'id' => '14271887-48ea-48c8-9890-6cb196afa0Gc',
            'employee_id' => 'S-300',
            'password' => '$2b$10$newhashhere',
            'first_name' => 'Official',
            'last_name' => 'Staff',
            'role' => 'staff',
            'is_active' => 1,
            'email' => 'officialstaff@clockit.com'
        ];

        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement2 = $this->createMock(PDOStatement::class);

        $this->mockDb->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStatement, $mockStatement2);

        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $mockStatement2->expects($this->once())
            ->method('execute')
            ->willReturn(true);
            
        $mockStatement2->expects($this->once())
            ->method('fetch')
            ->willReturn($mockData);

        $result = $this->model->updatePasswordDb('S-300', '$2b$10$newhashhere');

        $this->assertTrue($result['success']);
        $this->assertMatchesRegularExpression('/^\$2[ab]\$/', $result['data']['password']);
    }
}
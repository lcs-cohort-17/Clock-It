<?php

use PHPUnit\Framework\TestCase;

class UserManagementTest extends TestCase
{
    private string $viewPath;

    protected function setUp(): void
    {
        $this->viewPath =
            __DIR__ . '/../src/views/admin/usermanagement.php';
    }

    public function testUserManagementPageExists()
    {
        $this->assertFileExists($this->viewPath);
    }

    public function testPageContainsUserManagementTitle()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'User Management',
            $content
        );
    }

    public function testSubtitleExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'self-registration is disabled',
            strtolower($content)
        );
    }

    public function testAddUserButtonExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'Add User',
            $content
        );

        $this->assertStringContainsString(
            'showAddModal',
            $content
        );
    }

    public function testAddUserFormFieldsExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'name="name"',
            $content
        );

        $this->assertStringContainsString(
            'name="email"',
            $content
        );

        $this->assertStringContainsString(
            'name="role"',
            $content
        );
    }

    public function testCreateFunctionalityExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'add_user',
            $content
        );
    }

    public function testEmployeeIdGenerationExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'generateEmployeeId',
            $content
        );

        $this->assertStringContainsString(
            'A-',
            $content
        );

        $this->assertStringContainsString(
            'S-',
            $content
        );
    }

    public function testRandomPasswordGenerationExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'generatePassword',
            $content
        );
    }

    public function testPasswordModalExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'Temporary Password',
            $content
        );
    }

    public function testUsersTableExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            '<table',
            $content
        );
    }

    public function testTableColumnsExist()
    {
        $content = file_get_contents($this->viewPath);

        $columns = [
            'Name',
            'Email',
            'Employee ID',
            'Role',
            'Status',
            'Actions'
        ];

        foreach ($columns as $column) {
            $this->assertStringContainsString(
                $column,
                $content
            );
        }
    }

    public function testAvatarInitialsFeatureExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains(
                strtolower($content),
                'avatar'
            )
            ||
            str_contains(
                strtolower($content),
                'initials'
            )
        );
    }

    public function testRoleBadgesExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'badge-admin')
            ||
            str_contains($content, 'badge-staff')
        );
    }

    public function testStatusBadgesExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains(
                strtolower($content),
                'active'
            )
            ||
            str_contains(
                strtolower($content),
                'inactive'
            )
        );
    }

    public function testEditFunctionalityExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'showEditModal',
            $content
        );

        $this->assertStringContainsString(
            'edit_user',
            $content
        );
    }

    public function testDisableFunctionalityExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'toggle_status',
            $content
        );
    }

    public function testActionButtonsExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'showEditModal')
            &&
            str_contains($content, 'reset_password')
            &&
            str_contains($content, 'toggle_status')
        );
    }

    public function testSearchFunctionalityExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'Search')
            ||
            str_contains($content, 'search')
        );
    }

    public function testPaginationExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'Previous',
            $content
        );

        $this->assertStringContainsString(
            'Next',
            $content
        );
    }

    public function testUsesAlpineJs()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'x-data')
            ||
            str_contains($content, 'x-show')
            ||
            str_contains($content, 'x-model')
            ||
            str_contains($content, '@click')
        );
    }

    public function testUsesBootstrap()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'container-fluid')
            ||
            str_contains($content, 'btn')
            ||
            str_contains($content, 'form-control')
            ||
            str_contains($content, 'd-flex')
        );
    }

    public function testDoesNotUseTailwind()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertFalse(
            str_contains($content, 'bg-gray')
        );

        $this->assertFalse(
            str_contains($content, 'text-gray')
        );

        $this->assertFalse(
            str_contains($content, 'grid-cols')
        );

        $this->assertFalse(
            str_contains($content, 'flex-col')
        );
    }

    public function testRealUserDataStructureExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, '$users')
            ||
            str_contains($content, 'foreach')
        );
    }

    public function testButtonsHaveClickFunctionality()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, '@click')
            ||
            str_contains($content, 'onclick')
        );
    }

    public function testResponsiveLayoutExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'flex-wrap')
            ||
            str_contains($content, 'table-wrapper')
            ||
            str_contains($content, 'container-fluid')
        );
    }
}
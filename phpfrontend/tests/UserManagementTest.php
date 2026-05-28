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

    /*
    |--------------------------------------------------------------------------
    | FILE + FOLDER STRUCTURE TESTS
    |--------------------------------------------------------------------------
    */

    public function testUserManagementPageExists()
    {
        $this->assertFileExists($this->viewPath);
    }

    public function testLayoutFolderExists()
    {
        $this->assertDirectoryExists(
            __DIR__ . '/../src/views/layouts'
        );
    }

    public function testAppLayoutExists()
    {
        $this->assertFileExists(
            __DIR__ . '/../src/views/layouts/app.php'
        );
    }

    public function testPublicFolderExists()
    {
        $this->assertDirectoryExists(
            __DIR__ . '/../public'
        );
    }

    public function testAssetsFolderExists()
    {
        $this->assertDirectoryExists(
            __DIR__ . '/../public/assets'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCEPTANCE CRITERIA TESTS
    |--------------------------------------------------------------------------
    */

    public function testPageContainsUserManagementTitle()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'User Management',
            $content
        );
    }

    public function testAddUserButtonExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'Add User',
            $content
        );
    }

    public function testFormFieldsExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'Full Name') ||
            str_contains($content, 'name')
        );

        $this->assertTrue(
            str_contains($content, 'Email') ||
            str_contains($content, 'email')
        );

        $this->assertTrue(
            str_contains($content, 'Role') ||
            str_contains($content, 'role')
        );
    }

    public function testEmployeeIdGenerationExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'A-') ||
            str_contains($content, 'S-')
        );
    }

    public function testRandomPasswordGenerationExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains(strtolower($content), 'password')
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

        $this->assertTrue(str_contains($content, 'Name'));
        $this->assertTrue(str_contains($content, 'Email'));
        $this->assertTrue(str_contains($content, 'Employee ID'));
        $this->assertTrue(str_contains($content, 'Role'));
        $this->assertTrue(str_contains($content, 'Status'));
        $this->assertTrue(str_contains($content, 'Actions'));
    }

    public function testAvatarInitialsFeatureExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains(
                strtolower($content),
                'initials'
            ) ||
            str_contains(
                strtolower($content),
                'avatar'
            )
        );
    }

    public function testRoleBadgesExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'Admin') ||
            str_contains($content, 'Staff')
        );
    }

    public function testStatusBadgesExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'Active') ||
            str_contains($content, 'Inactive')
        );
    }

    public function testActionButtonsExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'showEditModal') &&
            str_contains($content, 'reset_password') &&
            str_contains($content, 'toggle_status')
        );
    }

    public function testSubtitleExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains(
                strtolower($content),
                'self-registration is disabled'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD FUNCTIONALITY TESTS
    |--------------------------------------------------------------------------
    */

    public function testCreateFunctionalityExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'add_user')
        );
    }

    public function testReadFunctionalityExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            '<table',
            $content
        );
    }

    public function testUpdateFunctionalityExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'edit_user')
        );
    }

    public function testDisableDeleteFunctionalityExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'toggle_status',
            $content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BUTTON FUNCTIONALITY TESTS
    |--------------------------------------------------------------------------
    */

    public function testButtonsExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            '<button',
            $content
        );
    }

    public function testButtonsHaveClickFunctionality()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, '@click') ||
            str_contains($content, 'onclick')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ALPINE JS TESTS
    |--------------------------------------------------------------------------
    */

    public function testAlpineJsExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'x-data') ||
            str_contains($content, 'x-show') ||
            str_contains($content, 'x-model')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BOOTSTRAP TESTS
    |--------------------------------------------------------------------------
    */

    public function testBootstrapClassesExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'container-fluid') ||
            str_contains($content, 'd-flex') ||
            str_contains($content, 'form-control') ||
            str_contains($content, 'btn')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CSS TESTS
    |--------------------------------------------------------------------------
    */

    public function testCssClassesExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertStringContainsString(
            'class=',
            $content
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE RESPONSIVENESS TESTS
    |--------------------------------------------------------------------------
    */

    public function testResponsiveClassesExist()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'container-fluid') ||
            str_contains($content, 'flex-wrap') ||
            str_contains($content, 'd-flex') ||
            str_contains($content, 'gap-')
        );
    }

    public function testResponsiveTableWrapperExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'table-wrapper') ||
            str_contains($content, 'table-responsive') ||
            str_contains($content, 'overflow')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION TESTS
    |--------------------------------------------------------------------------
    */

    public function testPaginationExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'Previous') &&
            str_contains($content, 'Next')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH TESTS
    |--------------------------------------------------------------------------
    */

    public function testSearchFunctionalityExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'Search') ||
            str_contains($content, 'search')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PASSWORD MODAL TESTS
    |--------------------------------------------------------------------------
    */

    public function testPasswordModalExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'Temporary Password')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEFINITION OF DONE TESTS
    |--------------------------------------------------------------------------
    */

    public function testPageUsesRealDataStructure()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, '$users') ||
            str_contains($content, 'foreach')
        );
    }

    public function testTailwindOrBootstrapStylingExists()
    {
        $content = file_get_contents($this->viewPath);

        $this->assertTrue(
            str_contains($content, 'btn') ||
            str_contains($content, 'shadow') ||
            str_contains($content, 'form-control')
        );
    }
}
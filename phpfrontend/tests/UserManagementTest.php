<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UserManagementTest extends TestCase
{
    private string $viewPath;
    private string $helperPath;

    protected function setUp(): void
    {
        $this->viewPath = __DIR__ . '/../src/views/admin/usermanagement.php';
        $this->helperPath = __DIR__ . '/../src/helpers/user-helper.php';
    }

    private function viewContent(): string
    {
        return file_get_contents($this->viewPath);
    }

    public function testUserManagementPageExists(): void
    {
        $this->assertFileExists($this->viewPath);
    }

    public function testPageContainsTitleAndSubtitle(): void
    {
        $content = strtolower($this->viewContent());

        $this->assertStringContainsString('user management', $content);
        $this->assertStringContainsString('self-registration is disabled', $content);
    }

    public function testAddUserButtonAndModalExist(): void
    {
        $content = $this->viewContent();

        $this->assertStringContainsString('Add User', $content);
        $this->assertStringContainsString('showAddModal', $content);
        $this->assertStringContainsString('x-show="showAddModal"', $content);
    }

    public function testAddUserFormHasRequiredFields(): void
    {
        $content = $this->viewContent();

        $this->assertStringContainsString('name="name"', $content);
        $this->assertStringContainsString('name="email"', $content);
        $this->assertStringContainsString('name="role"', $content);
        $this->assertStringContainsString('add_user', $content);
    }

    public function testEmployeeIdGenerationWorks(): void
    {
        require_once $this->helperPath;

        $this->assertSame('A-001', generateEmployeeId('Admin', []));
        $this->assertSame('S-101', generateEmployeeId('Staff', []));
    }

    public function testEmployeeIdFormatIsCorrect(): void
    {
        require_once $this->helperPath;

        $adminId = generateEmployeeId('Admin', []);
        $staffId = generateEmployeeId('Staff', []);

        $this->assertMatchesRegularExpression('/^A-\d{3}$/', $adminId);
        $this->assertMatchesRegularExpression('/^S-\d{3}$/', $staffId);
    }

    public function testRandomPasswordGenerationWorks(): void
    {
        require_once $this->helperPath;

        $password = generatePassword();

        $this->assertNotEmpty($password);
        $this->assertGreaterThanOrEqual(10, strlen($password));
    }

    public function testTemporaryPasswordModalExists(): void
    {
        $content = $this->viewContent();

        $this->assertStringContainsString('Temporary Password', $content);
        $this->assertStringContainsString('generated_password', $content);
    }

    public function testUsersTableExistsWithCorrectColumns(): void
    {
        $content = $this->viewContent();

        $this->assertStringContainsString('<table', $content);

        foreach (['Name', 'Email', 'Employee ID', 'Role', 'Status', 'Actions'] as $column) {
            $this->assertStringContainsString($column, $content);
        }
    }

    public function testAvatarInitialsExist(): void
    {
        $content = strtolower($this->viewContent());

        $this->assertTrue(
            str_contains($content, 'avatar') ||
            str_contains($content, 'initials')
        );
    }

    public function testRoleBadgesExist(): void
    {
        $content = $this->viewContent();

        $this->assertTrue(
            str_contains($content, 'badge-admin') &&
            str_contains($content, 'badge-staff')
        );
    }

    public function testStatusBadgesExist(): void
    {
        $content = $this->viewContent();

        $this->assertTrue(
            str_contains($content, 'status') &&
            str_contains($content, 'Active') ||
            str_contains($content, 'Inactive')
        );
    }

    public function testEditFunctionalityExists(): void
    {
        $content = $this->viewContent();

        $this->assertStringContainsString('showEditModal', $content);
        $this->assertStringContainsString('edit_user', $content);
        $this->assertStringContainsString('x-model="editName"', $content);
        $this->assertStringContainsString('x-model="editEmail"', $content);
        $this->assertStringContainsString('x-model="editRole"', $content);
    }

    public function testDisableFunctionalityExists(): void
    {
        $content = $this->viewContent();

        $this->assertStringContainsString('toggle_status', $content);
    }

    public function testResetPasswordFunctionalityExists(): void
    {
        $content = $this->viewContent();

        $this->assertStringContainsString('reset_password', $content);
        $this->assertStringContainsString('bi-key', $content);
    }

    public function testShareOrCopyLinkActionExists(): void
    {
        $content = strtolower($this->viewContent());

        $this->assertTrue(
            str_contains($content, 'copy') ||
            str_contains($content, 'share') ||
            str_contains($content, 'bi-link') ||
            str_contains($content, 'bi-clipboard')
        );
    }

    public function testActionsColumnHasExpectedButtons(): void
    {
        $content = $this->viewContent();

        $this->assertStringContainsString('bi-pencil', $content);
        $this->assertStringContainsString('bi-key', $content);
        $this->assertStringContainsString('bi-slash-circle', $content);
    }

    public function testSearchExists(): void
    {
        $content = strtolower($this->viewContent());

        $this->assertTrue(
            str_contains($content, 'search') &&
            str_contains($content, 'name="q"')
        );
    }

    public function testPaginationExists(): void
    {
        $content = $this->viewContent();

        $this->assertStringContainsString('Previous', $content);
        $this->assertStringContainsString('Next', $content);
        $this->assertStringContainsString('page=', $content);
    }

    public function testUsesAlpineJs(): void
    {
        $content = $this->viewContent();

        $this->assertTrue(
            str_contains($content, 'x-data') ||
            str_contains($content, 'x-show') ||
            str_contains($content, 'x-model') ||
            str_contains($content, '@click')
        );
    }

    public function testUsesBootstrapAndCustomCssOnly(): void
    {
        $content = $this->viewContent();

        $this->assertTrue(
            str_contains($content, 'container-fluid') ||
            str_contains($content, 'btn') ||
            str_contains($content, 'form-control') ||
            str_contains($content, 'd-flex') ||
            str_contains($content, 'table')
        );
    }

    public function testDoesNotUseTailwind(): void
    {
        $content = $this->viewContent();

        $tailwindClasses = [
            'bg-gray',
            'text-gray',
            'grid-cols',
            'flex-col',
            'rounded-xl',
            'p-6',
            'md:',
            'lg:',
            'dark:'
        ];

        foreach ($tailwindClasses as $class) {
            $this->assertFalse(
                str_contains($content, $class),
                "Tailwind class found: {$class}"
            );
        }
    }

    public function testResponsiveLayoutExists(): void
    {
        $content = $this->viewContent();

        $this->assertTrue(
            str_contains($content, 'container-fluid') ||
            str_contains($content, 'table-wrapper') ||
            str_contains($content, 'table-responsive') ||
            str_contains($content, 'flex-wrap')
        );
    }

    public function testRealUserDataLoopExists(): void
    {
        $content = $this->viewContent();

        $this->assertTrue(
            str_contains($content, '$users') ||
            str_contains($content, 'foreach ($pageData as $u)')
        );
    }
}
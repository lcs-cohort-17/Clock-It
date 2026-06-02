<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class LeaveRequestModalTest extends TestCase
{
    private string $leaveRequestModalFile;
    private string $content;

    protected function setUp(): void
    {
        $this->leaveRequestModalFile = dirname(__DIR__) . '/modals/LeaveRequestModal.php';
        $this->content = file_get_contents($this->leaveRequestModalFile);
    }

    public function testLeaveRequestModalFileExists(): void
    {
        $this->assertFileExists($this->leaveRequestModalFile);
    }

    public function testLeaveRequestModalHasModalWrapper(): void
    {
        $this->assertStringContainsString('x-show="showLeave"', $this->content);
        $this->assertStringContainsString('class="custom-modal"', $this->content);
    }

    public function testLeaveRequestModalHeader(): void
    {
        $this->assertStringContainsString('<h2>New Leave Request</h2>', $this->content);
        $this->assertStringContainsString('Submit a request for time off.', $this->content);
    }

    public function testLeaveRequestModalHasFormFields(): void
    {
        $this->assertStringContainsString('<form>', $this->content);
        $this->assertStringContainsString('<select class="form-select">', $this->content);
        $this->assertStringContainsString('<input', $this->content);
        $this->assertStringContainsString('type="date"', $this->content);
        $this->assertStringContainsString('<textarea', $this->content);
    }

    public function testLeaveRequestModalHasLeaveTypeOptions(): void
    {
        $this->assertStringContainsString('Annual Leave', $this->content);
        $this->assertStringContainsString('Sick Leave', $this->content);
        $this->assertStringContainsString('Family Responsibility', $this->content);
    }

    public function testLeaveRequestModalHasActions(): void
    {
        $this->assertStringContainsString('class="btn btn-outline-secondary"', $this->content);
        $this->assertStringContainsString('class="btn btn-primary"', $this->content);
        $this->assertStringContainsString('Cancel', $this->content);
        $this->assertStringContainsString('Submit Request', $this->content);
    }

    public function testLeaveRequestModalCloseButton(): void
    {
        $this->assertStringContainsString('class="btn-close"', $this->content);
        $this->assertStringContainsString('@click="showLeave=false"', $this->content);
    }
}

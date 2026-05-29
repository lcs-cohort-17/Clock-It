<?php

use PHPUnit\Framework\TestCase;

class ProfileRenderTest extends TestCase
{
    public function testProfilePageLoads()
    {
        $user = [
            'name' => 'Demo Staff',
            'email' => 'staff@clockit.app',
            'employeeId' => 'EMP-001',
            'role' => 'staff',
        ];

        ob_start();
        include __DIR__ . '/../src/views/staff/profile/profile.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Profile Information', $output);
        $this->assertStringContainsString('window.ProfileBootstrap', $output);
    }

    public function testDoesNotLeakEditingState()
    {
        $user = [
            'name' => 'Demo Staff',
            'email' => 'staff@clockit.app',
            'employeeId' => 'EMP-001',
            'role' => 'staff',
        ];

        ob_start();
        include __DIR__ . '/../src/views/staff/profile/profile.php';
        $output = ob_get_clean();

        // ensures no broken server-side state leaks
        $this->assertStringNotContainsString('isEditing = true', $output);
    }

    public function testCropPreviewIsSquareToPreventAvatarStretching()
    {
        $css = file_get_contents(__DIR__ . '/../public/assets/css/profile.css');
        $js = file_get_contents(__DIR__ . '/../public/assets/js/profile.js');

        $this->assertStringContainsString('aspect-ratio: 1 / 1', $css);
        $this->assertStringContainsString('const cropSize = Math.min(el.cropArea.offsetWidth, el.cropArea.offsetHeight);', $js);
        $this->assertStringContainsString('const srcW = cropSize * scaleFactor;', $js);
        $this->assertStringContainsString('const srcH = cropSize * scaleFactor;', $js);
    }
}

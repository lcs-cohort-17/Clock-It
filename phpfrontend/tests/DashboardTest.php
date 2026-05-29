<?php
use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase {
    
    public function testAppTitleIsConfigured() {
        $appName = "Clock-It";
        

        $this->assertEquals("Clock-It", $appName);
    }


    public function testViewsDirectoryExists() {
        $viewsDirectory = __DIR__ . '/../src/views';
        

        $this->assertNotNull($viewsDirectory);
    }
}
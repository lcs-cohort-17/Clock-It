<?php
use PHPUnit\Framework\TestCase;

class AdminOverrideTest extends TestCase
{
    private $mysqli;
    
    protected function setUp(): void
    {
        $this->mysqli = new mysqli("127.0.0.1:3307", "root", "", "clock_it_db");
        if ($this->mysqli->connect_error) {
            $this->markTestSkipped("DB connection failed: " . $this->mysqli->connect_error);
        }
    }

public function testAuditTriggerFiresOnOverride()
{
    $id = '80e86f2f-21b0-47b8-8841-5832b2a4286e';
    $admin_id = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';
    
    // Step 1: Reset updated_by to NULL so trigger condition will pass
    $this->mysqli->query("UPDATE attendance_events SET updated_by=NULL WHERE id='$id'");
    
    $time = "2026-05-28 16:30:00"; 
    $loc = "PHPUnit DAT-EPIC-02 test run";
    
    // Count audit logs before
    $before = $this->mysqli->query("SELECT COUNT(*) as c FROM audit_logs")->fetch_assoc()['c'];
    
    // Step 2: Run the override UPDATE that should fire the trigger
    $stmt = $this->mysqli->prepare("UPDATE attendance_events SET timestamp=?, location=?, updated_by=? WHERE id=?");
    $stmt->bind_param("ssss", $time, $loc, $admin_id, $id);
    $stmt->execute();
    $stmt->close();

    // Count audit logs after
    $after = $this->mysqli->query("SELECT COUNT(*) as c FROM audit_logs")->fetch_assoc()['c'];
    $this->assertEquals($before + 1, $after, "Audit trigger did not create a new log entry");

    // Check the newest audit log for our specific data
    $result = $this->mysqli->query("SELECT * FROM audit_logs WHERE record_id='$id' ORDER BY id DESC LIMIT 1");
    $log = $result->fetch_assoc();
    
    $this->assertNotNull($log, "Audit log was not created by trigger");
    $this->assertEquals('override', $log['action']);
    $this->assertEquals($admin_id, $log['changed_by']);
    $this->assertStringContainsString('16:30:00', $log['new_values']);
}
    protected function tearDown(): void
    {
        $this->mysqli->close();
    }
}
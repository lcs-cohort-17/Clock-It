<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class DataRetentionTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require_once __DIR__ . '/../src/views/partials/settings_features.php';

        $_SESSION['retention_days'] = 30;
        $_SESSION['retention_records'] = [
            ['id' => 1, 'name' => 'Very old record', 'date' => date('Y-m-d', strtotime('-90 days'))],
            ['id' => 2, 'name' => 'Recent record', 'date' => date('Y-m-d', strtotime('-5 days'))],
        ];
        unset($_SESSION['retention_error'], $_SESSION['retention_success']);
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    private function renderDataRetentionCard(): string
    {
        ob_start();
        render_data_retention_settings_card();
        return ob_get_clean();
    }

    private function renderSettingsPost(string $days): string
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'action' => 'purge_retention',
            'retention_days' => $days,
        ];

        ob_start();
        include __DIR__ . '/../src/views/admin/settings.php';
        return ob_get_clean();
    }

    public function testDataRetentionCardRenders(): void
    {
        $output = $this->renderDataRetentionCard();

        $this->assertStringContainsString('Data Retention', $output);
        $this->assertStringContainsString('Keep attendance logs for 12 months.', $output);
        $this->assertStringContainsString('class="card settings-card data-retention-settings-card"', $output);
    }

    public function testAdminCanEnterNumberOfDays(): void
    {
        $output = $this->renderDataRetentionCard();

        $this->assertStringContainsString('name="retention_days"', $output);
        $this->assertStringContainsString('value="30"', $output);
    }

    public function testPurgeButtonDisplays(): void
    {
        $output = $this->renderDataRetentionCard();

        $this->assertStringContainsString('Purge Old Records', $output);
    }

    public function testPurgeRemovesOnlyRecordsOlderThanSelectedDays(): void
    {
        $output = $this->renderSettingsPost('30');

        $this->assertCount(1, $_SESSION['retention_records']);
        $this->assertSame(2, $_SESSION['retention_records'][0]['id']);
        $this->assertStringContainsString('1 old record purged. Recent records were kept.', $output);
    }

    public function testInvalidLettersAreRejected(): void
    {
        $output = $this->renderSettingsPost('abc');

        $this->assertCount(2, $_SESSION['retention_records']);
        $this->assertStringContainsString('Please enter a positive whole number of days.', $output);
    }

    public function testNegativeDaysAreRejected(): void
    {
        $output = $this->renderSettingsPost('-10');

        $this->assertCount(2, $_SESSION['retention_records']);
        $this->assertStringContainsString('Please enter a positive whole number of days.', $output);
    }

    public function testEmptyDaysAreRejected(): void
    {
        $output = $this->renderSettingsPost('');

        $this->assertCount(2, $_SESSION['retention_records']);
        $this->assertStringContainsString('Please enter a positive whole number of days.', $output);
    }

    public function testRecentRecordsAreNotDeletedByMistake(): void
    {
        $this->renderSettingsPost('30');

        $remainingIds = array_column($_SESSION['retention_records'], 'id');
        $this->assertContains(2, $remainingIds);
        $this->assertNotContains(1, $remainingIds);
    }
}

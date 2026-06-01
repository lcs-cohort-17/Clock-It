<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/views/partials/QRCodeHelpers.php';
require_once __DIR__ . '/../src/views/partials/CreateQRForm.php';
require_once __DIR__ . '/../src/views/partials/QRDropdownButton.php';
require_once __DIR__ . '/../src/views/partials/QRCodeListItem.php';
require_once __DIR__ . '/../src/views/partials/QRModal.php';
require_once __DIR__ . '/../src/views/admin/QRCodeGeneratorLayout.php';

final class QRCodeAcceptanceCriteriaTest extends TestCase
{
    public function testQrFeatureMeetsTechnicalRequirementsAndAcceptanceCriteria(): void
    {
        $dropdown = render_qr_dropdown_button();
        $form = render_create_qr_form(true);
        $generatedQr = qr_generate('clock-in', 'Main Entrance', 'HQ');
        $modal = render_qr_modal($generatedQr);

        $activeItem = [
            'id' => '1',
            'name' => 'HQ Entrance',
            'type' => 'clock-in',
            'location' => 'Main Entrance',
            'createdAt' => time(),
            'expiresAfter' => 24,
        ];
        $expiredItem = [
            'id' => '2',
            'name' => 'HQ Exit',
            'type' => 'clock-out',
            'location' => '',
            'createdAt' => time() - 7200,
            'expiresAfter' => 1,
        ];
        $activeItemQr = qr_from_list_item($activeItem);
        $activeListItem = render_qr_code_list_item($activeItem);
        $expiredListItem = render_qr_code_list_item($expiredItem);

        ob_start();
        render_qr_generator_layout([$activeItem, $expiredItem], true, $modal, 'active');
        $layout = (string) ob_get_clean();

        self::assertStringContainsString('Generate QR Code', $dropdown, 'Dropdown button component should render.');
        self::assertStringContainsString('Clock In QR', $dropdown, 'Dropdown should include Clock In option.');
        self::assertStringContainsString('Clock Out QR', $dropdown, 'Dropdown should include Clock Out option.');
        self::assertStringContainsString('@keydown.escape.window', $dropdown, 'Dropdown should support keyboard escape.');
        self::assertStringContainsString('aria-haspopup="true"', $dropdown, 'Dropdown should expose ARIA popup state.');

        self::assertStringContainsString('role="dialog"', $modal, 'Reusable modal should expose dialog role.');
        self::assertStringContainsString('aria-modal="true"', $modal, 'Modal should be accessible to assistive tech.');
        self::assertStringContainsString('modal-dialog-centered', $modal, 'Modal should use responsive Bootstrap modal layout.');
        self::assertStringContainsString('https://api.qrserver.com/v1/create-qr-code/', $modal, 'QR generation library/service should be integrated.');

        self::assertSame('clock-in', $generatedQr['type'], 'QR generation should preserve selected QR type.');
        self::assertSame(60, $generatedQr['expiresAt'] - $generatedQr['createdAt'], 'Dropdown-generated QR should expire after 60 seconds.');
        self::assertSame(86400, $activeItemQr['expiresAt'] - $activeItemQr['createdAt'], 'Created QR should respect the form expiry hours.');
        self::assertStringContainsString('Expires after 24h 0m 0s', render_qr_modal($activeItemQr), 'Created QR modal should show accurate expiry duration.');
        self::assertStringContainsString('x-text="formatTime(secondsLeft)"', $modal, 'Modal should show a live countdown.');

        self::assertSame('used', qr_status(qr_mark_used($generatedQr)), 'QR logic should support single-use used state.');
        self::assertSame('expired', qr_item_status($expiredItem), 'QR logic should expire created QR codes.');
        self::assertStringContainsString('No active QR', render_qr_modal(qr_from_list_item($expiredItem)), 'Expired QR should not show a scannable image.');

        self::assertStringContainsString('Generate New QR', $modal, 'Modal should include a refresh/regenerate action.');
        self::assertStringContainsString('Label: Main Entrance', $generatedQr['value'], 'Scanned QR text should include the label.');
        self::assertStringContainsString('Location: HQ', $generatedQr['value'], 'Scanned QR text should include the location.');
        self::assertStringContainsString('Location: None', qr_text_value('clock-out', time(), 'Exit'), 'QR text should show None when location is missing.');

        self::assertStringContainsString('Active', $activeListItem, 'QR list item should show active status.');
        self::assertStringContainsString('Expired', $expiredListItem, 'QR list item should show expired status.');
        self::assertStringContainsString('href="?status=all"', $layout, 'All filter should render.');
        self::assertStringContainsString('href="?status=active"', $layout, 'Active filter should render.');
        self::assertStringContainsString('href="?status=expired"', $layout, 'Expired filter should render.');

        self::assertStringContainsString('name="label" required', $form, 'Create form should require a QR label.');
        self::assertStringContainsString('name="expiresAfter"', $form, 'Create form should include expiry-hours input.');
        self::assertStringContainsString('name="viewport"', $layout, 'Layout should be responsive on mobile/tablet.');
        self::assertStringContainsString('bootstrap@5.3.2', $layout, 'Bootstrap CSS should be loaded.');
        self::assertStringContainsString('alpinejs', $layout, 'Alpine.js should be loaded.');

        self::assertStringContainsString('fetch(', $layout . $modal, 'Backend API endpoint for QR token generation is required.');
        self::assertStringContainsString('Loading', $layout . $modal, 'Loading state while QR generates is required.');
        self::assertStringContainsString('error', strtolower($layout . $modal), 'Error handling for failed generation/network issues is required.');
    }
}

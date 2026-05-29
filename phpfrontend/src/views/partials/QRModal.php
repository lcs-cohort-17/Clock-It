<?php

declare(strict_types=1);

require_once __DIR__ . '/QRCodeHelpers.php';

function render_qr_modal(?array $qr): string
{
    if (!$qr) {
        return '';
    }

    $status = qr_status($qr);
    $type = (string) $qr['type'];
    $title = $type === 'clock-in' ? 'Clock In QR Code' : 'Clock Out QR Code';
    $value = (string) $qr['value'];
    $escapedValue = qr_h($value);
    $encodedValue = rawurlencode($value);
    $secondsLeft = max(0, (int) $qr['expiresAt'] - time());
    $expiresAfterLabel = qr_format_seconds_left((int) $qr['expiresAt'] - (int) $qr['createdAt']);
    $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&color=002f4f&bgcolor=ffffff&data={$encodedValue}";

    $statusTitle = match ($status) {
        'active' => 'Ready to Scan',
        'used' => 'QR Code Already Used',
        'expired' => 'QR Code Expired',
        default => 'Generating QR Code...',
    };

    $message = match ($status) {
        'active' => 'This QR code expires in <span x-text="formatTime(secondsLeft)"></span>',
        'used' => 'This code has been scanned. Please generate a new one.',
        'expired' => 'The QR code has expired. Please refresh to get a new one.',
        default => 'Please wait while we create your secure code',
    };

    $qrBody = $status === 'active'
        ? '<img class="qr-image" src="' . qr_h($qrImageUrl) . '" alt="' . qr_h($value) . '">'
        : '<div class="qr-placeholder">No active QR</div>';

    return <<<HTML
    <div class="modal-backdrop fade show"></div>
    <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="qr-modal-title" x-data="{ secondsLeft: {$secondsLeft}, formatTime(value) { const seconds = Math.max(0, value); const hours = Math.floor(seconds / 3600); const minutes = Math.floor((seconds % 3600) / 60); const remainingSeconds = seconds % 60; if (hours > 0) return hours + 'h ' + minutes + 'm ' + remainingSeconds + 's'; if (minutes > 0) return minutes + 'm ' + remainingSeconds + 's'; return remainingSeconds + 's'; }, start() { setInterval(() => { if (this.secondsLeft > 0) this.secondsLeft-- }, 1000) } }" x-init="start()">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="modal-title fs-5" id="qr-modal-title">{$title}</h2>
            <a class="btn-close" href="./index.php?clearQr=1" aria-label="Close"></a>
          </div>
          <div class="modal-body text-center">
            <h3>{$statusTitle}</h3>
            <p class="text-muted">{$message}</p>
            <div class="qr-frame mx-auto mb-3">{$qrBody}</div>
            <p class="qr-text">{$escapedValue}</p>
            <div class="d-flex justify-content-center gap-2 mt-3">
              <a class="btn btn-outline-secondary" href="./index.php?clearQr=1">Close</a>
              <a class="btn btn-primary" href="?qrType={$type}">Generate New QR</a>
            </div>
            <p class="text-muted small mt-3">Single-use only - Expires after {$expiresAfterLabel}</p>
          </div>
        </div>
      </div>
    </div>
    HTML;
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/QRCodeHelpers.php';

function render_qr_code_list_item(array $item): string
{
    $id = qr_h((string) $item['id']);
    $name = qr_h((string) $item['name']);
    $type = (string) $item['type'];
    $location = trim((string) ($item['location'] ?? ''));
    $createdAt = (int) $item['createdAt'];
    $expiresAfter = (int) ($item['expiresAfter'] ?? 24);
    $status = qr_item_status($item);
    $statusLabel = ucfirst($status);
    $statusClass = $status === 'active' ? 'text-bg-success' : 'text-bg-secondary';
    $typeClass = $type === 'clock-in' ? 'clock-in' : 'clock-out';
    $icon = $type === 'clock-in' ? 'In' : 'Out';
    $createdDate = date('Y/m/d', $createdAt);
    $createdTime = date('H:i', $createdAt);
    $expiresAt = qr_item_expires_at($item);
    $expiresDate = date('Y/m/d', $expiresAt);
    $expiresTime = date('H:i', $expiresAt);
    $locationHtml = $location !== '' ? '<p class="muted small">' . qr_h($location) . '</p>' : '';

    return <<<HTML
    <div class="qr-list-item">
      <a class="qr-list-main" href="?viewQr={$id}" aria-label="View {$name} QR code">
        <div class="type-icon {$typeClass}" aria-hidden="true">{$icon}</div>
        <div>
          <p class="item-title">{$name} <span class="badge {$statusClass}">{$statusLabel}</span></p>
          <p class="muted small">Created {$createdDate} at {$createdTime} - Expires after {$expiresAfter}h - Expires {$expiresDate} at {$expiresTime}</p>
          {$locationHtml}
        </div>
      </a>
      <form method="post">
        <input type="hidden" name="action" value="revoke">
        <input type="hidden" name="id" value="{$id}">
        <button class="icon-button danger" type="submit" aria-label="Revoke QR">X</button>
      </form>
    </div>
    HTML;
}

<?php

declare(strict_types=1);

const QR_EXPIRY_SECONDS = 60;

function qr_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function qr_format_datetime(?int $timestamp = null): string
{
    return date('Y/m/d H:i', $timestamp ?? time());
}

function qr_generate(string $type, string $label = '', string $location = ''): array
{
    if (!in_array($type, ['clock-in', 'clock-out'], true)) {
        throw new InvalidArgumentException('Invalid QR type.');
    }

    $createdAt = time();

    return [
        'token' => sprintf('%s-%d-%s', $type, $createdAt, bin2hex(random_bytes(6))),
        'type' => $type,
        'createdAt' => $createdAt,
        'expiresAt' => $createdAt + QR_EXPIRY_SECONDS,
        'value' => qr_text_value($type, $createdAt, $label, $location),
    ];
}

function qr_from_list_item(array $item): array
{
    $type = (string) $item['type'];
    $createdAt = (int) $item['createdAt'];
    $expiresAt = qr_item_expires_at($item);

    return [
        'token' => sprintf('%s-%d-%s', $type, $createdAt, (string) $item['id']),
        'type' => $type,
        'createdAt' => $createdAt,
        'expiresAt' => $expiresAt,
        'value' => qr_text_value($type, $createdAt, (string) $item['name'], (string) ($item['location'] ?? '')),
    ];
}

function qr_item_expires_at(array $item): int
{
    $createdAt = (int) $item['createdAt'];
    $expiresAfter = max(1, (int) ($item['expiresAfter'] ?? 24));

    return $createdAt + ($expiresAfter * 60 * 60);
}

function qr_item_status(array $item): string
{
    return time() > qr_item_expires_at($item) ? 'expired' : 'active';
}

function qr_format_seconds_left(int $seconds): string
{
    $seconds = max(0, $seconds);
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $remainingSeconds = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%dh %dm %ds', $hours, $minutes, $remainingSeconds);
    }

    if ($minutes > 0) {
        return sprintf('%dm %ds', $minutes, $remainingSeconds);
    }

    return sprintf('%ds', $remainingSeconds);
}

function qr_text_value(string $type, ?int $timestamp = null, string $label = '', string $location = ''): string
{
    $action = $type === 'clock-in' ? 'Clocked in' : 'Clocked out';
    $displayLabel = trim($label) !== '' ? trim($label) : ($type === 'clock-in' ? 'Clock In QR Code' : 'Clock Out QR Code');
    $displayLocation = trim($location) !== '' ? trim($location) : 'None';

    return sprintf(
        "%s at %s\nLabel: %s\nLocation: %s",
        $action,
        qr_format_datetime($timestamp),
        $displayLabel,
        $displayLocation
    );
}

function qr_status(?array $qr): string
{
    if (!$qr) {
        return 'loading';
    }

    if (($qr['used'] ?? false) === true) {
        return 'used';
    }

    return time() > (int) $qr['expiresAt'] ? 'expired' : 'active';
}

function qr_mark_used(array $qr): array
{
    $qr['used'] = true;
    return $qr;
}

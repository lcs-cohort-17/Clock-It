<?php

declare(strict_types=1);

/**
 * @var array<int, array{label: string, href: string, variant: string}> $actions
 * @var bool $sheetsConnected
 */
?>
<nav class="quick-actions" aria-label="Dashboard actions">
  <?php foreach ($actions as $action): ?>
    <?php
      $label = $action['label'];
      $href = $action['href'];
    ?>

    <a
      class="btn btn-<?= e($action['variant']) ?> card dashboard-card quick-action-card rounded-4 border shadow-sm bg-white text-start"
      href="<?= e($href) ?>"
      aria-label="<?= e($label) ?>"
    >
      <?php if ($label === 'QR Generator'): ?>
        <i class="bi bi-qr-code quick-action-icon" aria-hidden="true"></i>
      <?php else: ?>
        <i class="bi bi-file-earmark-text quick-action-icon" aria-hidden="true"></i>
      <?php endif; ?>

      <i class="bi bi-arrow-up-right quick-action-arrow" aria-hidden="true"></i>

      <h2 class="h5 quick-action-title"><?= e($label) ?></h2>
      <p class="quick-action-description mb-0">
        <?php if ($label === 'QR Generator'): ?>
          Create and manage clock-in QR codes
        <?php elseif ($label === 'Attendance Logs'): ?>
          All clock events with full audit trail
        <?php else: ?>
          <?= $sheetsConnected ? 'Connected' : 'Not connected' ?>
        <?php endif; ?>
      </p>
    </a>
  <?php endforeach; ?>
</nav>

<?php

declare(strict_types=1);

function render_qr_generator_layout(array $qrList, bool $showCreateForm, string $modal, string $statusFilter = 'all'): void
{
    $filterLinks = [
        'all' => 'All',
        'active' => 'Active',
        'expired' => 'Expired',
    ];
    ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>QR Code Generator</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    :root { --navy: #002f4f; --text: #1f2937; --muted: #6b7280; --bg: #f4f4f4; --border: #e5e7eb; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: Arial, sans-serif; background: var(--bg); color: var(--text); }
    a { color: inherit; text-decoration: none; }
    .page { max-width: 72rem; margin: 0 auto; padding: 2rem 1rem; }
    h1 { color: var(--navy); margin: 0; font-size: clamp(1.5rem, 2vw, 2rem); }
    .muted { color: var(--muted); }
    .small { font-size: .75rem; }
    .actions { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1rem; margin: 2rem 0; }
    .btn { border: 0; border-radius: .5rem; cursor: pointer; display: inline-flex; align-items: center; gap: .5rem; font-weight: 600; padding: .7rem 1.1rem; }
    .btn-primary { background: var(--navy); color: white; }
    .btn-secondary { background: white; border: 1px solid #d1d5db; color: var(--navy); }
    .dropdown { position: relative; }
    .dropdown-menu { position: absolute; z-index: 20; min-width: 12rem; margin-top: .5rem; border: 1px solid var(--border); border-radius: .5rem; background: white; box-shadow: 0 10px 20px rgba(0,0,0,.12); }
    .dropdown-item { display: block; padding: .75rem 1rem; }
    .dropdown-item:hover { background: #f9fafb; }
    .panel, .list-shell { background: rgba(255,255,255,.75); border-radius: .75rem; border: 1px solid #f3f4f6; padding: 1rem; }
    .panel-header, .list-header, .qr-list-item, .modal-header, .modal-actions { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
    .status-filters { display: inline-flex; gap: .35rem; }
    .status-filter { border-radius: .35rem; color: var(--muted); padding: .35rem .55rem; }
    .status-filter.active { background: var(--navy); color: white; }
    .form-grid { display: grid; gap: 1rem; }
    label span { display: block; color: #374151; font-size: .875rem; margin-bottom: .25rem; }
    input, select { width: 100%; border: 1px solid #d1d5db; border-radius: .5rem; padding: .65rem .75rem; }
    .form-actions { display: flex; gap: .75rem; }
    .qr-list { display: grid; gap: .75rem; }
    .qr-list-item { background: white; border: 1px solid #f3f4f6; border-radius: .75rem; padding: 1rem; }
    .qr-list-main { display: flex; align-items: center; gap: 1rem; flex: 1; }
    .qr-list-main:hover .item-title { color: var(--navy); }
    .type-icon { border-radius: .5rem; padding: .5rem; }
    .type-icon.clock-in { background: #ecfdf5; color: #16a34a; }
    .type-icon.clock-out { background: #fffbeb; color: #d97706; }
    .item-title { font-weight: 600; margin: 0 0 .25rem; }
    .icon-button, .icon-link { background: transparent; border: 0; color: #6b7280; cursor: pointer; font-size: 1.2rem; }
    .danger:hover { color: #ef4444; }
    .modal { --bs-modal-width: 28rem; }
    .modal-content { overflow: hidden; border: 0; border-radius: 1rem; box-shadow: 0 20px 50px rgba(0,0,0,.25); }
    .modal-header { border-bottom: 1px solid #f3f4f6; padding: 1.25rem; }
    .modal-header h2 { color: var(--navy); font-size: 1.25rem; margin: 0; }
    .modal-body { padding: 1.5rem; text-align: center; }
    .modal-body h3 { margin: 0 0 .25rem; }
    .qr-frame { display: inline-flex; margin: 1rem 0; border: 1px solid #f3f4f6; border-radius: .75rem; background: white; padding: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
    .qr-image, .qr-placeholder { width: 220px; height: 220px; }
    .qr-placeholder { display: grid; place-items: center; background: #f9fafb; color: #9ca3af; }
    .qr-text { color: var(--navy); font-weight: 700; white-space: pre-line; }
  </style>
</head>
<body>
  <main class="page">
    <header>
      <h1>QR Code Generator</h1>
      <p class="muted">Create unique QR codes for clock-in/clock-out points.</p>
    </header>

    <section class="actions">
      <?= render_qr_dropdown_button() ?>
      <a class="btn btn-secondary" href="?create=1">+ New QR code</a>
    </section>

    <?= render_create_qr_form($showCreateForm) ?>

    <section class="list-shell">
      <div class="list-header">
        <h2>QR Codes</h2>
        <div class="status-filters small" aria-label="Filter QR codes">
          <?php foreach ($filterLinks as $filter => $label): ?>
            <a class="status-filter <?= $statusFilter === $filter ? 'active' : '' ?>" href="?status=<?= $filter ?>"><?= $label ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="qr-list">
        <?php foreach ($qrList as $item): ?>
          <?= render_qr_code_list_item($item) ?>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <?= $modal ?>
</body>
</html>
    <?php
}

<?php ob_start(); ?>
<div class="app-shell">
<?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>
<div class="main-panel"><?php require __DIR__ . '/../partials/header.php'; ?>
<main class="content center-content">
    <h1>Scan QR Code</h1>
    <p class="muted">Display this workplace QR code so staff can scan it from their phone.</p>
    <section class="card qr-card">
        <p class="badge">Workplace QR Code</p>
        <div class="fake-qr">CLOCK<br>IT</div>
        <strong>CLOCK-IT-SITE-001</strong>
        <p class="muted">Use this QR code for workplace attendance check-in and check-out.</p>
    </section>
</main></div></div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

<?php ob_start(); ?>
<div class="app-shell">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>
        <main class="content">
            <h1>QR Code Generator</h1>
            <section class="card qr-card">
                <p class="badge">Generated Workplace Code</p>
                <div class="fake-qr">ADMIN<br>QR</div>
                <strong>CLOCK-IT-SITE-001</strong>
                <button onclick="alert('QR code regenerated')">Generate QR</button>
            </section>
        </main>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

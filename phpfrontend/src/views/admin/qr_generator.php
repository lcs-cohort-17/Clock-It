<?php
$pageTitle = 'QR Generator';
$user = $user ?? ['name' => 'Demo Admin'];

ob_start();
?>
<div class="app-shell">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>
        <main class="content">
            <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-slate-900">QR Generator</h1>
                        <p class="mt-2 text-lg text-slate-600">Create the demo attendance QR code for staff clock events.</p>
                    </div>
                    <section class="qr-card card">
                        <h2>Today&apos;s Clock QR</h2>
                        <div class="fake-qr">CLOCK-IT</div>
                        <p class="muted">Demo QR code for the current attendance session.</p>
                        <button type="button">Download QR</button>
                    </section>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';

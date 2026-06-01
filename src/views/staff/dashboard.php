<?php ob_start(); ?>
<div class="app-shell">
<?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>
<div class="main-panel">
<?php require __DIR__ . '/../partials/header.php'; ?>
<main class="content">
    <h1>Staff Dashboard</h1>
    <p class="muted">Welcome back, <?= htmlspecialchars($user['name']) ?>.</p>
    <section class="grid stats-grid">
        <div class="card"><span>Currently Onsite</span><strong><?= $stats['currentlyOnsite'] ?></strong></div>
        <div class="card"><span>Total Staff Today</span><strong><?= $stats['totalStaffToday'] ?></strong></div>
        <div class="card"><span>Pending Sync</span><strong><?= $stats['pendingSync'] ?></strong></div>
        <div class="card"><span>Total Events</span><strong><?= $stats['totalEvents'] ?></strong></div>
    </section>
    <section class="card">
        <h2>Today&apos;s Activity</h2>
        <table><thead><tr><th>Name</th><th>Type</th><th>Time</th></tr></thead><tbody>
        <?php foreach ($events as $event): ?>
            <tr><td><?= htmlspecialchars($event['userName']) ?></td><td><?= strtoupper($event['type']) ?></td><td><?= htmlspecialchars($event['timestamp']) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </section>
</main>
</div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

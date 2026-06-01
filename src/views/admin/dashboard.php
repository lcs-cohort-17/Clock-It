<?php ob_start(); ?>
<div class="app-shell"><?php require __DIR__ . '/../partials/admin_sidebar.php'; ?><div class="main-panel"><?php require __DIR__ . '/../partials/header.php'; ?>
<main class="content">
<h1>Admin Dashboard</h1><p class="muted">Monitor attendance activity and staff status.</p>
<section class="grid stats-grid">
<div class="card"><span>Currently Onsite</span><strong><?= $stats['currentlyOnsite'] ?></strong></div><div class="card"><span>Total Staff Today</span><strong><?= $stats['totalStaffToday'] ?></strong></div><div class="card"><span>Pending Sync</span><strong><?= $stats['pendingSync'] ?></strong></div><div class="card"><span>Total Events</span><strong><?= $stats['totalEvents'] ?></strong></div>
</section>
<section class="grid two-col"><div class="card"><h2>Recent Events</h2><table><tbody><?php foreach ($events as $event): ?><tr><td><?= htmlspecialchars($event['userName']) ?></td><td><?= strtoupper($event['type']) ?></td><td><?= htmlspecialchars($event['timestamp']) ?></td></tr><?php endforeach; ?></tbody></table></div><div class="card"><h2>Onsite Staff</h2><?php foreach ($onsiteStaff as $staff): ?><p><strong><?= htmlspecialchars($staff['name']) ?></strong><br><span class="muted"><?= htmlspecialchars($staff['employeeId']) ?> clocked in <?= htmlspecialchars($staff['clockedInAt']) ?></span></p><?php endforeach; ?></div></section>
</main></div></div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

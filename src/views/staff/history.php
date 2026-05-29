<?php ob_start(); ?>
<div class="app-shell"><?php require __DIR__ . '/../partials/staff_sidebar.php'; ?><div class="main-panel"><?php require __DIR__ . '/../partials/header.php'; ?>
<main class="content">
<h1>History</h1><p class="muted">View recent attendance records.</p>
<section class="card"><table><thead><tr><th>Name</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach ($events as $event): ?><tr><td><?= htmlspecialchars($event['userName']) ?></td><td><?= htmlspecialchars($event['type']) ?></td><td><?= htmlspecialchars($event['timestamp']) ?></td></tr><?php endforeach; ?>
</tbody></table></section>
</main></div></div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

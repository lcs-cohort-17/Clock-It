<?php ob_start(); ?>
<div class="app-shell"><?php require __DIR__ . '/../partials/staff_sidebar.php'; ?><div class="main-panel"><?php require __DIR__ . '/../partials/header.php'; ?>
<main class="content"><h1>Calendar</h1><section class="card calendar"><h2><?= date('F Y') ?></h2><p class="muted">Leave dates, attendance days and workplace events can be shown here.</p></section></main>
</div></div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

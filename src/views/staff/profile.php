<?php $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); ob_start(); ?>
<div class="app-shell"><?php require __DIR__ . '/../partials/staff_sidebar.php'; ?><div class="main-panel"><?php require __DIR__ . '/../partials/header.php'; ?>
<main class="content">
<h1>Profile</h1><p class="muted">User Profile - Your account information</p>
<section class="grid two-col">
<div class="card"><h2>Profile Workspace</h2><p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p><p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p><p><strong>Employee ID:</strong> <?= htmlspecialchars($user['employeeId']) ?></p><p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p></div>
<div class="card"><h2>Change Password</h2><?php if ($flash): ?><div class="alert <?= $flash['valid'] ? 'success' : 'error' ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?><form class="form" method="post" action="<?= htmlspecialchars(clockit_route('/profile/password'), ENT_QUOTES, 'UTF-8') ?>"><label>Current Password<input type="password" name="current_password"></label><label>New Password<input type="password" name="new_password"></label><label>Confirm New Password<input type="password" name="confirm_password"></label><button data-testid="password-submit">Update Password</button></form></div>
</section>
<section class="card"><h2>Support Workspace</h2><p class="muted">Need help? Contact your administrator for account and attendance support.</p></section>
</main></div></div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

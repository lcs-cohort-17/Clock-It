<?php
/**
 * Header Component
 * Shared top navbar for staff and admin areas.
 */
$roleLabel = ($_SESSION['user_role'] ?? 'staff') === 'admin' ? 'Admin' : 'Staff';
?>
<header class="app-header">
  <div class="app-header-main">
    <button class="sidebar-menu-toggle" type="button" aria-label="Open navigation" aria-controls="appSidebar" aria-expanded="false" data-sidebar-toggle>
      <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <div>
      <h2>Clock-It</h2>
      <div class="welcome-text">
        Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?> · <?= $roleLabel ?>
      </div>
    </div>
  </div>
  <div class="app-header-actions">
    <?php include __DIR__ . '/theme-toggle.php'; ?>
  </div>
</header>

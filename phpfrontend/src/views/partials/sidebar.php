<?php
/**
 * Sidebar Component
 * Role-aware navigation for staff and admin areas.
 */
$role = $_SESSION['user_role'] ?? 'staff';
$isAdmin = $role === 'admin';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$navItems = $isAdmin
  ? [
      ['href' => '/admin-dashboard', 'icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
      ['href' => '/admin-dashboard/users', 'icon' => 'bi-people', 'label' => 'User Management'],
      ['href' => '/admin-dashboard/attendance', 'icon' => 'bi-clock-history', 'label' => 'Attendance Log'],
      ['href' => '/admin-dashboard/qr-generator', 'icon' => 'bi-qr-code-scan', 'label' => 'QR Generator'],
      ['href' => '/admin-dashboard/settings', 'icon' => 'bi-gear', 'label' => 'Settings'],
    ]
  : [
      ['href' => '/staff-dashboard', 'icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
      ['href' => '/scan-qr', 'icon' => 'bi-qr-code-scan', 'label' => 'Scan QR Code'],
      ['href' => '/history', 'icon' => 'bi-clock-history', 'label' => 'Attendance History'],
      ['href' => '/calendar', 'icon' => 'bi-calendar3', 'label' => 'Calendar'],
      ['href' => '/profile', 'icon' => 'bi-person-circle', 'label' => 'Profile'],
    ];

$isActive = static function (string $href) use ($currentPath): bool {
  return str_ends_with($currentPath, $href) || $currentPath === $href;
};
?>
<div class="sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>
<aside class="app-sidebar" id="appSidebar" aria-label="Primary navigation">
  <div class="sidebar-brand-row">
    <div>
    <h1>Clock-It</h1>
    <p><?= $isAdmin ? 'Admin Panel' : 'Attendance System' ?></p>
    </div>
    <button class="sidebar-close" type="button" aria-label="Close navigation" data-sidebar-close>
      <i class="bi bi-x-lg" aria-hidden="true"></i>
    </button>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($navItems as $item): ?>
      <a
        href="<?= route_url($item['href']) ?>"
        class="sidebar-nav-link<?= $isActive($item['href']) ? ' active' : '' ?>"
        title="<?= htmlspecialchars($item['label']) ?>"
        data-sidebar-close
      >
        <i class="bi <?= htmlspecialchars($item['icon']) ?>" aria-hidden="true"></i>
        <span><?= htmlspecialchars($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <button class="sidebar-logout" x-on:click="logoutUser()" type="button">
    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
    <span>Sign Out</span>
  </button>
</aside>

<script>
function logoutUser() {
  if (confirm('Are you sure you want to sign out?')) {
    window.location.href = '<?= route_url('/logout') ?>';
  }
}
</script>

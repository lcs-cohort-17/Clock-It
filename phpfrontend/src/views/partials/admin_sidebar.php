<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if (($basePath ?? '') !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
}
function navLinkClass(string $route, string $currentPath): string {
    return $route === $currentPath ? 'nav-link active' : 'nav-link text-white';
}
// PHP fallback – will be replaced by the script below
$displayName = $user['name'] ?? 'Loading…';
$displayEmail = $user['email'] ?? '';
?>

<aside id="sidebar" class="bg-dark text-white vh-100 p-3 d-flex flex-column" style="width:280px;">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Clock It</h2>
        <small class="text-light">Attendance Suite</small>
    </div>
    <ul class="nav flex-column gap-2">
        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard')) ?>" class="<?= navLinkClass('/admin-dashboard', $currentPath) ?>">
                <i class="bi bi-grid me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/users')) ?>" class="<?= navLinkClass('/admin-dashboard/users', $currentPath) ?>">
                <i class="bi bi-people me-2"></i> User Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/attendance')) ?>" class="<?= navLinkClass('/admin-dashboard/attendance', $currentPath) ?>">
                <i class="bi bi-clock-history me-2"></i> Attendance Logs
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/settings')) ?>" class="<?= navLinkClass('/admin-dashboard/settings', $currentPath) ?>">
                <i class="bi bi-gear me-2"></i> Settings
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/qr-generator')) ?>"
               class="<?= navLinkClass('/admin-dashboard/qr-generator', $currentPath) ?>">
                <i class="bi bi-qr-code me-2" aria-hidden="true"></i>
                QR Generator
            </a>
        </li>
    </ul>
    <div class="mt-auto pt-4">
        <h6 id="sidebar-name"><?= htmlspecialchars($displayName) ?></h6>
        <small id="sidebar-email"><?= htmlspecialchars($displayEmail) ?></small>
        <a href="<?= e(app_url('/logout')) ?>" class="btn btn-outline-light w-100 mt-3">
            <i class="bi bi-box-arrow-right me-2"></i> Sign Out
        </a>
    </div>
</aside>

<script>
(function() {
    function updateSidebar() {
        // Try to get the store; it may not exist yet
        if (typeof Alpine === 'undefined' || !Alpine.store('app')) return false;
        var user = Alpine.store('app').user;
        if (!user || !user.first_name) return false;
        
        var nameEl = document.getElementById('sidebar-name');
        var emailEl = document.getElementById('sidebar-email');
        if (nameEl) nameEl.textContent = (user.first_name || '') + ' ' + (user.last_name || '');
        if (emailEl) emailEl.textContent = user.email || '';
        return true;
    }
    
    // Try immediately
    if (!updateSidebar()) {
        // If not ready, poll every 100ms until it is
        var interval = setInterval(function() {
            if (updateSidebar()) clearInterval(interval);
        }, 100);
        // Stop after 10 seconds
        setTimeout(function() { clearInterval(interval); }, 10000);
    }
})();
</script>

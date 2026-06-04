<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if (($basePath ?? '') !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
}
function staffNavLinkClass(string $route, string $currentPath): string {
    return $route === $currentPath ? 'nav-link active' : 'nav-link text-white';
}
?>
<aside id="sidebar" class="bg-dark text-white vh-100 p-3 d-flex flex-column">
    <div class="mb-4 sidebar-header">
        <div class="sidebar-brand">
            <h2 class="fw-bold mb-1 sidebar-brand-text">Clock It</h2>
            <small class="text-light sidebar-text">Staff Portal</small>
        </div>
        <button type="button" class="sidebar-close" data-sidebar-close aria-label="Close navigation">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>

    <ul class="nav flex-column gap-2">
        <li class="nav-item">
            <a href="<?= e(app_url('/staff-dashboard')) ?>"
               class="<?= staffNavLinkClass('/staff-dashboard', $currentPath) ?>">
                <i class="bi bi-grid me-2" aria-hidden="true"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= e(app_url('/scan-qr')) ?>"
               class="<?= staffNavLinkClass('/scan-qr', $currentPath) ?>">
                <i class="bi bi-qr-code-scan me-2" aria-hidden="true"></i>
                <span class="sidebar-text">Scan QR</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= e(app_url('/history')) ?>"
               class="<?= staffNavLinkClass('/history', $currentPath) ?>">
                <i class="bi bi-clock-history me-2" aria-hidden="true"></i>
                <span class="sidebar-text">History</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= e(app_url('/profile')) ?>"
               class="<?= staffNavLinkClass('/profile', $currentPath) ?>">
                <i class="bi bi-person me-2" aria-hidden="true"></i>
                <span class="sidebar-text">Profile</span>
            </a>
        </li>
    </ul>

    <div class="mt-auto pt-4">
        <div class="d-flex align-items-center gap-3">
            <div class="staff-sidebar-avatar" id="sidebar-avatar" aria-hidden="true">
                <!-- filled by script -->
            </div>
            <div class="min-width-0 sidebar-user-info">
                <h6 class="mb-1 text-truncate" id="sidebar-name">Loading…</h6>
                <small class="d-block text-truncate" id="sidebar-email"></small>
            </div>
        </div>
        <a href="<?= e(app_url('/logout')) ?>" class="btn btn-outline-light w-100 mt-3">
            <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>
            <span class="sidebar-text">Sign Out</span>
        </a>
    </div>
</aside>

<script>
(function() {
    function updateStaffSidebar() {
        if (typeof Alpine === 'undefined' || !Alpine.store('app')) return false;
        const user = Alpine.store('app').user;
        if (!user || !user.first_name) return false;

        const fullName = (user.first_name || '') + ' ' + (user.last_name || '');
        document.getElementById('sidebar-name').textContent = fullName.trim();
        document.getElementById('sidebar-email').textContent = user.email || '';
        const initials = fullName.split(' ').map(w => w[0] || '').join('').toUpperCase().substring(0, 2);
        document.getElementById('sidebar-avatar').textContent = initials;
        return true;
    }
    if (!updateStaffSidebar()) {
        var interval = setInterval(function() {
            if (updateStaffSidebar()) clearInterval(interval);
        }, 100);
        setTimeout(function() { clearInterval(interval); }, 10000);
    }
})();
</script>
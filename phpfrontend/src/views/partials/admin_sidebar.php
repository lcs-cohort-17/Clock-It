<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

if (($basePath ?? '') !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
}

function navLinkClass(string $route, string $currentPath): string
{
    return $route === $currentPath
        ? 'nav-link active'
        : 'nav-link text-white';
}

// Get real user from session or store
$displayName = $user['name'] ?? 'User';
$displayEmail = $user['email'] ?? '';
?>

<aside id="sidebar" class="bg-dark text-white vh-100 p-3 d-flex flex-column" style="width:280px;">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Clock It</h2>
        <small class="text-light">Attendance Suite</small>
    </div>

    <ul class="nav flex-column gap-2">
        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard')) ?>"
               class="<?= navLinkClass('/admin-dashboard', $currentPath) ?>">
                <i class="bi bi-grid me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/users')) ?>"
               class="<?= navLinkClass('/admin-dashboard/users', $currentPath) ?>">
                <i class="bi bi-people me-2"></i> User Management
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/attendance')) ?>"
               class="<?= navLinkClass('/admin-dashboard/attendance', $currentPath) ?>">
                <i class="bi bi-clock-history me-2"></i> Attendance Logs
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/settings')) ?>"
               class="<?= navLinkClass('/admin-dashboard/settings', $currentPath) ?>">
                <i class="bi bi-gear me-2"></i> Settings
            </a>
        </li>
    </ul>

<div class="mt-auto pt-4" x-data x-init="
    // Update sidebar with store data when available
    $watch('$store.app.user', user => {
        if (user) {
            document.getElementById('sidebar-name').textContent = user.first_name + ' ' + user.last_name
            document.getElementById('sidebar-email').textContent = user.email
        }
    })
">
    <h6 id="sidebar-name"><?= htmlspecialchars($displayName) ?></h6>
    <small id="sidebar-email"><?= htmlspecialchars($displayEmail) ?></small>
    <a href="<?= e(app_url('/logout')) ?>" class="btn btn-outline-light w-100 mt-3">
        <i class="bi bi-box-arrow-right me-2"></i> Sign Out
    </a>
</div>
</aside>
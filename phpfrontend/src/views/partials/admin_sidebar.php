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

?>

<aside id="sidebar" class="bg-dark text-white vh-100 p-3 d-flex flex-column sidebar-collapsed">

    <div class="mb-4 sidebar-header">
        <div class="sidebar-brand">
            <h2 class="fw-bold mb-1 sidebar-brand-text">Clock It</h2>
            <small class="text-light sidebar-text">Attendance Suite</small>
        </div>
        <button
            type="button"
            class="sidebar-close"
            data-sidebar-close
            aria-label="Close navigation"
        >
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>

    <ul class="nav flex-column gap-2">

        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard')) ?>"
               class="<?= navLinkClass('/admin-dashboard', $currentPath) ?>">
                <i class="bi bi-grid me-2" aria-hidden="true"></i>
                Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/qr-generator')) ?>"
               class="<?= navLinkClass('/admin-dashboard/qr-generator', $currentPath) ?>">
                <i class="bi bi-qr-code me-2" aria-hidden="true"></i>
                QR Generator
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/users')) ?>"
               class="<?= navLinkClass('/admin-dashboard/users', $currentPath) ?>">
                <i class="bi bi-people me-2" aria-hidden="true"></i>
                User Management
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/attendance')) ?>"
               class="<?= navLinkClass('/admin-dashboard/attendance', $currentPath) ?>">
                <i class="bi bi-clock-history me-2" aria-hidden="true"></i>
                Attendance Logs
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/leave-requests')) ?>"
               class="<?= navLinkClass('/admin-dashboard/leave-requests', $currentPath) ?>">
                <i class="bi bi-person-check me-2" aria-hidden="true"></i>
                Leave Requests
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= e(app_url('/admin-dashboard/settings')) ?>"
               class="<?= navLinkClass('/admin-dashboard/settings', $currentPath) ?>">
                <i class="bi bi-gear me-2" aria-hidden="true"></i>
                Settings
            </a>
        </li>

    </ul>

    <div class="mt-auto pt-4">

        <h6><?= htmlspecialchars($user['name']) ?></h6>

        <small><?= htmlspecialchars($user['email']) ?></small>

        <a href="<?= e(app_url('/logout')) ?>" class="btn btn-outline-light w-100 mt-3">
            <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>
            Sign Out
        </a>

    </div>

</aside>

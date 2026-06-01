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

<aside id="sidebar" class="bg-dark text-white vh-100 p-3" style="width:280px;">

    <div class="mb-4">
        <h2 class="fw-bold mb-1">Clock It</h2>
        <small class="text-light">Attendance Suite</small>
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

    </div>

</aside>

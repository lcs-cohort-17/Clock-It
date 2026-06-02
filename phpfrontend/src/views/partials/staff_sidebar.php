<?php

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

if (($basePath ?? '') !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
}

function staffNavLinkClass(string $route, string $currentPath): string
{
    return $route === $currentPath
        ? 'nav-link active'
        : 'nav-link text-white';
}
?>

<aside id="sidebar" class="bg-dark text-white vh-100 p-3 d-flex flex-column" style="width:280px;">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Clock It</h2>
        <small class="text-light">Attendance Suite</small>
    </div>

    <ul class="nav flex-column gap-2">
        <li class="nav-item">
            <a href="<?= e(app_url('/staff-dashboard')) ?>"
               class="<?= staffNavLinkClass('/staff-dashboard', $currentPath) ?>">
                <i class="bi bi-grid me-2" aria-hidden="true"></i>
                Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= e(app_url('/scan-qr')) ?>"
               class="<?= staffNavLinkClass('/scan-qr', $currentPath) ?>">
                <i class="bi bi-qr-code-scan me-2" aria-hidden="true"></i>
                Scan QR
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= e(app_url('/history')) ?>"
               class="<?= staffNavLinkClass('/history', $currentPath) ?>">
                <i class="bi bi-clock-history me-2" aria-hidden="true"></i>
                History
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= e(app_url('/profile')) ?>"
               class="<?= staffNavLinkClass('/profile', $currentPath) ?>">
                <i class="bi bi-person me-2" aria-hidden="true"></i>
                Profile
            </a>
        </li>
    </ul>

    <div class="mt-auto pt-4">
        <h6><?= e($user['name']) ?></h6>
        <small><?= e($user['email']) ?></small>
        <a href="<?= e(app_url('/logout')) ?>" class="btn btn-outline-light w-100 mt-3">
            <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>
            Sign Out
        </a>
    </div>
</aside>

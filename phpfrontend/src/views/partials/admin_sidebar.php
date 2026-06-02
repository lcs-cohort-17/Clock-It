<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

$adminNavItems = [
    '/admin-dashboard' => 'Dashboard',
    '/admin-dashboard/attendance' => 'Attendance',
    '/admin-dashboard/qr-generator' => 'QR Generator',
    '/admin/settings' => 'Settings',
];
?>
<aside id="sidebar" class="sidebar admin-sidebar">
    <button type="button" class="close-btn" onclick="closeSidebar()" aria-label="Close navigation">&times;</button>
    <div class="brand">Clock-It</div>
    <p class="muted" style="color:#cbd5e1;margin:0;">Admin portal</p>
    <nav aria-label="Admin navigation">
        <?php foreach ($adminNavItems as $href => $label): ?>
            <?php $isActive = $currentPath === $href || ($href === '/admin-dashboard' && $currentPath === '/admin/dashboard'); ?>
            <a href="<?= htmlspecialchars($href) ?>"<?= $isActive ? ' class="active" aria-current="page"' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

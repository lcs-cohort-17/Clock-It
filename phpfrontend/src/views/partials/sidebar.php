<?php
$navRole = strtolower((string) ($user['accountRole'] ?? 'staff'));
$items = nav_items($navRole);
?>
<aside class="app-sidebar" x-bind:class="{ 'is-collapsed': sidebarCollapsed, 'is-open': mobileNavOpen }">
    <div class="brand-block">
        <a href="<?= route_url($navRole === 'admin' ? '/admin/dashboard' : '/staff/dashboard') ?>" class="brand-mark" aria-label="Clock-It home">
            <span class="brand-symbol"><?= ui_icon('spark') ?></span>
            <span class="brand-copy">
                <span><?= e($brand['name']) ?></span>
                <small><?= e($brand['tagline']) ?></small>
            </span>
        </a>
        <button class="icon-button sidebar-pin d-none d-lg-inline-flex" type="button" title="Collapse navigation" x-on:click="sidebarCollapsed = !sidebarCollapsed">
            <?= ui_icon('menu') ?>
        </button>
    </div>

    <nav class="app-nav" aria-label="<?= e(ucfirst($navRole)) ?> navigation">
        <?php foreach ($items as $item): ?>
            <?php $active = $currentPath === $item['path']; ?>
            <a href="<?= route_url($item['path']) ?>" class="app-nav-link <?= $active ? 'active' : '' ?>" title="<?= e($item['label']) ?>">
                <?= ui_icon($item['icon']) ?>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-status">
        <div class="sidebar-status-dot"></div>
        <div>
            <strong>Live sync</strong>
            <span>Mock state active</span>
        </div>
    </div>

    <div class="sidebar-profile">
        <div class="avatar"><?= e($user['avatar'] ?? 'HR') ?></div>
        <div class="sidebar-profile-copy">
            <strong><?= e($user['name'] ?? 'Demo User') ?></strong>
            <span><?= e($user['jobTitle'] ?? ucfirst($navRole)) ?></span>
        </div>
    </div>

    <a class="logout-link" href="<?= route_url('/logout') ?>">
        <?= ui_icon('logout') ?>
        <span>Sign out</span>
    </a>
</aside>
<button class="nav-backdrop" type="button" aria-label="Close navigation" x-on:click="mobileNavOpen = false"></button>

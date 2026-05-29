<?php
$pageTitle = $pageTitle ?? $title ?? 'Admin';
$userName = $user['name'] ?? 'Demo Admin';
?>
<header class="topbar">
    <button type="button" class="hamburger" onclick="openSidebar()" aria-label="Open navigation">&#9776;</button>
    <div>
        <strong><?= htmlspecialchars($pageTitle) ?></strong>
    </div>
    <div class="topbar-right">
        <span class="pill online">Online</span>
        <span class="pill"><?= htmlspecialchars($userName) ?></span>
    </div>
</header>

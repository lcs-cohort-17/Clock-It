<header class="topbar">
    <button class="hamburger" type="button" onclick="openSidebar()" aria-label="Open navigation">&#9776;</button>
    <div></div>
    <div class="topbar-right">
        <span class="pill online">Online</span>
        <span class="pill"><?= htmlspecialchars(ucfirst($user['role'] ?? 'Guest')) ?></span>
    </div>
</header>

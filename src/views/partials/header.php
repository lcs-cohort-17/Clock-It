<header class="topbar">
    <button class="hamburger" type="button" onclick="openSidebar()">☰</button>
    <div></div>
    <div class="topbar-right">
        <span class="pill online">Online</span>
        <span class="pill"><?= htmlspecialchars(ucfirst($user['role'] ?? 'Guest')) ?></span>
    </div>
</header>

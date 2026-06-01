<aside id="sidebar" class="sidebar">
    <button class="close-btn" type="button" onclick="closeSidebar()" aria-label="Close navigation">&times;</button>
    <div class="brand">Clock-It</div>
    <p class="muted"><?= htmlspecialchars($user['name']) ?><br><?= htmlspecialchars($user['employeeId']) ?></p>
    <nav>
        <a href="<?= htmlspecialchars(clockit_route('/staff-dashboard'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
        <a href="<?= htmlspecialchars(clockit_route('/scan-qr'), ENT_QUOTES, 'UTF-8') ?>">Scan QR</a>
        <a href="<?= htmlspecialchars(clockit_route('/history'), ENT_QUOTES, 'UTF-8') ?>">History</a>
        <a href="<?= htmlspecialchars(clockit_route('/calendar'), ENT_QUOTES, 'UTF-8') ?>">Calendar</a>
        <a href="<?= htmlspecialchars(clockit_route('/profile'), ENT_QUOTES, 'UTF-8') ?>">Profile</a>
        <a href="<?= htmlspecialchars(clockit_route('/logout'), ENT_QUOTES, 'UTF-8') ?>">Logout</a>
    </nav>
</aside>

<aside id="sidebar" class="sidebar admin-sidebar">
    <button class="close-btn" type="button" onclick="closeSidebar()" aria-label="Close navigation">&times;</button>
    <div class="brand">Clock-It Admin</div>
    <p class="muted"><?= htmlspecialchars($user['name']) ?><br><?= htmlspecialchars($user['employeeId']) ?></p>
    <nav>
        <a href="<?= htmlspecialchars(clockit_route('/admin-dashboard'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
        <a href="<?= htmlspecialchars(clockit_route('/admin-dashboard/attendance'), ENT_QUOTES, 'UTF-8') ?>">Attendance</a>
        <a href="<?= htmlspecialchars(clockit_route('/admin-dashboard/qr-generator'), ENT_QUOTES, 'UTF-8') ?>">QR Generator</a>
        <a href="<?= htmlspecialchars(clockit_route('/admin-dashboard/users'), ENT_QUOTES, 'UTF-8') ?>">Users</a>
        <a href="<?= htmlspecialchars(clockit_route('/admin-dashboard/settings'), ENT_QUOTES, 'UTF-8') ?>">Settings</a>
        <a href="<?= htmlspecialchars(clockit_route('/logout'), ENT_QUOTES, 'UTF-8') ?>">Logout</a>
    </nav>
</aside>

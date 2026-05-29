<aside id="sidebar" class="sidebar admin-sidebar">
    <button class="close-btn" type="button" onclick="closeSidebar()">&times;</button>
    <div class="brand">Clock-It Admin</div>
    <p class="muted"><?= htmlspecialchars($user['name']) ?><br><?= htmlspecialchars($user['employeeId']) ?></p>
    <nav>
        <a href="/admin-dashboard">Dashboard</a>
        <a href="/admin-dashboard/attendance">Attendance</a>
        <a href="/admin-dashboard/qr-generator">QR Generator</a>
        <a href="/admin-dashboard/users">Users</a>
        <a href="/admin-dashboard/settings">Settings</a>
        <a href="/logout">Logout</a>
    </nav>
</aside>

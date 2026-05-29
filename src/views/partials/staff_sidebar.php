<aside id="sidebar" class="sidebar">
    <button class="close-btn" type="button" onclick="closeSidebar()">&times;</button>
    <div class="brand">Clock-It</div>
    <p class="muted"><?= htmlspecialchars($user['name']) ?><br><?= htmlspecialchars($user['employeeId']) ?></p>
    <nav>
        <a href="/staff-dashboard">Dashboard</a>
        <a href="/scan-qr">Scan QR</a>
        <a href="/history">History</a>
        <a href="/calendar">Calendar</a>
        <a href="/profile">Profile</a>
        <a href="/logout">Logout</a>
    </nav>
</aside>

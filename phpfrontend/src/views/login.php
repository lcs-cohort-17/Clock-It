<?php ob_start(); ?>
<main class="login-page">
    <section class="login-card">
        <div>
            <p class="badge">Attendance Management</p>
            <h1>Welcome to Clock-It</h1>
            <p class="muted">Sign in with your email or employee ID to access your dashboard.</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= route_url('/login') ?>" class="form">
            <label> Email or Employee ID
                <input name="identifier" required placeholder="staff@clockit.app">
            </label>
            <label> Password
                <input name="password" type="password" required placeholder="password123">
            </label>
            <button type="submit">Login</button>
        </form>
        <div class="demo-grid">
            <div><strong>Staff:</strong> staff@clockit.app / password123</div>
            <div><strong>Admin:</strong> admin@clockit.app / admin123</div>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/layouts/app.php'; ?>

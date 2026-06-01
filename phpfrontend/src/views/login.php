<?php ob_start(); ?>
<main class="login-page" x-data="{ mode: 'login' }" :class="mode === 'login' ? 'login-mode' : 'signup-mode'">
    <div class="login-line-field" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="login-theme-toggle">
        <?php include __DIR__ . '/partials/theme-toggle.php'; ?>
    </div>
    <section class="login-card">
        <div>
            <p class="badge">Attendance Management</p>
            <h1>Welcome to Clock-It</h1>
            <p class="muted" x-text="mode === 'login' ? 'Sign in with your email or employee ID to access your dashboard.' : 'Create an account to start tracking attendance with Clock-It.'"></p>
        </div>
        <div class="auth-tabs" role="tablist" aria-label="Authentication options">
            <button type="button" :class="{ active: mode === 'login' }" @click="mode = 'login'" role="tab" :aria-selected="mode === 'login'">
                Login
            </button>
            <button type="button" :class="{ active: mode === 'signup' }" @click="mode = 'signup'" role="tab" :aria-selected="mode === 'signup'">
                Sign Up
            </button>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= route_url('/auth/google') ?>" class="auth-google-form">
            <input type="hidden" name="auth_mode" :value="mode">
            <button class="google-auth-btn" type="submit">
                <i class="bi bi-google" aria-hidden="true"></i>
                <span x-text="mode === 'login' ? 'Sign in with Google' : 'Sign up with Google'"></span>
            </button>
        </form>
        <div class="auth-divider"><span>or</span></div>
        <form method="post" action="<?= route_url('/login') ?>" class="form">
            <input type="hidden" name="auth_mode" :value="mode">
            <label x-show="mode === 'signup'" x-cloak>
                <span><i class="bi bi-person me-1" aria-hidden="true"></i>Full Name</span>
                <input name="name" placeholder="Demo User" :required="mode === 'signup'">
            </label>
            <label>
                <span><i class="bi bi-person-badge me-1" aria-hidden="true"></i>Email or Employee ID</span>
                <input name="identifier" required placeholder="staff@clockit.app">
            </label>
            <label>
                <span><i class="bi bi-lock me-1" aria-hidden="true"></i>Password</span>
                <input name="password" type="password" required placeholder="password123">
            </label>
            <label x-show="mode === 'signup'" x-cloak>
                <span><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>Confirm Password</span>
                <input name="password_confirmation" type="password" placeholder="password123" :required="mode === 'signup'">
            </label>
            <button type="submit">
                <i class="bi me-1" :class="mode === 'login' ? 'bi-box-arrow-in-right' : 'bi-person-plus'" aria-hidden="true"></i>
                <span x-text="mode === 'login' ? 'Login' : 'Create Account'"></span>
            </button>
        </form>
        <div class="demo-grid">
            <div><strong>Staff:</strong> staff@clockit.app / password123</div>
            <div><strong>Admin:</strong> admin@clockit.app / admin123</div>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/layouts/app.php'; ?>

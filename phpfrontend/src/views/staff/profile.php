<?php ob_start(); ?>
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Staff</p>
            <h1 class="h3 mb-0">Profile</h1>
        </div>
        <a class="btn btn-outline-secondary" href="/staff-dashboard">Back</a>
    </div>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash']['message']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <section class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Account</h2>
                    <dl class="mb-0">
                        <dt>Name</dt>
                        <dd><?= htmlspecialchars($user['name']) ?></dd>
                        <dt>Email</dt>
                        <dd><?= htmlspecialchars($user['email']) ?></dd>
                        <dt>Employee ID</dt>
                        <dd class="mb-0"><?= htmlspecialchars($user['employeeId']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <form action="/profile/password" method="post" class="card h-100">
                <div class="card-body d-grid gap-3">
                    <h2 class="h5">Password</h2>
                    <input class="form-control" type="password" name="password" placeholder="New password" required>
                    <button class="btn btn-primary" type="submit">Save password</button>
                </div>
            </form>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

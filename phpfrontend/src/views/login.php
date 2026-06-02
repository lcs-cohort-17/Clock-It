<main class="auth-shell" x-data="loginPanel()">
    <section class="auth-visual">
        <a href="<?= route_url('/login') ?>" class="brand-mark">
            <span class="brand-symbol"><?= ui_icon('spark') ?></span>
            <span class="brand-copy">
                <span><?= e($brand['name']) ?></span>
                <small><?= e($brand['tagline']) ?></small>
            </span>
        </a>

        <h1>Premium attendance operations for modern HR teams.</h1>
        <p class="mt-3">A dark, enterprise-ready workspace for live attendance, QR governance, employee administration, schedules, reviews, and export-ready audit data.</p>

        <div class="auth-metrics">
            <div class="auth-metric">
                <strong>2</strong>
                <span>onsite now</span>
            </div>
            <div class="auth-metric">
                <strong>6</strong>
                <span>events today</span>
            </div>
            <div class="auth-metric">
                <strong>1</strong>
                <span>review item</span>
            </div>
        </div>
    </section>

    <section class="auth-card">
        <div>
            <span class="badge-soft teal">Secure demo access</span>
            <h2 class="mt-3">Sign in to Clock-It</h2>
            <p>Choose a role and continue into the server-rendered PHP application.</p>
        </div>

        <div class="segmented" role="group" aria-label="Demo role">
            <button type="button" x-bind:class="{ active: role === 'staff' }" x-on:click="setRole('staff')">Staff</button>
            <button type="button" x-bind:class="{ active: role === 'admin' }" x-on:click="setRole('admin')">Admin</button>
        </div>

        <form class="row g-3" method="post" action="<?= route_url('/login') ?>" x-on:submit="submit">
            <div class="col-12">
                <label class="form-label" for="identifier">Email</label>
                <input class="form-control" id="identifier" name="identifier" type="email" autocomplete="email" x-model="identifier" required>
            </div>

            <div class="col-12">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" x-model="password" required>
            </div>

            <template x-if="error">
                <div class="col-12">
                    <div class="feedback-panel danger" x-text="error"></div>
                </div>
            </template>

            <div class="col-12">
                <button class="btn btn-primary w-100" type="submit">
                    <?= ui_icon('shield') ?>
                    <span>Enter workspace</span>
                </button>
            </div>
        </form>

        <div class="mini-row">
            <div>
                <strong>Demo staff</strong>
                <span>Tentsaolo Khoza</span>
            </div>
            <span class="badge-soft violet">EMP-108</span>
        </div>
    </section>
</main>

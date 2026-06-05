<header class="app-header border-bottom p-3">
    <div class="d-flex justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <button
                type="button"
                class="sidebar-toggle"
                data-sidebar-toggle
                aria-controls="sidebar"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>

            <button
                type="button"
                class="app-back-button"
                aria-label="Go back"
                title="Go back"
                data-app-back
                data-dashboard-url="<?= e(app_url($isAdminDashboard ? '/admin-dashboard' : '/staff-dashboard')) ?>"
                data-dashboard-path="<?= e($isAdminDashboard ? '/admin-dashboard' : '/staff-dashboard') ?>"
            >
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </button>
        </div>

        <div class="d-flex justify-content-end align-items-center gap-2 gap-sm-3">
            <button
                type="button"
                class="theme-toggle"
                data-theme-toggle
                aria-label="Switch to dark mode"
                aria-pressed="false"
                title="Switch to dark mode"
            >
                <i class="bi bi-sun-fill theme-icon theme-icon-sun" aria-hidden="true"></i>
                <span class="theme-toggle-track" aria-hidden="true">
                    <span class="theme-toggle-thumb"></span>
                </span>
                <i class="bi bi-moon-stars-fill theme-icon theme-icon-moon" aria-hidden="true"></i>
            </button>

            <span class="badge bg-secondary">
                <?= $isAdminDashboard ? 'Admin' : 'Staff' ?>
            </span>
        </div>
    </div>
</header>

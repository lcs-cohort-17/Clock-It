<?php ob_start(); ?>
<main class="container py-4">
    <section class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Admin Dashboard</h1>
            <p class="text-body-secondary mb-0">Manage attendance and export clock-event logs.</p>
        </div>

        <div
            x-data="adminAttendanceExport($el.dataset.apiEndpoint)"
            data-api-endpoint="/api/attendance/clock-events"
            class="d-flex flex-column align-items-start align-items-md-end gap-2"
        >
            <button
                type="button"
                class="btn btn-primary d-inline-flex align-items-center gap-2"
                x-on:click="exportLogs"
                x-bind:disabled="loading"
                x-bind:aria-busy="loading.toString()"
            >
                <span x-show="!loading" aria-hidden="true">&darr;</span>
                <span x-show="loading" x-cloak class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                <span x-text="loading ? 'Exporting...' : 'Export Logs to CSV'">Export Logs to CSV</span>
            </button>

            <div
                x-show="error"
                x-cloak
                class="alert alert-danger py-2 px-3 mb-0"
                role="alert"
                x-text="error"
            ></div>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

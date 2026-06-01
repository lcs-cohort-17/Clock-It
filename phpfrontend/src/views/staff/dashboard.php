<?php ob_start(); ?>
<main class="container py-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Staff</p>
            <h1 class="h3 mb-1">Welcome, <?= htmlspecialchars($user['name']) ?></h1>
            <p class="text-body-secondary mb-0"><?= htmlspecialchars($user['employeeId']) ?> · <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <div class="d-flex gap-2">
            <a class="btn btn-primary" href="/scan-qr">Scan QR</a>
            <a class="btn btn-outline-secondary" href="/logout">Logout</a>
        </div>
    </div>

    <section class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Onsite</p>
                    <p class="h3 mb-0"><?= (int) $stats['currentlyOnsite'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Staff today</p>
                    <p class="h3 mb-0"><?= (int) $stats['totalStaffToday'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Pending sync</p>
                    <p class="h3 mb-0"><?= (int) $stats['pendingSync'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-body-secondary mb-1">Events</p>
                    <p class="h3 mb-0"><?= (int) $stats['totalEvents'] ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header bg-white">
            <h2 class="h5 mb-0">Recent activity</h2>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Staff member</th>
                        <th>Event</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['userName']) ?></td>
                            <td><?= htmlspecialchars($event['type']) ?></td>
                            <td><?= htmlspecialchars($event['timestamp']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

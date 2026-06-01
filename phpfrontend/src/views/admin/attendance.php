<?php ob_start(); ?>
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-primary fw-semibold small mb-2">Admin</p>
            <h1 class="h3 mb-0">Attendance</h1>
        </div>
        <a class="btn btn-outline-secondary" href="/admin-dashboard">Back</a>
    </div>

    <section class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Staff member</th>
                        <th>Event</th>
                        <th>Timestamp</th>
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

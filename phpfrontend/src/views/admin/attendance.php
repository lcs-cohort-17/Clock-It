<?php
$pageTitle = 'Attendance';
$user = $user ?? ['name' => 'Demo Admin'];
$events = $events ?? [];

ob_start();
?>
<div class="app-shell">
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>
        <main class="content">
            <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-slate-900">Attendance</h1>
                        <p class="mt-2 text-lg text-slate-600">Review recent clock-in and clock-out events.</p>
                    </div>
                    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <table>
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
                    </section>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';

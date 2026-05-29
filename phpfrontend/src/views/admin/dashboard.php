<?php
$pageTitle = 'Admin Dashboard';
$user = $user ?? ['name' => 'Demo Admin'];
$stats = $stats ?? [
    'currentlyOnsite' => 0,
    'totalStaffToday' => 0,
    'pendingSync' => 0,
    'totalEvents' => 0,
];
$events = $events ?? [];
$onsiteStaff = $onsiteStaff ?? [];

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
                        <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
                        <p class="mt-2 text-lg text-slate-600">Welcome to the Clock-It admin portal.</p>
                    </div>

                    <section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm text-slate-600">Currently onsite</p>
                            <strong class="mt-2 block text-2xl text-slate-900"><?= htmlspecialchars((string) $stats['currentlyOnsite']) ?></strong>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm text-slate-600">Staff today</p>
                            <strong class="mt-2 block text-2xl text-slate-900"><?= htmlspecialchars((string) $stats['totalStaffToday']) ?></strong>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm text-slate-600">Pending sync</p>
                            <strong class="mt-2 block text-2xl text-slate-900"><?= htmlspecialchars((string) $stats['pendingSync']) ?></strong>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm text-slate-600">Total events</p>
                            <strong class="mt-2 block text-2xl text-slate-900"><?= htmlspecialchars((string) $stats['totalEvents']) ?></strong>
                        </div>
                    </section>

                    <section class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="mb-4 text-lg font-semibold text-slate-900">Recent Activity</h2>
                            <div class="space-y-4">
                                <?php foreach ($events as $event): ?>
                                    <div class="flex justify-between border-b border-slate-100 pb-4">
                                        <div>
                                            <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars($event['userName']) ?></p>
                                            <p class="text-xs text-slate-500"><?= htmlspecialchars($event['timestamp']) ?></p>
                                        </div>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700"><?= htmlspecialchars($event['type']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="mb-4 text-lg font-semibold text-slate-900">Onsite Staff</h2>
                            <div class="space-y-4">
                                <?php foreach ($onsiteStaff as $person): ?>
                                    <div class="flex justify-between border-b border-slate-100 pb-4">
                                        <div>
                                            <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars($person['name']) ?></p>
                                            <p class="text-xs text-slate-500"><?= htmlspecialchars($person['employeeId']) ?></p>
                                        </div>
                                        <span class="text-sm text-slate-600"><?= htmlspecialchars($person['clockedInAt']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';

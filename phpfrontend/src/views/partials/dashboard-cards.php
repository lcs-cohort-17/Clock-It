<?php

$dashboardCards = [
    [
        'label' => 'Currently Onsite',
        'value' => $stats['currentlyOnsite'] ?? 0,
        'icon' => 'bi-people-fill',
        'color' => 'text-primary',
    ],
    [
        'label' => 'Clocked In',
        'value' => $stats['totalStaffToday'] ?? 0,
        'icon' => 'bi-clock-fill',
        'color' => 'text-success',
    ],
    [
        'label' => 'Pending Sync',
        'value' => $stats['pendingSync'] ?? 0,
        'icon' => 'bi-arrow-repeat',
        'color' => 'text-warning',
    ],
    [
        'label' => 'Total Events',
        'value' => $stats['totalEvents'] ?? 0,
        'icon' => 'bi-bar-chart-fill',
        'color' => 'text-danger',
    ],
];
?>

<div class="row g-4 dashboard-stats">
    <?php foreach ($dashboardCards as $card): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <section class="page-card dashboard-stat-card h-100 p-3">
                <div class="d-flex justify-content-between gap-3">
                    <h2 class="h6 mb-0"><?= e($card['label']) ?></h2>
                    <i class="bi <?= e($card['icon']) ?> <?= e($card['color']) ?> fs-3" aria-hidden="true"></i>
                </div>

                <p class="display-6 fw-bold mt-3 mb-0"><?= e((string) $card['value']) ?></p>
            </section>
        </div>
    <?php endforeach; ?>
</div>

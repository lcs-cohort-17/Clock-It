<?php

$firstName = explode(' ', $user['name'])[0];
$currentTime = date('H:i');
$isClockedIn = false;
$statusText = $isClockedIn ? 'Clocked In' : 'Clocked Out';
$locationStatus = $isClockedIn ? 'ONSITE' : 'OFFSITE';
?>

<section class="staff-dashboard container-fluid p-4 p-lg-5">
    <div class="staff-status-card page-card p-4 p-lg-5">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-4">
            <div>
                <p class="staff-eyebrow mb-2">Good morning, <?= e($firstName) ?></p>
                <h1 class="staff-status-title mb-2"><?= e($statusText) ?></h1>
                <span class="badge rounded-pill text-bg-light border">
                    You are currently <?= e($locationStatus) ?>
                </span>
                <p class="text-muted mt-3 mb-0">No QR scan recorded yet.</p>
            </div>

            <time class="staff-time" datetime="<?= e($currentTime) ?>">
                <?= e($currentTime) ?>
            </time>
        </div>

        <a href="<?= e(app_url('/scan-qr')) ?>"
           class="btn btn-main staff-scan-btn w-100 mt-4">
            <i class="bi bi-qr-code-scan" aria-hidden="true"></i>
            Scan QR
        </a>
    </div>

    <section class="page-card p-4 mt-4">
        <h2 class="h5 fw-bold mb-3">
            <i class="bi bi-geo-alt me-2" aria-hidden="true"></i>
            Today's activity
        </h2>
        <div class="staff-empty-state">No clock events today yet.</div>
    </section>

    <div class="row g-4 mt-1">
        <div class="col-12 col-md-4">
            <a href="<?= e(app_url('/history')) ?>" class="staff-action-card page-card">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                <h2>Calendar</h2>
                <p>View your schedule</p>
            </a>
        </div>

        <div class="col-12 col-md-4">
            <a href="<?= e(app_url('/history')) ?>" class="staff-action-card page-card">
                <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                <h2>Leave Requests</h2>
                <p>Review your requests</p>
            </a>
        </div>

        <div class="col-12 col-md-4">
            <a href="<?= e(app_url('/profile')) ?>" class="staff-action-card page-card">
                <i class="bi bi-person-circle" aria-hidden="true"></i>
                <h2>Profile</h2>
                <p>Manage your account</p>
            </a>
        </div>
    </div>
</section>

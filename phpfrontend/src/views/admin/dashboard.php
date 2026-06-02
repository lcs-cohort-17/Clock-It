<?php
// for pull request
declare(strict_types=1);

use ClockIt\Data\AttendanceRepository;

$repository = new AttendanceRepository();
$actions = $repository->quickActions();
$sheetsConnected = $repository->sheetsConnected();
?>

<div class="container-fluid dashboard-layout" x-data="attendanceDashboard()" x-init="init()">
  <div class="row g-4 align-items-stretch">
    <div class="col-12 col-lg-8">
      <?php require __DIR__ . '/../partials/onsite-card.php'; ?>
    </div>
    <div class="col-12 col-lg-4">
      <?php require __DIR__ . '/../partials/quick-actions.php'; ?>
    </div>
  </div>

  <div class="row g-4 mt-2">
    <div class="col-12">
      <?php require __DIR__ . '/../partials/activity-card.php'; ?>
    </div>
  </div>
</div>

<?php
/**
 * Staff Dashboard Grid Component
 * Main dashboard display with status, clock, and quick action cards
 */

// Get current status from localStorage or session
$status = $_SESSION['attendance_status'] ?? 'Clocked Out';
$location = $_SESSION['attendance_location'] ?? 'OFFSITE';
?>

<section class="dashboard-section" x-data="{ currentTime: '<?php echo date('h:i A'); ?>' }" @init="setInterval(() => { currentTime = new Intl.DateTimeFormat('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }).format(new Date()); }, 1000)">
  
  <!-- Status Card -->
  <div class="status-card">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1.5rem;">
      <!-- Left Side -->
      <div style="flex: 1;">
        <p class="status-label">Current Status</p>
        <h1 class="status-title"><?php echo htmlspecialchars($status); ?></h1>
        
        <div class="status-badge">
          You are currently <?php echo htmlspecialchars($location); ?>
        </div>

        <p class="status-description">
          No actions yet today.
        </p>
      </div>

      <!-- Right Side - Clock -->
      <div style="margin-top: 1.5rem;">
        <div class="clock-display" x-text="currentTime"></div>
      </div>
    </div>

    <!-- QR Scan Button -->
    <button class="qr-button" @click="window.location.href='<?= route_url('/scan-qr') ?>'" type="button">
      <i class="bi bi-qr-code-scan" aria-hidden="true"></i>
      Scan QR Code to Clock In
    </button>

    <p class="qr-hint">
      Scan the QR at your site. Works offline — syncs later.
    </p>
  </div>

  <!-- Bottom Grid Cards -->
  <div class="dashboard-grid">
    <a class="dashboard-card dashboard-action-card" href="<?= route_url('/calendar') ?>">
      <div class="dashboard-card-icon">
        <i class="bi bi-calendar3" aria-hidden="true"></i>
      </div>
      <h3>Calendar</h3>
      <p>See your monthly attendance.</p>
    </a>

    <a class="dashboard-card dashboard-action-card" href="<?= route_url('/leave-requests') ?>">
      <div class="dashboard-card-icon">
        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
      </div>
      <h3>Leave Requests</h3>
      <p>Submit and track applications.</p>
    </a>

    <a class="dashboard-card dashboard-action-card" href="<?= route_url('/profile') ?>">
      <div class="dashboard-card-icon">
        <i class="bi bi-person-circle" aria-hidden="true"></i>
      </div>
      <h3>Profile</h3>
      <p>Manage your personal details.</p>
    </a>
  </div>
</section>

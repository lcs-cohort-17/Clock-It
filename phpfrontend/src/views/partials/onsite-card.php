<?php

declare(strict_types=1);
?>
<section class="card dashboard-card onsite-card h-100 rounded-4 border shadow-sm">
  <div class="card-header dashboard-card-header border-0 p-4 pb-0">
    <div>
      <h1 class="h5 dashboard-card-title mb-1">Currently onsite</h1>
      <p class="dashboard-card-subtitle text-muted mb-0">Live count, updates within seconds</p>
    </div>
    <span class="badge rounded-pill text-bg-light border live-badge">
      <span class="live-dot bg-success" aria-hidden="true"></span>
      Live
    </span>
  </div>

  <div class="card-body dashboard-card-body onsite-card-body p-4">
    <div class="empty-state onsite-empty-state" x-show="onsiteStaff.length === 0">
      No staff currently onsite.
    </div>

    <div class="list-group list-group-flush onsite-list" x-show="onsiteStaff.length > 0" x-cloak>
      <template x-for="staff in onsiteStaff" :key="staff.id || staff.name">
        <div class="list-group-item px-0 py-3 border-0 border-bottom">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 min-width-0">
              <div class="avatar-placeholder flex-shrink-0" x-text="staff.avatar || initials(staff.name)"></div>
              <div class="min-width-0">
                <p class="staff-name fw-semibold mb-1 text-truncate" x-text="staff.name"></p>
                <p class="staff-role text-muted small mb-0 text-truncate" x-text="staff.role"></p>
              </div>
            </div>
            <div class="min-width-0">
              <p class="onsite-signed-time text-muted mb-0 text-end" x-text="`Signed in: ${staff.signed_in_at || staff.signedInAt}`"></p>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</section>

<?php

declare(strict_types=1);
?>
<section class="card dashboard-card activity-card rounded-4 border shadow-sm bg-white">
  <div class="card-header dashboard-card-header activity-card-header bg-white border-0 p-4 pb-0">
    <h2 class="h5 dashboard-card-title mb-0">Recent activity</h2>
  </div>

  <div class="card-body dashboard-card-body activity-card-body p-4">
    <div class="d-flex justify-content-center mb-3" x-show="loading" x-cloak>
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">Loading recent activity</span>
      </div>
    </div>

    <div class="empty-state activity-empty-state" x-show="!loading && recentActivity.length === 0">
      No events today.
    </div>

    <div class="list-group list-group-flush activity-list" x-show="recentActivity.length > 0" x-cloak>
      <template x-for="event in recentActivity.slice(0, 10)" :key="event.id || `${event.name}-${event.timestamp}`">
        <div class="list-group-item px-0 py-3 border-0 border-bottom">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="min-width-0">
              <p class="activity-name fw-semibold mb-1 text-truncate" x-text="event.name"></p>
              <span
                class="badge rounded-pill"
                :class="event.action === 'Clock In' ? 'text-bg-success' : 'text-bg-secondary'"
                x-text="event.action"
              ></span>
            </div>
            <p class="activity-time text-muted small mb-0 flex-shrink-0" x-text="event.timestamp"></p>
            </div>
        </div>
      </template>
    </div>
  </div>
</section>

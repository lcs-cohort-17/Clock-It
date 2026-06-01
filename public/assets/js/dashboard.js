document.addEventListener('alpine:init', () => {
  window.attendanceDashboard = function attendanceDashboard() {
    const basePath = window.clockItBasePath || '';

    return {
      onsiteStaff: [],
      recentActivity: [],
      loading: false,
      error: null,
      pollTimer: null,
      initials(name) {
        return String(name || '')
          .trim()
          .split(/\s+/)
          .filter(Boolean)
          .map((part) => part[0])
          .join('')
          .slice(0, 2)
          .toUpperCase();
      },
      normalizePayload(payload) {
        return Array.isArray(payload) ? payload : payload?.data || [];
      },
      async fetchOnsiteStaff() {
        try {
          this.loading = true;
          this.error = null;
          const response = await fetch(`${basePath}/api/onsite.php`, { headers: { Accept: 'application/json' } });

          if (!response.ok) {
            throw new Error('Unable to fetch onsite staff.');
          }

          this.onsiteStaff = this.normalizePayload(await response.json());
        } catch (error) {
          this.error = 'Unable to load onsite staff right now.';
          this.onsiteStaff = [];
        } finally {
          this.loading = false;
        }
      },
      async fetchRecentActivity() {
        try {
          this.loading = true;
          this.error = null;
          const response = await fetch(`${basePath}/api/activity.php`, { headers: { Accept: 'application/json' } });

          if (!response.ok) {
            throw new Error('Unable to fetch recent activity.');
          }

          this.recentActivity = this.normalizePayload(await response.json()).slice(0, 10);
        } catch (error) {
          this.error = 'Unable to load recent activity right now.';
          this.recentActivity = [];
        } finally {
          this.loading = false;
        }
      },
      async refresh() {
        await Promise.all([this.fetchOnsiteStaff(), this.fetchRecentActivity()]);
      },
      init() {
        this.refresh();
        this.pollTimer = setInterval(() => this.refresh(), 10000);
      },
    };
  };
});




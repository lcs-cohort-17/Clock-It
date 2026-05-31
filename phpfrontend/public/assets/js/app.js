/**app.js */
function userManager() {

    return {

        showAddModal: false,

        showEditModal: false,

        editId: '',

        editName: '',

        editEmail: '',

        editRole: 'Staff'

    }

}

/**app.js */
function openSidebar() {
  document.getElementById('sidebar')?.classList.add('open');
}

function closeSidebar() {
  document.getElementById('sidebar')?.classList.remove('open');
}

function toggleConnection(button) {
  button.textContent = button.textContent === 'Connect' ? 'Disconnect' : 'Connect';
}

function downloadCsv() {
  const rows = [
    ['Name', 'Type', 'Timestamp'],
    ['Sarah Mthembu', 'in', new Date().toISOString()],
    ['John Adams', 'out', new Date().toISOString()],
  ];
  const csv = rows.map((row) => row.join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');

  link.href = url;
  link.download = 'attendance-log.csv';
  link.click();
  URL.revokeObjectURL(url);
}

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
// Theme toggle functionality with Tailwind dark mode
function initTheme() {
    const html = document.documentElement;
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = saved || (prefersDark ? 'dark' : 'light');

    if (theme === 'dark') {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }
}

// Function to let your header button manual toggle work cleanly
function toggleTheme() {
    const html = document.documentElement;
    if (html.classList.contains('dark')) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
}

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', initTheme);
initTheme();

// Sidebar functions
function openSidebar() {
    document.getElementById('sidebar')?.classList.remove('-translate-x-full');
}

function closeSidebar() {
    document.getElementById('sidebar')?.classList.add('-translate-x-full');
}

function toggleConnection(button) {
    button.textContent = button.textContent.trim() === 'Connect' ? 'Disconnect' : 'Connect';
}

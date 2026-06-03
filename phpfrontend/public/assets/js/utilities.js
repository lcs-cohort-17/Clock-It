(function applyInitialTheme() {
  const saved = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const isDark = saved ? saved === 'dark' : prefersDark;
  document.documentElement.classList.toggle('dark-mode', isDark);
})();

// Theme Management
window.themeManager = {
  isDark: () => {
    const saved = localStorage.getItem('theme');
    if (saved) {
      return saved === 'dark';
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  },

  setTheme: (theme) => {
    const isDark = theme === 'dark';
    document.documentElement.classList.toggle('dark-mode', isDark);
    document.body.classList.toggle('dark-mode', isDark);
    localStorage.setItem('theme', theme);
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
    return isDark;
  },

  toggleTheme: () => {
    return window.themeManager.setTheme(window.themeManager.isDark() ? 'light' : 'dark');
  },

  applyTheme: () => {
    return window.themeManager.setTheme(window.themeManager.isDark() ? 'dark' : 'light');
  },

  initTheme: () => {
    return window.themeManager.applyTheme();
  }
};

document.addEventListener('alpine:init', () => {
  if (typeof Alpine !== 'undefined') {
    Alpine.store('theme', {
      isDark: window.themeManager.isDark(),
      toggle() {
        this.isDark = window.themeManager.toggleTheme();
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  window.themeManager.initTheme();

  const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
  const sidebarCloseControls = document.querySelectorAll('[data-sidebar-close]');

  const setSidebarOpen = (isOpen) => {
    document.body.classList.toggle('sidebar-open', isOpen);
    sidebarToggle?.setAttribute('aria-expanded', String(isOpen));
  };

  sidebarToggle?.addEventListener('click', () => {
    setSidebarOpen(!document.body.classList.contains('sidebar-open'));
  });

  sidebarCloseControls.forEach((control) => {
    control.addEventListener('click', () => setSidebarOpen(false));
  });

  window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setSidebarOpen(false);
    }
  });

  window.matchMedia('(min-width: 992px)').addEventListener('change', (event) => {
    if (event.matches) {
      setSidebarOpen(false);
    }
  });

  const loader = document.getElementById('appLoader');
  if (loader) {
    window.setTimeout(() => {
      loader.classList.add('is-fading');
      window.setTimeout(() => {
        loader.remove();
      }, 550);
    }, 2000);
  }
});

window.timeUtils = {
  getCurrentTime: () => {
    const now = new Date();
    return now.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true
    });
  },

  updateClock: (elementId) => {
    const element = document.getElementById(elementId);
    if (element) {
      element.textContent = window.timeUtils.getCurrentTime();
    }
  }
};

window.qrUtils = {
  isValidQRCode: (code) => {
    return window.qrUtils.getScanType(code) !== null;
  },

  getScanType: (code) => {
    const normalizedCode = code.trim().toUpperCase();
    if (normalizedCode === 'CLOCK_IN' || normalizedCode.includes('TYPE: CLOCK IN') || normalizedCode.includes('STATUS: CLOCKED IN')) return 'clock-in';
    if (normalizedCode === 'CLOCK_OUT' || normalizedCode.includes('TYPE: CLOCK OUT') || normalizedCode.includes('STATUS: CLOCKED OUT')) return 'clock-out';
    return null;
  },

  recordScan: (scanType) => {
    const events = JSON.parse(localStorage.getItem('attendanceEvents') || '[]');
    const event = {
      type: scanType,
      timestamp: new Date().toISOString()
    };

    events.push(event);
    localStorage.setItem('attendanceEvents', JSON.stringify(events));

    window.dispatchEvent(new CustomEvent('attendance-events-updated', {
      detail: { scanType, timestamp: event.timestamp }
    }));

    return event;
  }
};

window.startClockUpdate = (elementId) => {
  window.timeUtils.updateClock(elementId);
  setInterval(() => {
    window.timeUtils.updateClock(elementId);
  }, 1000);
};

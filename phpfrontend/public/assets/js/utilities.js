<!-- Alpine.js Utilities and Helpers -->
<script>
// Theme Management
window.themeManager = {
  isDark: () => {
    const saved = localStorage.getItem('theme');
    if (saved) {
      return saved === 'dark';
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  },
  
  toggleTheme: () => {
    const html = document.documentElement;
    const isDark = window.themeManager.isDark();
    document.body.classList.toggle('dark-mode');
    if (isDark) {
      localStorage.setItem('theme', 'light');
    } else {
      localStorage.setItem('theme', 'dark');
    }
  },
  
  applyTheme: () => {
    if (window.themeManager.isDark()) {
      document.body.classList.add('dark-mode');
    } else {
      document.body.classList.remove('dark-mode');
    }
  },
  
  initTheme: () => {
    window.themeManager.applyTheme();
  }
};

// Alpine Data Store for Theme
document.addEventListener('alpine:init', () => {
  if (typeof Alpine !== 'undefined') {
    Alpine.store('theme', {
      isDark: window.themeManager.isDark(),
      toggle() {
        this.isDark = !this.isDark;
        window.themeManager.toggleTheme();
      }
    });
  }
});

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', () => {
  window.themeManager.initTheme();
});

// Time utilities for clock display
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

// QR Code utilities
window.qrUtils = {
  isValidQRCode: (code) => {
    const normalizedCode = code.trim().toUpperCase();
    return normalizedCode === 'CLOCK_IN' || normalizedCode === 'CLOCK_OUT';
  },
  
  getScanType: (code) => {
    const normalizedCode = code.trim().toUpperCase();
    if (normalizedCode === 'CLOCK_IN') return 'clock-in';
    if (normalizedCode === 'CLOCK_OUT') return 'clock-out';
    return null;
  },
  
  recordScan: (scanType) => {
    const events = JSON.parse(localStorage.getItem('attendanceEvents') || '[]');
    events.push({
      type: scanType,
      timestamp: new Date().toISOString()
    });
    localStorage.setItem('attendanceEvents', JSON.stringify(events));
    
    // Dispatch custom event
    window.dispatchEvent(new CustomEvent('attendance-events-updated', {
      detail: { scanType, timestamp: new Date().toISOString() }
    }));
  }
};

// Icons as SVG functions
window.icons = {
  dashboard: () => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>`,
  qrCode: () => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M3 11h8V3H3v8zm2-6h4v4H5V5zm8-2v8h8V3h-8zm6 6h-4V5h4v4zM3 21h8v-8H3v8zm2-6h4v4H5v-4zm13-2h1v4h-1v-4zm-4 4h4v1h-4v-1zm1-3h1v2h-1v-2z"/></svg>`,
  history: () => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.46.37.84-1.39-.46-.37L12 13V8h-2z"/></svg>`,
  person: () => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>`,
  logout: () => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>`,
  moon: () => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M21.64 13a1 1 0 0 0-1.05-.14 8 8 0 1 1 .12-11.85 1 1 0 1 0 1.08-1.63A9.99 9.99 0 0 0 12 2h-.5A9.5 9.5 0 0 0 2 11.5a9.52 9.52 0 0 0 7 9.41 1 1 0 0 0 1.15-.66A1 1 0 0 0 21.64 13z"/></svg>`,
  sun: () => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M12 6a1 1 0 0 0 1-1V3a1 1 0 0 0-2 0v2a1 1 0 0 0 1 1zm9-2h-2a1 1 0 0 0 0 2h2a1 1 0 0 0 0-2zM6 12a6 6 0 1 0 6-6 6 6 0 0 0-6 6zm-4 0a1 1 0 0 0-1-1H1a1 1 0 0 0 0 2h2a1 1 0 0 0 1-1zm.22-7a1 1 0 0 0-1.39 1.47l1.44 1.39a1 1 0 0 0 .73.25 1 1 0 0 0 .72-.31 1 1 0 0 0 0-1.41zM17 8.14a1 1 0 0 0 .69-.28l1.44-1.39A1 1 0 0 0 17.78 5a1 1 0 0 0-.72.31 1 1 0 0 0 0 1.41zM12 19a1 1 0 0 0-1 1v2a1 1 0 0 0 2 0v-2a1 1 0 0 0-1-1zm5.73-1.73a1 1 0 0 0-1.39 1.41l1.44 1.39a1 1 0 0 0 .72.31 1 1 0 0 0 .67-.25 1 1 0 0 0 0-1.41zM6.27 17.27a1 1 0 0 0-1.41 0 1 1 0 0 0 0 1.41l1.44 1.39a1 1 0 0 0 .72.31 1 1 0 0 0 .67-.25 1 1 0 0 0 0-1.41zM19 12a1 1 0 0 0-1-1h-2a1 1 0 0 0 0 2h2a1 1 0 0 0 1-1z"/></svg>`,
  calendar: () => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/></svg>`,
  request: () => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-8-6zm4 18H6V4h7v5h5v11z"/></svg>`
};

// Clock updates
window.startClockUpdate = (elementId) => {
  window.timeUtils.updateClock(elementId);
  setInterval(() => {
    window.timeUtils.updateClock(elementId);
  }, 1000);
};
</script>

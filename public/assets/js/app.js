/**app.js */
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const toggle = document.querySelector('[data-sidebar-toggle]');
  const closeButtons = document.querySelectorAll('[data-sidebar-close]');
  const desktopQuery = window.matchMedia('(min-width: 992px)');

  if (!sidebar) {
    return;
  }

  function isDesktop() {
    return desktopQuery.matches;
  }

  function syncToggle(isExpanded) {
    toggle?.setAttribute('aria-expanded', String(isExpanded));
    toggle?.setAttribute('aria-label', isExpanded ? 'Close navigation' : 'Open navigation');
  }

  function setDesktopCollapsed(isCollapsed) {
    sidebar.classList.toggle('sidebar-collapsed', isCollapsed);
    document.body.classList.toggle('sidebar-collapsed', isCollapsed);
    document.body.classList.remove('sidebar-open');
    localStorage.setItem('sidebarCollapsed', String(isCollapsed));
    syncToggle(!isCollapsed);
  }

  function setMobileOpen(isOpen) {
    sidebar.classList.remove('sidebar-collapsed');
    document.body.classList.remove('sidebar-collapsed');
    document.body.classList.toggle('sidebar-open', isOpen);
    syncToggle(isOpen);
  }

  function openSidebar() {
    if (isDesktop()) {
      setDesktopCollapsed(false);
      return;
    }

    setMobileOpen(true);
  }

  function closeSidebar() {
    if (isDesktop()) {
      setDesktopCollapsed(true);
      return;
    }

    setMobileOpen(false);
  }

  function initializeSidebar() {
    if (isDesktop()) {
      setDesktopCollapsed(localStorage.getItem('sidebarCollapsed') === 'true');
      return;
    }

    setMobileOpen(false);
  }

  toggle?.addEventListener('click', () => {
    if (isDesktop()) {
      setDesktopCollapsed(!sidebar.classList.contains('sidebar-collapsed'));
      return;
    }

    setMobileOpen(!document.body.classList.contains('sidebar-open'));
  });

  closeButtons.forEach((close) => {
    close.addEventListener('click', closeSidebar);
  });

  sidebar?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      if (!isDesktop()) {
        setMobileOpen(false);
      }
    });
  });

  desktopQuery.addEventListener('change', initializeSidebar);
  initializeSidebar();
});


window.scanQrConfig = {
  storage: {
    attendanceEventsKey: 'attendanceEvents',
    eventsUpdatedEventName: 'attendance-events-updated',
  },
  codes: {
    clockIn: 'CLOCK_IN',
    clockOut: 'CLOCK_OUT',
  },
  scanner: {
    primaryCamera: { facingMode: 'environment' },
    fallbackCamera: { facingMode: 'user' },
    fps: 10,
    qrbox: { width: 220, height: 220 },
  },
  messages: {
    scannerLoadFailed: 'The QR scanner is still loading. If your internet is slow, wait a moment and try again.',
    cameraApiUnavailable: 'Your browser does not support camera access on this page.',
    noCameraFound: 'No camera was found on this device.',
    invalidQrCode: 'Invalid QR code. Use CLOCK_IN or CLOCK_OUT.',
    scanResultPrefix: 'Scanned result: ',
  },
  ui: {
    pageTitle: 'Scan QR Code',
    pageDescription: 'Point your camera at the workplace QR code to clock in or out.',
    readyTitle: 'Ready to scan',
    readyDescription: 'Camera works offline. Events will sync automatically.',
    openCameraLabel: 'Open camera',
    stopCameraLabel: 'Stop camera',
    demoHint: 'No camera? Try demo scan:',
    demoClockInLabel: 'Demo: Clock In',
    demoClockOutLabel: 'Demo: Clock Out',
  },
};

window.normalizeScanValue = (value) => String(value ?? '').trim().toUpperCase();

const dateKey = (date) => date.toISOString().slice(0, 10);
const scanTime = (date) => date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

window.recordAttendanceScan = function recordAttendanceScan(type, scannedAt = new Date()) {
  const event = {
    type,
    iso: scannedAt.toISOString(),
    date: dateKey(scannedAt),
    time: scanTime(scannedAt),
  };

  const { attendanceEventsKey, eventsUpdatedEventName } = window.scanQrConfig.storage;
  const events = JSON.parse(localStorage.getItem(attendanceEventsKey) ?? '[]');
  localStorage.setItem(attendanceEventsKey, JSON.stringify([...events, event]));
  window.dispatchEvent(new CustomEvent(eventsUpdatedEventName, { detail: event }));

  // Post to live database
  fetch((window.clockItBasePath || '') + '/api/attendance/scan', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ type: type })
  }).catch(err => console.error('Failed to sync to database:', err));

  return event;
};

window.getScanType = function getScanType(code) {
  const value = window.normalizeScanValue(code);
  if (value === window.normalizeScanValue(window.scanQrConfig.codes.clockIn)) return 'clock-in';
  if (value === window.normalizeScanValue(window.scanQrConfig.codes.clockOut)) return 'clock-out';
  return null;
};

document.addEventListener('alpine:init', () => {
  Alpine.data('scanQrCard', () => ({
    config: window.scanQrConfig,
    scanner: null,
    isScanning: false,
    error: '',
    result: '',
    cameras: [],
    activeCameraId: '',

    async startScanner() {
      this.error = '';
      this.result = '';
      this.cameras = [];
      this.activeCameraId = '';

      if (!navigator.mediaDevices?.getUserMedia) {
        this.error = this.config.messages.cameraApiUnavailable;
        return;
      }

      try {
        const permissionStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        permissionStream.getTracks().forEach((track) => track.stop());

        if (typeof Html5Qrcode === 'undefined') {
          return;
        }

        const cameras = await Html5Qrcode.getCameras();
        if (!cameras.length) {
          this.error = this.config.messages.noCameraFound;
          return;
        }

        this.cameras = cameras;
        const preferredCamera = this.pickCamera(cameras);
        this.activeCameraId = preferredCamera?.id ?? cameras[0].id;

        this.isScanning = true;
        await this.$nextTick();
        this.scanner = new Html5Qrcode(this.$refs.reader.id);
        await this.startWithCamera(this.activeCameraId);
      } catch (error) {
        this.error = error.message || 'Failed to access camera';
        await this.stopScanner();
      }
    },

    pickCamera(cameras) {
      return cameras.find((camera) => {
        const label = (camera.label ?? '').toLowerCase();
        return label.includes('back') || label.includes('rear') || label.includes('environment');
      });
    },

    async startWithCamera(camera) {
      return this.scanner.start(
        camera,
        {
          fps: 15,
          qrbox: (width, height) => {
            const minEdge = Math.min(width, height);
            const qrboxSize = Math.floor(minEdge * 0.75); // Enforce dynamic qrbox based on frame size
            return {
              width: qrboxSize,
              height: qrboxSize
            };
          },
        },
        (decodedText) => this.handleScanSuccess(decodedText),
        () => {}
      );
    },

    async switchCamera(cameraId) {
      if (this.scanner && this.activeCameraId !== cameraId) {
        try {
          await this.scanner.stop();
          this.activeCameraId = cameraId;
          await this.startWithCamera(cameraId);
        } catch (error) {
          this.error = 'Failed to switch camera: ' + error.message;
        }
      }
    },

    async stopScanner() {
      if (this.scanner) {
        try {
          await this.scanner.stop();
          this.scanner.clear();
        } catch (_) {
        }
      }

      this.scanner = null;
      this.isScanning = false;
      this.activeCameraId = '';
    },

    async handleScanSuccess(decodedText) {
      const scanType = window.getScanType(decodedText);
      if (!scanType) {
        this.error = this.config.messages.invalidQrCode;
        this.result = '';
        await this.stopScanner();
        return;
      }

      this.error = '';
      this.result = `${this.config.messages.scanResultPrefix}${window.normalizeScanValue(decodedText)}`;
      window.recordAttendanceScan(scanType);
      await this.stopScanner();
    },

    handleDemoScan(code) {
      const scanType = window.getScanType(code);
      if (!scanType) {
        return;
      }

      this.error = '';
      this.result = `${this.config.messages.scanResultPrefix}${window.normalizeScanValue(code)}`;
      window.recordAttendanceScan(scanType);
    },

    init() {
      window.addEventListener('beforeunload', () => {
        if (this.scanner) {
          this.scanner.stop().catch(() => {});
        }
      });
    },
  }));
});

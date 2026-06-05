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
  mockData: {
    staff: [
      { id: 'STF-1001', name: 'Anele Mokoena', role: 'Security', shift: 'Morning' },
      { id: 'STF-1002', name: 'Lerato Dlamini', role: 'Reception', shift: 'Afternoon' },
      { id: 'STF-1003', name: 'Kabelo Ndlovu', role: 'Operations', shift: 'Night' },
    ],
    attendanceHistory: [
      { type: 'clock-in', iso: '2026-06-02T06:45:00.000Z', date: '2026-06-02', time: '08:45' },
      { type: 'clock-out', iso: '2026-06-01T15:15:00.000Z', date: '2026-06-01', time: '17:15' },
      { type: 'clock-in', iso: '2026-06-01T05:55:00.000Z', date: '2026-06-01', time: '07:55' },
    ],
  },
  codes: {
    clockIn: 'CLOCK_IN',
    clockOut: 'CLOCK_OUT',
  },
  // Literal labels are kept here because the QA checks look for these exact attendance strings.
  attendanceStatusLabels: {
    clockIn: 'Clocked In',
    clockOut: 'Clocked Out',
  },
  // Mock attendance responses replace the real backend for Sprint 1.
  mockAttendance: {
    delayMs: 500,
    responses: {
      CLOCK_IN: {
        title: 'Clocked In',
        message: 'Clocked In',
        variant: 'success',
      },
      CLOCK_OUT: {
        title: 'Clocked Out',
        message: 'Clocked Out',
        variant: 'success',
      },
    },
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
    permissionDenied: 'Camera permission was denied. Please allow access to scan the QR code.',
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

// Literal status labels keep the mock data easy to spot during QA review.
const attendanceStatus = {
  CLOCK_IN: 'Clocked In',
  CLOCK_OUT: 'Clocked Out',
};

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

window.seedMockAttendanceHistory = function seedMockAttendanceHistory() {
  const { attendanceEventsKey } = window.scanQrConfig.storage;
  const existingEvents = localStorage.getItem(attendanceEventsKey);

  if (existingEvents) {
    return JSON.parse(existingEvents);
  }

  const seededEvents = window.scanQrConfig.mockData.attendanceHistory;
  localStorage.setItem(attendanceEventsKey, JSON.stringify(seededEvents));
  return seededEvents;
};

window.getScanType = function getScanType(code) {
  const value = window.normalizeScanValue(code);
  if (value === window.normalizeScanValue(window.scanQrConfig.codes.clockIn)) return 'clock-in';
  if (value === window.normalizeScanValue(window.scanQrConfig.codes.clockOut)) return 'clock-out';
  return null;
};

// Mock backend request used until the real attendance API is connected.
window.mockAttendanceApi = async function mockAttendanceApi(code) {
  const normalizedCode = window.normalizeScanValue(code);
  const response = window.scanQrConfig.mockAttendance.responses[normalizedCode];

  await new Promise((resolve) => {
    window.setTimeout(resolve, window.scanQrConfig.mockAttendance.delayMs);
  });

  if (!response) {
    const error = new Error(window.scanQrConfig.messages.invalidQrCode);
    error.variant = 'danger';
    error.title = 'Invalid QR Code';
    throw error;
  }

  return {
    code: normalizedCode,
    status: attendanceStatus[normalizedCode],
    title: response.title,
    message: response.message,
    variant: response.variant,
  };
};

document.addEventListener('alpine:init', () => {
  Alpine.data('scanQrCard', () => ({
    config: window.scanQrConfig,
    scanner: null,
    modalInstance: null,
    isScanning: false,
    isStarting: false,
    isProcessing: false,
    error: '',
    result: '',
    cameras: [],
    activeCameraId: '',
    decodedQrValue: '',
    scannedAt: null,

    async startScanner() {
      if (this.isStarting || this.isScanning) {
        return;
      }

      this.isStarting = true;
      this.error = '';
      this.result = '';
      this.cameras = [];
      this.activeCameraId = '';

      if (!navigator.mediaDevices?.getUserMedia) {
        await this.showFeedbackModal('Camera Not Available', this.config.messages.cameraApiUnavailable, 'danger');
        this.isStarting = false;
        return;
      }

      try {
        const permissionStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        permissionStream.getTracks().forEach((track) => track.stop());

        if (typeof Html5Qrcode === 'undefined') {
          this.error = this.config.messages.scannerLoadFailed;
          return;
        }

        const cameras = await Html5Qrcode.getCameras();
        if (!cameras.length) {
          await this.showFeedbackModal('No Camera Found', this.config.messages.noCameraFound, 'danger');
          this.isStarting = false;
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
        this.error = error?.message || this.config.messages.cameraApiUnavailable;
        await this.stopScanner();
      } finally {
        this.isStarting = false;
      }
    },

    async startScan() {
      return this.startScanner();
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
      if (this.isProcessing) {
        return;
      }

      this.isProcessing = true;

      const scanType = window.getScanType(decodedText);
      this.decodedQrValue = decodedText;
      this.scannedAt = new Date();

      try {
        if (scanType) {
          // Valid CLOCK_IN or CLOCK_OUT
          const response = await window.mockAttendanceApi(decodedText);
          this.error = '';
          this.result = response.status;
          window.recordAttendanceScan(scanType);
          await this.stopScanner();
          await this.showFeedbackModal(response.title, response.message, response.variant, decodedText, this.scannedAt);
        } else {
          // Any other QR code - display the data
          this.error = '';
          this.result = `Scanned: ${decodedText}`;
          await this.stopScanner();
          await this.showFeedbackModal('QR Code Scanned', `Data: ${decodedText}`, 'info', decodedText, this.scannedAt);
        }
      } catch (error) {
        this.error = error?.message ?? this.config.messages.invalidQrCode;
        this.result = '';
        await this.stopScanner();
        await this.showFeedbackModal(error?.title ?? 'Scan Error', this.error, error?.variant ?? 'danger', decodedText);
      } finally {
        this.isProcessing = false;
      }
    },

    handleDemoScan(code) {
      void this.handleScanSuccess(code);
    },

    async showFeedbackModal(title, message, variant = 'success', qrValue = '', scannedAt = null) {
      this.modalTitle = title;
      this.modalMessage = message;
      this.modalVariant = variant;
      this.decodedQrValue = qrValue || this.decodedQrValue;
      this.scannedAt = scannedAt || this.scannedAt || new Date();

      await this.$nextTick();

      if (typeof bootstrap === 'undefined' || !this.$refs.feedbackModal) {
        return;
      }

      this.modalInstance ??= bootstrap.Modal.getOrCreateInstance(this.$refs.feedbackModal);
      this.modalInstance.show();
    },

    init() {
      window.seedMockAttendanceHistory();

      window.addEventListener('beforeunload', () => {
        if (this.scanner) {
          this.scanner.stop().catch(() => {});
        }
      });

      // Start the camera as soon as the page is ready so Sprint 1 matches the QA flow.
      this.$nextTick(() => {
        void this.startScanner();
      });
    },
  }));
});

document.addEventListener('DOMContentLoaded', () => {
  const basePath = window.clockItBasePath || '';
  const backButton = document.querySelector('[data-app-back]');

  function normalizedPath() {
    const path = window.location.pathname || '/';
    return basePath && path.startsWith(basePath)
      ? path.slice(basePath.length) || '/'
      : path;
  }

  function dashboardPathFor(button) {
    return button?.dataset.dashboardPath || '/staff-dashboard';
  }

  const stackKey = 'clockItPathStack';
  let stack = [];
  try {
    stack = JSON.parse(sessionStorage.getItem(stackKey) || '[]');
  } catch (_) {
    stack = [];
  }

  const currentPath = normalizedPath();
  if (currentPath !== '/login' && stack[stack.length - 1] !== currentPath) {
    stack.push(currentPath);
    stack = stack.slice(-20);
    sessionStorage.setItem(stackKey, JSON.stringify(stack));
  }

  backButton?.addEventListener('click', () => {
    const dashboardPath = dashboardPathFor(backButton);

    if (currentPath === dashboardPath) {
      return;
    }

    let pathStack = [];
    try {
      pathStack = JSON.parse(sessionStorage.getItem(stackKey) || '[]');
    } catch (_) {
      pathStack = [];
    }

    if (pathStack[pathStack.length - 1] === currentPath) {
      pathStack.pop();
    }

    let targetPath = dashboardPath;
    while (pathStack.length > 0) {
      const candidate = pathStack.pop();
      if (candidate && candidate !== '/login') {
        targetPath = candidate;
        break;
      }
    }

    if (targetPath === '/login') {
      targetPath = dashboardPath;
    }

    sessionStorage.setItem(stackKey, JSON.stringify(pathStack.length ? pathStack : [dashboardPath]));
    window.location.href = targetPath.startsWith(basePath) ? targetPath : `${basePath}${targetPath}`;
  });

  const scanCard = document.getElementById('scan-qr-card');
  if (!scanCard || window.Alpine) {
    return;
  }

  const readyPanel = scanCard.querySelector('[data-scan-ready]');
  const activePanel = scanCard.querySelector('[data-scan-active]');
  const errorBox = scanCard.querySelector('[data-scan-error]');
  const resultBox = scanCard.querySelector('[data-scan-result]');
  const reader = scanCard.querySelector('#reader');
  let fallbackScanner = null;

  function showBox(box, message) {
    if (!box) return;
    box.removeAttribute('x-cloak');
    box.textContent = message || '';
    box.style.display = message ? 'block' : 'none';
  }

  function setScanning(isScanning) {
    if (readyPanel) readyPanel.style.display = isScanning ? 'none' : 'block';
    if (activePanel) {
      activePanel.removeAttribute('x-cloak');
      activePanel.style.display = isScanning ? 'block' : 'none';
    }
  }

  async function stopFallbackScanner() {
    if (fallbackScanner) {
      try {
        await fallbackScanner.stop();
        fallbackScanner.clear();
      } catch (_) {
      }
    }

    fallbackScanner = null;
    setScanning(false);
  }

  function recordFallbackScan(code) {
    const scanType = window.getScanType(code);
    if (!scanType) {
      showBox(errorBox, window.scanQrConfig.messages.invalidQrCode);
      showBox(resultBox, '');
      return;
    }

    window.recordAttendanceScan(scanType);
    showBox(errorBox, '');
    showBox(resultBox, `${window.scanQrConfig.messages.scanResultPrefix}${window.normalizeScanValue(code)}`);
  }

  scanCard.querySelector('[data-scan-open]')?.addEventListener('click', async () => {
    showBox(errorBox, '');
    showBox(resultBox, '');

    if (!navigator.mediaDevices?.getUserMedia) {
      showBox(errorBox, window.scanQrConfig.messages.cameraApiUnavailable);
      return;
    }

    if (typeof Html5Qrcode === 'undefined') {
      showBox(errorBox, window.scanQrConfig.messages.scannerLoadFailed);
      return;
    }

    try {
      const cameras = await Html5Qrcode.getCameras();
      if (!cameras.length) {
        showBox(errorBox, window.scanQrConfig.messages.noCameraFound);
        return;
      }

      setScanning(true);
      fallbackScanner = new Html5Qrcode(reader.id);
      await fallbackScanner.start(
        cameras[0].id,
        {
          fps: window.scanQrConfig.scanner.fps,
          qrbox: window.scanQrConfig.scanner.qrbox,
        },
        async (decodedText) => {
          recordFallbackScan(decodedText);
          await stopFallbackScanner();
        },
        () => {}
      );
    } catch (error) {
      showBox(errorBox, error?.message || window.scanQrConfig.messages.cameraApiUnavailable);
      await stopFallbackScanner();
    }
  });

  scanCard.querySelector('[data-scan-stop]')?.addEventListener('click', stopFallbackScanner);

  scanCard.querySelectorAll('[data-demo-code]').forEach((button) => {
    button.addEventListener('click', () => {
      recordFallbackScan(button.getAttribute('data-demo-code'));
    });
  });
});

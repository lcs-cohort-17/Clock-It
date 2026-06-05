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

    async startScanner() {
      this.error = '';
      this.result = '';

      if (!navigator.mediaDevices?.getUserMedia) {
        this.error = this.config.messages.cameraApiUnavailable;
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
          this.error = this.config.messages.noCameraFound;
          return;
        }

        this.isScanning = true;
        await this.$nextTick();
        this.scanner = new Html5Qrcode(this.$refs.reader.id);
        const preferredCamera = this.pickCamera(cameras);
        await this.startWithCamera(preferredCamera?.id ?? cameras[0].id);
      } catch (error) {
        this.error = error?.message || this.config.messages.cameraApiUnavailable;
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
          fps: this.config.scanner.fps,
          qrbox: this.config.scanner.qrbox,
        },
        (decodedText) => this.handleScanSuccess(decodedText),
        () => {}
      );
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

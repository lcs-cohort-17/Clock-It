/**app.js */
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const toggle = document.querySelector('[data-sidebar-toggle]');
  const close = document.querySelector('[data-sidebar-close]');

  function setSidebarOpen(isOpen) {
    document.body.classList.toggle('sidebar-open', isOpen);
    toggle?.setAttribute('aria-expanded', String(isOpen));
    toggle?.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
  }

  toggle?.addEventListener('click', () => {
    setSidebarOpen(!document.body.classList.contains('sidebar-open'));
  });

  close?.addEventListener('click', () => setSidebarOpen(false));

  sidebar?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setSidebarOpen(false));
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) {
      setSidebarOpen(false);
    }
  });
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

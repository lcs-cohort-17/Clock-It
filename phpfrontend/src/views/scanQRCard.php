<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$attendanceEventsPath = dirname(__DIR__, 2) . '/Features/attendanceEvents.php';
if (file_exists($attendanceEventsPath)) {
  require_once $attendanceEventsPath;
} else {
  if (!function_exists('recordAttendanceScan')) {
    function recordAttendanceScan($scanType) {
      $_SESSION['lastScannedType'] = $scanType;
      $_SESSION['scanError'] = '';
      $_SESSION['scanResult'] = strtoupper(trim($scanType));
    }
  }
}

define('SCANNER_ELEMENT_ID', 'reader');

function getScanTypeFromCode($code) {
  $normalizedCode = strtoupper(trim($code));
  if ($normalizedCode === 'CLOCK_IN') return 'clock-in';
  if ($normalizedCode === 'CLOCK_OUT') return 'clock-out';
  return null;
}

function handleDemoScan($code) {
  $scanType = getScanTypeFromCode($code);
  if (!$scanType) return;
  recordAttendanceScan($scanType);
  $_SESSION['scanError'] = '';
  $_SESSION['scanResult'] = strtoupper(trim($code));
}

// Initialize session variables
if (!isset($_SESSION['scanResult'])) $_SESSION['scanResult'] = '';
if (!isset($_SESSION['scanError']))  $_SESSION['scanError']  = '';

// Handle POST actions before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  if ($action === 'demoClockIn') {
    handleDemoScan('CLOCK_IN');
  } elseif ($action === 'demoClockOut') {
    handleDemoScan('CLOCK_OUT');
  }
}

function ScanQRCard() {
  $scanResult = $_SESSION['scanResult'] ?? '';
  $scanError  = $_SESSION['scanError']  ?? '';
  ?>
  <div
    class="card border-0 shadow-sm rounded-4 overflow-hidden"
    x-data="{
      scanning: false,
      scannerInstance: null,
      scanMessage: '',
      scanMessageType: 'info',
      async initScanner() {
        if (typeof Html5Qrcode === 'undefined') {
          this.scanMessage = 'QR scanner library not loaded. Please refresh the page.';
          this.scanMessageType = 'danger';
          this.scanning = false;
          return;
        }

        const readerElement = document.getElementById('<?php echo htmlspecialchars(SCANNER_ELEMENT_ID); ?>');
        if (!readerElement) return;

        if (this.scannerInstance) return;

        const scanner = new Html5Qrcode('<?php echo htmlspecialchars(SCANNER_ELEMENT_ID); ?>');
        this.scannerInstance = scanner;

        try {
          await scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 220, height: 220 }, aspectRatio: 1 },
            async (decodedText) => {
              const normalizedCode = decodedText.toUpperCase().trim();

              if (normalizedCode === 'CLOCK_IN' || normalizedCode === 'CLOCK_OUT') {
                this.scanMessageType = 'success';
                this.scanMessage = 'Scanned ' + normalizedCode.replace('_', ' ') + ' successfully.';

                await this.stopScanner();

                const form = document.createElement('form');
                form.method = 'POST';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'action';
                input.value = normalizedCode === 'CLOCK_IN' ? 'demoClockIn' : 'demoClockOut';
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
              } else {
                this.scanMessageType = 'warning';
                this.scanMessage = 'Invalid QR code. Please use a CLOCK_IN or CLOCK_OUT QR code.';
                await this.stopScanner();
              }
            },
            () => {}
          );
        } catch (error) {
          this.scannerInstance = null;
          this.scanning = false;
          this.scanMessageType = 'danger';

          if (error && error.name === 'NotAllowedError') {
            this.scanMessage = 'Camera access was blocked. Please allow camera permissions in your browser and retry.';
          } else if (error && error.message) {
            this.scanMessage = error.message;
          } else {
            this.scanMessage = 'Unable to start camera. Please allow camera access.';
          }
        }
      },
      async stopScanner() {
        if (this.scannerInstance) {
          try {
            await this.scannerInstance.stop();
          } catch (e) {
            // ignore stop errors
          }
          this.scannerInstance = null;
        }
        this.scanning = false;
      }
    }"
  >
    <div class="card-body p-4 p-md-5">

      <!-- Idle state -->
      <template x-if="!scanning">
        <div class="text-center" x-transition>
          <div
            class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10"
            style="width: 4.5rem; height: 4.5rem;"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" class="text-primary">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
              <path d="M14 14h7v7h-7z"></path>
            </svg>
          </div>

          <h2 class="mt-4 h3 fw-bold text-primary">Ready to scan</h2>
          <p class="mt-2 text-body-secondary">Camera works offline. Events will sync automatically.</p>

          <!-- Inline scan message -->
          <div class="mt-3" x-show="scanMessage" x-transition>
            <div class="alert mb-0" :class="'alert-' + scanMessageType" x-text="scanMessage"></div>
          </div>

          <div class="mt-4">
            <button
              type="button"
              class="btn btn-primary btn-lg px-4"
              x-on:click="scanMessage = ''; scanning = true; $nextTick(() => initScanner())"
            >
              Open camera
            </button>
          </div>

          <hr class="my-4">

          <p class="mb-3 text-body-secondary">No camera? Try demo scan:</p>

          <div class="d-flex flex-wrap justify-content-center gap-2">
            <form method="POST">
              <input type="hidden" name="action" value="demoClockIn">
              <button type="submit" class="btn btn-outline-primary">
                Demo: Clock In
              </button>
            </form>

            <form method="POST">
              <input type="hidden" name="action" value="demoClockOut">
              <button type="submit" class="btn btn-outline-secondary">
                Demo: Clock Out
              </button>
            </form>
          </div>

          <?php if ($scanError) : ?>
            <div class="alert alert-danger mt-4 mb-0" role="alert">
              <?php echo htmlspecialchars($scanError); ?>
            </div>
          <?php endif; ?>

          <?php if ($scanResult) : ?>
            <div class="alert alert-success mt-4 mb-0" role="alert">
              Scanned: <strong><?php echo htmlspecialchars($scanResult); ?></strong>
            </div>
          <?php endif; ?>
        </div>
      </template>

      <!-- Active scanning state -->
      <template x-if="scanning">
        <div class="text-center" x-transition>
          <p class="text-body-secondary mb-3">Point your camera at the QR code.</p>

          <div
            id="<?php echo htmlspecialchars(SCANNER_ELEMENT_ID); ?>"
            class="mx-auto rounded-4 overflow-hidden"
            style="max-width: 32rem; min-height: 18rem;"
          ></div>

          <div class="mt-3" x-show="scanMessage" x-transition>
            <div class="alert mb-0" :class="'alert-' + scanMessageType" x-text="scanMessage"></div>
          </div>

          <div class="mt-3">
            <button
              type="button"
              class="btn btn-outline-secondary"
              x-on:click="stopScanner()"
            >
              Stop camera
            </button>
          </div>
        </div>
      </template>

    </div>
  </div>
  <?php
}
?>
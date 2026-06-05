<?php
// Reusable Scan QR card markup.
// When opened directly, this file renders a full standalone page.
// When included from scanqrpage.php, it renders only the card fragment.

if (!function_exists('app_asset_url')) {
    function app_asset_url(string $path = ''): string
    {
        $base = rtrim(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
    }
}

$isStandalone = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__;
?>

<?php if ($isStandalone): ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Scan QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_asset_url('assets/css/app.css'), ENT_QUOTES) ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.4/build/html5-qrcode.min.js"></script>
    <script>
      window.scanQrDummyAttendanceData = [
        { code: "CLOCK_IN", attendanceStatus: "Clocked In" },
        { code: "CLOCK_OUT", attendanceStatus: "Clocked Out" }
      ];
    </script>
    <script defer src="<?= htmlspecialchars(app_asset_url('assets/js/app.js'), ENT_QUOTES) ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  </head>
  <body class="scan-page-body">
    <main class="scan-page min-vh-100 py-4 d-flex align-items-center">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-xl-10">
            <h1 class="scan-title mb-2">Scan QR Code</h1>
            <p class="scan-lead mb-3">Point your camera at the workplace QR code to clock in or out.</p>
            <div class="mt-3">
<?php endif; ?>

<div id="scan-qr-card" x-data="scanQrCard()" class="card scan-card border-0 shadow-sm">
  <div id="scanner-ui" class="card-body p-4 p-md-5 text-center">
    <?php // Camera preview and scan controls live here. ?>
    <div x-show="!isScanning" x-cloak>
      <div class="scan-icon d-inline-flex align-items-center justify-content-center rounded-4 mb-3">
        <svg viewBox="0 0 24 24" width="37" height="37" fill="none" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
          <path d="M3 7V3h4" />
          <path d="M21 7V3h-4" />
          <path d="M3 17v4h4" />
          <path d="M21 17v4h-4" />
          <rect x="7" y="7" width="10" height="10" rx="1" />
        </svg>
      </div>

      <h2 x-text="config.ui.readyTitle" class="staff-section-title scan-title mt-4 mb-3"></h2>
      <p x-text="config.ui.readyDescription" class="scan-muted mb-4"></p>

      <button
        type="button"
        @click="startScan()"
        onclick="startScan()"
        class="btn scan-open-btn btn-lg px-4 mb-3"
        x-text="config.ui.openCameraLabel"
      ></button>
    </div>

    <?php // This is the live camera preview box shown during scanning. ?>
    <div x-show="isScanning" x-cloak class="text-center">
      <div id="reader" x-ref="reader" class="mx-auto"></div>
      
      <div x-show="cameras.length > 1" class="mt-3 mx-auto" style="max-width: 280px;">
        <label for="camera-select" class="form-label text-muted small mb-1">Switch Camera:</label>
        <select
          id="camera-select"
          class="form-select form-select-sm"
          @change="switchCamera($event.target.value)"
        >
          <template x-for="cam in cameras" :key="cam.id">
            <option :value="cam.id" x-text="cam.label || 'Camera ' + cam.id" :selected="cam.id === activeCameraId"></option>
          </template>
        </select>
      </div>

      <button
        type="button"
        @click="stopScanner"
        class="btn scan-demo-btn mt-3"
        x-text="config.ui.stopCameraLabel"
      ></button>
    </div>
  </div>

  <?php // Bootstrap modal for scan success and scan errors. ?>
  <div
    x-ref="feedbackModal"
    class="modal fade"
    id="scanFeedbackModal"
    tabindex="-1"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header" :class="modalVariant === 'success' ? 'bg-success text-white' : modalVariant === 'info' ? 'bg-info text-white' : 'bg-danger text-white'">
          <h2 class="modal-title fs-5 fw-bold" x-text="modalTitle"></h2>
          <button
            type="button"
            class="btn-close"
            :class="modalVariant === 'success' || modalVariant === 'info' ? 'btn-close-white' : 'btn-close-white'"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <!-- QR Data Details -->
          <div class="qr-data-details bg-light p-3 rounded" x-show="decodedQrValue">
            <h6 class="fw-bold mb-2">QR Code Data:</h6>
            <div class="qr-value-display bg-white p-2 rounded border mb-3">
              <code class="text-monospace" x-text="decodedQrValue" style="word-break: break-all; font-size: 0.85rem;"></code>
            </div>
            
            <div class="row g-2 text-sm">
              <div class="col-auto">
                <small class="text-body-secondary d-block">
                  <strong>Status:</strong>
                </small>
                <small x-text="result"></small>
              </div>
              <div class="col-auto" x-show="scannedAt">
                <small class="text-body-secondary d-block">
                  <strong>Scanned At:</strong>
                </small>
                <small x-text="scannedAt ? new Date(scannedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }) : ''"></small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if ($isStandalone): ?>
            </div>
          </div>
        </div>
      </div>
    </main>
  </body>
</html>
<?php endif; ?>


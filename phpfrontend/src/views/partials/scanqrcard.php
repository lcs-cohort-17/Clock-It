<?php
// Reusable Scan QR card markup.
// When opened directly, this file renders a full standalone page.
// When included from scanqrpage.php, it renders only the card fragment.

$isStandalone = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__;
?>

<?php if ($isStandalone): ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Scan QR Code</title>
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
        @click="startScanner"
        class="btn scan-open-btn btn-lg px-4 mb-3"
        x-text="config.ui.openCameraLabel"
      ></button>
    </div>

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

  <div
    x-show="error"
    x-cloak
    x-text="error"
    class="alert scan-alert-danger mb-0"
  ></div>
  <div
    x-show="result"
    x-cloak
    x-text="result"
    class="alert scan-alert-success mt-3 mb-0"
  ></div>

  <!-- Success Modal -->
  <div
    class="modal fade"
    :class="{ 'show d-block': showSuccessModal }"
    x-show="showSuccessModal"
    tabindex="-1"
    role="dialog"
    aria-modal="true"
    style="background: rgba(0, 0, 0, 0.5); z-index: 1050;"
    x-cloak
  >
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-body text-center p-5">
          <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-4 animate-scale" style="width: 80px; height: 80px;">
            <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polyline points="20 6 9 17 4 12" />
            </svg>
          </div>
          <h3 class="fw-bold mb-2 text-dark" x-text="successTitle"></h3>
          <p class="text-muted mb-4" x-text="successMessage"></p>
          <button type="button" class="btn btn-success w-100 py-2 rounded-3 fw-semibold shadow-sm" style="background: #198754; border: none;" @click="showSuccessModal = false">
            Done
          </button>
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


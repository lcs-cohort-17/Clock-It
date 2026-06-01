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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.4/build/html5-qrcode.min.js"></script>
    <script defer src="/assets/js/app.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  </head>
  <body class="scan-page-body">
    <main class="scan-page min-vh-100 py-4 d-flex align-items-center">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-xl-10">
            <h1 class="display-5 fw-bold scan-title mb-2">Scan QR Code</h1>
            <p class="fs-4 scan-lead mb-3">Point your camera at the workplace QR code to clock in or out.</p>
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

      <h2 x-text="config.ui.readyTitle" class="h1 fw-bold scan-title mt-4 mb-3"></h2>
      <p x-text="config.ui.readyDescription" class="fs-5 scan-muted mb-4"></p>

      <button
        type="button"
        @click="startScanner"
        class="btn scan-open-btn btn-lg px-4 mb-3"
        x-text="config.ui.openCameraLabel"
      ></button>

      <hr class="my-4">

      <div class="text-center">
        <p x-text="config.ui.demoHint" class="fs-5 scan-muted"></p>
        <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">
          <button
            type="button"
            @click="handleDemoScan(config.codes.clockIn)"
            class="btn scan-demo-btn btn-lg"
            x-text="config.ui.demoClockInLabel"
          ></button>

          <button
            type="button"
            @click="handleDemoScan(config.codes.clockOut)"
            class="btn scan-demo-btn btn-lg"
            x-text="config.ui.demoClockOutLabel"
          ></button>
        </div>
      </div>
    </div>

    <?php // This is the live camera preview box shown during scanning. ?>
    <div x-show="isScanning" x-cloak class="text-center">
      <div id="reader" x-ref="reader" class="mx-auto"></div>
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
        <div class="modal-header" :class="modalVariant === 'success' ? 'bg-success text-white' : 'bg-danger text-white'">
          <h2 class="modal-title fs-5 fw-bold" x-text="modalTitle"></h2>
          <button
            type="button"
            class="btn-close"
            :class="modalVariant === 'success' ? 'btn-close-white' : ''"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <p class="mb-2" x-text="modalMessage"></p>
          <p class="small text-body-secondary mb-0" x-show="result" x-text="result"></p>
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


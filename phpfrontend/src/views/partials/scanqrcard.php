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
    <div x-show="!isScanning" data-scan-ready>
      <div class="scan-icon d-inline-flex align-items-center justify-content-center rounded-4 mb-3">
        <svg viewBox="0 0 24 24" width="37" height="37" fill="none" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
          <path d="M3 7V3h4" />
          <path d="M21 7V3h-4" />
          <path d="M3 17v4h4" />
          <path d="M21 17v4h-4" />
          <rect x="7" y="7" width="10" height="10" rx="1" />
        </svg>
      </div>

      <h2 x-text="config.ui.readyTitle" class="staff-section-title scan-title mt-4 mb-3">Ready to scan</h2>
      <p x-text="config.ui.readyDescription" class="scan-muted mb-4">Camera works offline. Events will sync automatically.</p>

      <button
        type="button"
        @click="startScanner"
        data-scan-open
        class="btn scan-open-btn btn-lg px-4 mb-3"
        x-text="config.ui.openCameraLabel"
      >Open camera</button>

      <hr class="my-4">

      <div class="text-center">
        <p x-text="config.ui.demoHint" class="scan-muted">No camera? Try demo scan:</p>
        <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">
          <button
            type="button"
            @click="handleDemoScan(config.codes.clockIn)"
            data-demo-code="CLOCK_IN"
            class="btn scan-demo-btn btn-lg"
            x-text="config.ui.demoClockInLabel"
          >Demo: Clock In</button>

          <button
            type="button"
            @click="handleDemoScan(config.codes.clockOut)"
            data-demo-code="CLOCK_OUT"
            class="btn scan-demo-btn btn-lg"
            x-text="config.ui.demoClockOutLabel"
          >Demo: Clock Out</button>
        </div>
      </div>
    </div>

    <div x-show="isScanning" x-cloak class="text-center" data-scan-active>
      <div id="reader" x-ref="reader" class="mx-auto"></div>
      <button
        type="button"
        @click="stopScanner"
        data-scan-stop
        class="btn scan-demo-btn mt-3"
        x-text="config.ui.stopCameraLabel"
      >Stop camera</button>
    </div>
  </div>

  <div
    x-show="error"
    x-cloak
    x-text="error"
    data-scan-error
    class="alert scan-alert-danger mb-0"
  ></div>
  <div
    x-show="result"
    x-cloak
    x-text="result"
    data-scan-result
    class="alert scan-alert-success mt-3 mb-0"
  ></div>
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


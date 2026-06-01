<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Scan QR Code</title>
  </head>
  <body class="scan-page-body">
    <main id="app" class="scan-page min-vh-100 py-4 d-flex align-items-center">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-xl-10">
            <h1 class="display-5 fw-bold scan-title mb-2">Scan QR Code</h1>
            <p class="fs-4 scan-lead mb-3">Point your camera at the workplace QR code to clock in or out.</p>

            <div class="mt-3">
          <?php include __DIR__ . '/scanqrcard.php'; ?>
            </div>
          </div>
        </div>
      </div>
    </main>

  </body>
</html>

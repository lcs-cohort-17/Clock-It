<?php ob_start(); ?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h2 class="mb-4 text-center">
                        Scan QR Code
                    </h2>

                    <div id="reader" style="width:100%;"></div>

                    <div
                        id="scan-result"
                        class="alert alert-success mt-3 d-none">
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    function onScanSuccess(decodedText) {

        const result = document.getElementById('scan-result');

        result.classList.remove('d-none');

        result.innerHTML =
            'Scanned QR Code: <strong>' + decodedText + '</strong>';
    }

    const html5QrCode = new Html5Qrcode("reader");

    html5QrCode.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: 250
        },
        onScanSuccess
    );
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/app.php';
?>
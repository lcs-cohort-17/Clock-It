<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>QR Clock System | One-Time Secure Check-in</title>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dropdown" id="clockDropdown">
        <button class="dropdown-btn" id="dropdownBtn" aria-haspopup="true" aria-expanded="false">
            <i class="bi bi-clock-history btn-icon"></i> Clock Actions
            <i class="bi bi-chevron-down dropdown-arrow"></i>
        </button>
        <div class="dropdown-menu" role="menu">
            <div class="dropdown-item" data-type="in">
                <i class="bi bi-check-circle-fill item-icon" style="color: #2e7d32;"></i> Clock In QR
            </div>
            <div class="dropdown-item" data-type="out">
                <i class="bi bi-x-circle-fill item-icon" style="color: #c62828;"></i> Clock Out QR
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="qrModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modalTitle" class="modal-title">
                    <i class="bi bi-qr-code"></i> Secure QR Code
                </h2>
                <button class="modal-close" id="closeModalBtn" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="qrTypeIndicator" class="qr-type-badge">
                    <i class="bi bi-hourglass-split"></i> <span>Loading...</span>
                </div>
                <div class="qr-container">
                    <div id="qrcode" class="qr-wrapper"></div>
                </div>
                <div id="statusArea">
                    <div id="expiryTimer" class="countdown-timer">--</div>
                    <div id="feedbackMessage" class="feedback-message"></div>
                </div>
                <div class="button-group">
                    <button id="refreshQrBtn" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat"></i> Refresh QR
                    </button>
                    <button id="simulateScanBtn" class="btn btn-warning">
                        <i class="bi bi-phone"></i> Simulate Scan
                    </button>
                </div>
                <div class="info-note">
                    <i class="bi bi-shield-lock-fill info-icon"></i> One-time use · Expires in 60 seconds
                </div>
            </div>
        </div>
    </div>

    <!-- Custom JavaScript -->
    <script src="js/app.js"></script>
</body>
</html>
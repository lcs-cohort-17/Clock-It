// DOM Elements
const dropdown = document.getElementById('clockDropdown');
const dropdownBtn = document.getElementById('dropdownBtn');
const dropdownItems = document.querySelectorAll('.dropdown-item');
const modal = document.getElementById('qrModal');
const closeModalBtn = document.getElementById('closeModalBtn');
const refreshBtn = document.getElementById('refreshQrBtn');
const simulateScanBtn = document.getElementById('simulateScanBtn');
const qrContainer = document.getElementById('qrcode');
const expiryTimerDiv = document.getElementById('expiryTimer');
const feedbackDiv = document.getElementById('feedbackMessage');
const qrTypeIndicator = document.getElementById('qrTypeIndicator');

// State
let currentToken = null;
let currentType = null;
let countdownInterval = null;
let expiryTimeout = null;
let qrCodeInstance = null;
let isTokenValid = false;
let tokenUsedFlag = false;

// Helper: Clear all timers
function clearAllTimers() {
    if (countdownInterval) clearInterval(countdownInterval);
    if (expiryTimeout) clearTimeout(expiryTimeout);
    countdownInterval = null;
    expiryTimeout = null;
}

// Helper: Destroy existing QR
function destroyQR() {
    if (qrContainer) qrContainer.innerHTML = '';
    qrCodeInstance = null;
}

// Helper: Update UI based on validity
function updateUIAfterValidity(valid, message, isExpired = false) {
    isTokenValid = valid;
    if (!valid) {
        if (isExpired) {
            expiryTimerDiv.innerHTML = '<i class="bi bi-clock-history"></i> Expired';
            expiryTimerDiv.className = 'countdown-timer status-expired';
            feedbackDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i> ${message || 'QR code has expired. Please refresh.'}`;
        } else {
            expiryTimerDiv.innerHTML = '<i class="bi bi-lock-fill"></i> Used / Invalid';
            expiryTimerDiv.className = 'countdown-timer status-invalid';
            feedbackDiv.innerHTML = `<i class="bi bi-x-octagon-fill"></i> ${message || 'This QR code is no longer usable.'}`;
        }
        // Add visual overlay if not present
        const wrapper = document.querySelector('.qr-wrapper');
        if (wrapper && !wrapper.querySelector('.qr-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'qr-overlay';
            overlay.innerHTML = `<i class="bi bi-slash-circle" style="font-size: 2rem;"></i><span>INVALID</span>`;
            wrapper.style.position = 'relative';
            wrapper.appendChild(overlay);
        }
    } else {
        const overlay = document.querySelector('.qr-overlay');
        if (overlay) overlay.remove();
        expiryTimerDiv.className = 'countdown-timer status-valid';
        feedbackDiv.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${message || 'QR ready to scan (one-time use)'}`;
    }
}

// Invalidate QR (used or expired)
function invalidateQR(reason, msg) {
    if (!currentToken) return;
    clearAllTimers();
    const isExpired = (reason === 'expired');
    updateUIAfterValidity(false, msg, isExpired);
    tokenUsedFlag = true;
    isTokenValid = false;
}

// Generate new QR code by calling the backend
async function generateQRCode(type) {
    try {
        destroyQR();
        clearAllTimers();
        tokenUsedFlag = false;
        isTokenValid = true;
        feedbackDiv.innerHTML = '<i class="bi bi-arrow-repeat"></i> Generating secure QR...';
        expiryTimerDiv.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading';
        qrTypeIndicator.innerHTML = type === 'in' 
            ? '<i class="bi bi-check-circle-fill"></i> <span>CLOCK IN QR · One-time use</span>' 
            : '<i class="bi bi-x-circle-fill"></i> <span>CLOCK OUT QR · One-time use</span>';

        const response = await fetch('/api/generate_qr', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type })
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.error || `HTTP ${response.status}`);
        }

        const data = await response.json();
        currentToken = data.token;
        currentType = data.type;

        const scanUrl = `${window.location.origin}/api/qr/scan?token=${encodeURIComponent(currentToken)}`;
        
        qrContainer.innerHTML = '';
        qrCodeInstance = new QRCode(qrContainer, {
            text: scanUrl,
            width: 220,
            height: 220,
            colorDark: "#093C5D",
            colorLight: "#FFFFFF",
            correctLevel: QRCode.CorrectLevel.M
        });

        const expiresAt = new Date(data.expires_at).getTime();
        let secondsLeft = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
        
        const updateCountdown = () => {
            if (secondsLeft <= 0) {
                clearAllTimers();
                if (!tokenUsedFlag && currentToken) {
                    invalidateQR('expired', 'QR code expired. Click Refresh for a new one.');
                }
                expiryTimerDiv.innerHTML = '<i class="bi bi-clock-history"></i> Expired';
                return;
            }
            expiryTimerDiv.innerHTML = `<i class="bi bi-hourglass-bottom"></i> Valid for: ${secondsLeft}s`;
            secondsLeft--;
        };
        updateCountdown();
        countdownInterval = setInterval(updateCountdown, 1000);
        
        expiryTimeout = setTimeout(() => {
            if (!tokenUsedFlag && currentToken) {
                invalidateQR('expired', 'QR code expired (fallback). Please refresh.');
            }
            clearAllTimers();
        }, secondsLeft * 1000 + 500);
        
        updateUIAfterValidity(true, 'Ready to scan. One-time use only.');
        
    } catch (error) {
        console.error('QR Generation Error:', error);
        feedbackDiv.innerHTML = `<i class="bi bi-exclamation-diamond-fill"></i> Failed to generate QR: ${error.message}`;
        qrContainer.innerHTML = '<div class="qr-placeholder"><i class="bi bi-qr-code-scan"></i> Error</div>';
        expiryTimerDiv.innerHTML = '<i class="bi bi-x-circle"></i> Error';
        isTokenValid = false;
    }
}

// Simulate scanning (calls the backend validation endpoint)
async function simulateScan() {
    if (!currentToken) {
        feedbackDiv.innerHTML = '<i class="bi bi-info-circle-fill"></i> No active QR code. Generate one first.';
        return;
    }
    
    if (!isTokenValid || tokenUsedFlag) {
        feedbackDiv.innerHTML = '<i class="bi bi-x-octagon-fill"></i> This QR code is already used or expired. Please refresh.';
        return;
    }
    
    try {
        const response = await fetch(`/api/qr/scan?token=${encodeURIComponent(currentToken)}`);
        const result = await response.json();
        
        if (result.valid) {
            tokenUsedFlag = true;
            isTokenValid = false;
            clearAllTimers();
            const action = result.action || (result.type === 'in' ? 'Clock In' : 'Clock Out');
            feedbackDiv.innerHTML = `<i class="bi bi-check-circle-fill"></i> Successfully ${action} recorded! QR code is now invalid.`;
            expiryTimerDiv.innerHTML = '<i class="bi bi-check-lg"></i> Used';
            expiryTimerDiv.className = 'countdown-timer status-invalid';
            const wrapper = document.querySelector('.qr-wrapper');
            if (wrapper && !wrapper.querySelector('.qr-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'qr-overlay';
                overlay.innerHTML = `<i class="bi bi-check-lg" style="font-size: 2rem;"></i><span>USED</span>`;
                wrapper.appendChild(overlay);
            }
        } else {
            const errorMsg = result.error || 'Invalid QR scan';
            feedbackDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i> ${errorMsg}`;
            if (errorMsg.includes('already been used') || errorMsg.includes('expired')) {
                invalidateQR('used', errorMsg);
            }
        }
    } catch (err) {
        feedbackDiv.innerHTML = `<i class="bi bi-wifi-off"></i> Scan simulation failed: ${err.message}`;
    }
}

// Reset modal state
function resetModalState() {
    clearAllTimers();
    destroyQR();
    currentToken = null;
    tokenUsedFlag = false;
    isTokenValid = false;
    feedbackDiv.innerHTML = '';
    expiryTimerDiv.innerHTML = '--';
    qrTypeIndicator.innerHTML = '<i class="bi bi-qr-code"></i> <span>Select an option</span>';
    const overlay = document.querySelector('.qr-overlay');
    if (overlay) overlay.remove();
}

// Open modal with specific type
async function openModalWithType(type) {
    resetModalState();
    modal.classList.add('active');
    await generateQRCode(type);
}

// Close modal
function closeModal() {
    modal.classList.remove('active');
    resetModalState();
}

// Event Listeners
dropdownBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('active');
    const expanded = dropdown.classList.contains('active');
    dropdownBtn.setAttribute('aria-expanded', expanded);
});

document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('active');
        dropdownBtn.setAttribute('aria-expanded', 'false');
    }
});

dropdownItems.forEach(item => {
    item.addEventListener('click', async () => {
        const type = item.getAttribute('data-type');
        dropdown.classList.remove('active');
        dropdownBtn.setAttribute('aria-expanded', 'false');
        await openModalWithType(type);
    });
});

closeModalBtn.addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

refreshBtn.addEventListener('click', async () => {
    if (!currentType) {
        feedbackDiv.innerHTML = '<i class="bi bi-info-circle-fill"></i> Please select Clock In or Clock Out from dropdown first.';
        return;
    }
    await generateQRCode(currentType);
});

simulateScanBtn.addEventListener('click', simulateScan);

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('active')) {
        closeModal();
    }
});

resetModalState();
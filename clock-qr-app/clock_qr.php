<?php
// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'generate_qr') {
        $type = $_POST['type'] ?? 'in'; // 'in' or 'out'
        $token = bin2hex(random_bytes(16));
        $timestamp = time();
        $expires = $timestamp + 60; // 60-second expiry

        // Store token in session
        session_start();
        $_SESSION['qr_tokens'][$token] = [
            'type'    => $type,
            'expires' => $expires,
            'used'    => false,
        ];

        $payload = json_encode([
            'token'   => $token,
            'type'    => $type,
            'expires' => $expires,
        ]);

        echo json_encode([
            'success' => true,
            'token'   => $token,
            'type'    => $type,
            'expires' => $expires,
            'payload' => $payload,
        ]);
        exit;
    }

    if ($_POST['action'] === 'validate_qr') {
        session_start();
        $token = $_POST['token'] ?? '';

        if (!isset($_SESSION['qr_tokens'][$token])) {
            echo json_encode(['success' => false, 'message' => 'Invalid QR code.']);
            exit;
        }

        $data = $_SESSION['qr_tokens'][$token];

        if ($data['used']) {
            echo json_encode(['success' => false, 'message' => 'QR code already used.']);
            exit;
        }

        if (time() > $data['expires']) {
            echo json_encode(['success' => false, 'message' => 'QR code has expired.']);
            exit;
        }

        // Mark as used (single-use enforcement)
        $_SESSION['qr_tokens'][$token]['used'] = true;

        echo json_encode([
            'success' => true,
            'type'    => $data['type'],
            'message' => 'Clock ' . strtoupper($data['type']) . ' recorded successfully.',
        ]);
        exit;
    }
}

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clock In / Out — QR</title>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- QR Code library -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@600;700;800&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:        #0d0f14;
    --surface:   #161a23;
    --border:    #252b38;
    --accent-in: #00e5a0;
    --accent-out:#ff6b6b;
    --text:      #e8eaf0;
    --muted:     #6b7280;
    --radius:    14px;
  }

  body {
    min-height: 100vh;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'DM Mono', monospace;
    color: var(--text);
    padding: 24px;
  }

  /* ── Noise texture overlay ── */
  body::before {
    content: '';
    position: fixed; inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
    pointer-events: none; z-index: 0;
  }

  /* ── Demo card ── */
  .demo-card {
    position: relative; z-index: 1;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 48px 40px;
    max-width: 420px; width: 100%;
    text-align: center;
    box-shadow: 0 24px 64px rgba(0,0,0,.5);
  }

  .brand {
    font-family: 'Syne', sans-serif;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.5px;
    margin-bottom: 6px;
  }

  .brand span { color: var(--accent-in); }

  .subtitle {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 36px;
    letter-spacing: 0.04em;
  }

  /* ── Dropdown wrapper ── */
  .dropdown { position: relative; display: inline-block; }

  .dropdown-btn {
    display: inline-flex; align-items: center; gap: 10px;
    background: linear-gradient(135deg, #1e2333 0%, #252b3a 100%);
    border: 1px solid var(--border);
    color: var(--text);
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.03em;
    padding: 14px 22px;
    border-radius: var(--radius);
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
  }

  .dropdown-btn:hover {
    border-color: var(--accent-in);
    box-shadow: 0 0 0 3px rgba(0,229,160,.12);
  }

  .dropdown-btn .chevron {
    transition: transform .25s;
    font-size: 16px;
    color: var(--muted);
  }

  .dropdown-btn.open .chevron { transform: rotate(180deg); }

  .dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    left: 50%; transform: translateX(-50%);
    background: #1a1f2e;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    min-width: 200px;
    box-shadow: 0 16px 48px rgba(0,0,0,.6);
    opacity: 0; pointer-events: none;
    transform: translateX(-50%) translateY(-8px);
    transition: opacity .2s, transform .2s;
    z-index: 100;
  }

  .dropdown-menu.open {
    opacity: 1; pointer-events: all;
    transform: translateX(-50%) translateY(0);
  }

  .dropdown-item {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 20px;
    cursor: pointer;
    font-size: 13px;
    font-family: 'Syne', sans-serif;
    font-weight: 600;
    transition: background .15s;
    border: none; background: none; width: 100%;
    color: var(--text); text-align: left;
  }

  .dropdown-item:hover { background: rgba(255,255,255,.05); }

  .dropdown-item.clock-in  .dot { background: var(--accent-in); }
  .dropdown-item.clock-out .dot { background: var(--accent-out); }

  .dot {
    width: 8px; height: 8px;
    border-radius: 50%; flex-shrink: 0;
  }

  /* ── Modal ── */
  .overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.75);
    backdrop-filter: blur(6px);
    z-index: 200;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none;
    transition: opacity .3s;
    padding: 20px;
  }

  .overlay.open { opacity: 1; pointer-events: all; }

  .modal {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 36px 32px 32px;
    max-width: 380px; width: 100%;
    text-align: center;
    box-shadow: 0 32px 80px rgba(0,0,0,.7);
    transform: translateY(20px) scale(.97);
    transition: transform .3s;
    position: relative;
  }

  .overlay.open .modal { transform: translateY(0) scale(1); }

  .modal-close {
    position: absolute; top: 16px; right: 16px;
    background: none; border: none; cursor: pointer;
    color: var(--muted); font-size: 20px;
    line-height: 1; padding: 4px;
    transition: color .15s;
  }

  .modal-close:hover { color: var(--text); }

  .modal-badge {
    display: inline-flex; align-items: center; gap: 8px;
    font-family: 'Syne', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: .08em; text-transform: uppercase;
    padding: 6px 14px; border-radius: 100px;
    margin-bottom: 20px;
  }

  .modal-badge.in  { background: rgba(0,229,160,.12); color: var(--accent-in);  border: 1px solid rgba(0,229,160,.25); }
  .modal-badge.out { background: rgba(255,107,107,.12); color: var(--accent-out); border: 1px solid rgba(255,107,107,.25); }

  .modal-title {
    font-family: 'Syne', sans-serif;
    font-size: 20px; font-weight: 800;
    margin-bottom: 4px;
  }

  .modal-hint {
    font-size: 11px; color: var(--muted);
    margin-bottom: 28px; letter-spacing: .03em;
  }

  /* ── QR container ── */
  .qr-wrap {
    position: relative;
    width: 220px; height: 220px;
    margin: 0 auto 24px;
  }

  .qr-inner {
    width: 220px; height: 220px;
    background: #fff;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    border: 4px solid;
    transition: border-color .3s;
    position: relative;
  }

  .qr-inner.in  { border-color: var(--accent-in); }
  .qr-inner.out { border-color: var(--accent-out); }

  #qrcode canvas, #qrcode img { display: block; }

  /* corner accents */
  .qr-inner::before, .qr-inner::after {
    content: '';
    position: absolute;
    width: 20px; height: 20px;
    border-radius: 4px;
    opacity: .6;
  }

  /* ── Timer ── */
  .timer-bar-wrap {
    background: var(--border);
    border-radius: 100px;
    height: 5px;
    overflow: hidden;
    margin-bottom: 16px;
  }

  .timer-bar {
    height: 100%;
    border-radius: 100px;
    width: 100%;
    transition: width 1s linear, background .5s;
  }

  .timer-bar.in  { background: var(--accent-in); }
  .timer-bar.out { background: var(--accent-out); }

  .timer-label {
    font-size: 11px; color: var(--muted);
    margin-bottom: 20px; letter-spacing: .03em;
  }

  .timer-label span { font-weight: 500; }

  /* ── State messages ── */
  .state-msg {
    display: none;
    flex-direction: column; align-items: center; gap: 10px;
    padding: 28px 0;
  }

  .state-msg.show { display: flex; }

  .state-msg i { font-size: 40px; }

  .state-msg p {
    font-family: 'Syne', sans-serif;
    font-weight: 700; font-size: 15px;
  }

  .state-msg small { font-size: 11px; color: var(--muted); }

  /* ── Refresh button ── */
  .btn-refresh {
    display: inline-flex; align-items: center; gap: 8px;
    border: none; border-radius: 10px;
    padding: 12px 24px;
    font-family: 'Syne', sans-serif;
    font-weight: 700; font-size: 13px;
    letter-spacing: .03em; cursor: pointer;
    transition: opacity .2s, transform .15s;
  }

  .btn-refresh:hover { opacity: .85; transform: translateY(-1px); }
  .btn-refresh:active { transform: translateY(0); }

  .btn-refresh.in  { background: var(--accent-in);  color: #0d0f14; }
  .btn-refresh.out { background: var(--accent-out); color: #fff; }

  .btn-refresh i { font-size: 15px; }

  /* spinning icon */
  @keyframes spin { to { transform: rotate(360deg); } }
  .spin { animation: spin .7s linear infinite; }

  /* ── Loading skeleton ── */
  .qr-loading {
    position: absolute; inset: 4px;
    background: #fff;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #ccc; font-size: 32px;
  }

  /* ── Error state ── */
  .error-msg {
    display: none;
    background: rgba(255,107,107,.1);
    border: 1px solid rgba(255,107,107,.3);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 12px; color: var(--accent-out);
    margin-bottom: 16px;
    text-align: left;
  }

  .error-msg.show { display: flex; gap: 8px; align-items: flex-start; }
  .error-msg i { flex-shrink: 0; margin-top: 1px; }
</style>
</head>
<body>

<!-- ── Demo card ── -->
<div class="demo-card">
  <div class="brand">Time<span>Track</span></div>
  <div class="subtitle">QR-BASED ATTENDANCE SYSTEM</div>

  <div class="dropdown" id="dropdown">
    <button class="dropdown-btn" id="dropdownBtn" onclick="toggleDropdown()">
      <i class="bi bi-qr-code"></i>
      Clock In / Out
      <i class="bi bi-chevron-down chevron"></i>
    </button>
    <div class="dropdown-menu" id="dropdownMenu">
      <button class="dropdown-item clock-in" onclick="openModal('in')">
        <span class="dot"></span>
        <i class="bi bi-box-arrow-in-right"></i>
        Clock In QR
      </button>
      <button class="dropdown-item clock-out" onclick="openModal('out')">
        <span class="dot"></span>
        <i class="bi bi-box-arrow-right"></i>
        Clock Out QR
      </button>
    </div>
  </div>
</div>

<!-- ── Modal ── -->
<div class="overlay" id="overlay" onclick="handleOverlayClick(event)">
  <div class="modal" id="modal">
    <button class="modal-close" onclick="closeModal()">
      <i class="bi bi-x-lg"></i>
    </button>

    <div class="modal-badge in" id="modalBadge">
      <i class="bi bi-box-arrow-in-right"></i>
      <span id="badgeLabel">Clock In</span>
    </div>

    <div class="modal-title" id="modalTitle">Scan to Clock In</div>
    <div class="modal-hint">Single-use · Expires in 60 seconds</div>

    <!-- Error -->
    <div class="error-msg" id="errorMsg">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <span id="errorText">Failed to generate QR code. Please try again.</span>
    </div>

    <!-- QR area -->
    <div id="qrArea">
      <div class="qr-wrap">
        <div class="qr-inner in" id="qrInner">
          <div class="qr-loading" id="qrLoading">
            <i class="bi bi-arrow-repeat spin"></i>
          </div>
          <div id="qrcode"></div>
        </div>
      </div>

      <div class="timer-bar-wrap">
        <div class="timer-bar in" id="timerBar"></div>
      </div>
      <div class="timer-label">Expires in <span id="timerLabel">60</span>s</div>

      <button class="btn-refresh in" id="refreshBtn" onclick="refreshQR()">
        <i class="bi bi-arrow-clockwise" id="refreshIcon"></i>
        Refresh QR Code
      </button>
    </div>

    <!-- Used state -->
    <div class="state-msg" id="usedState">
      <i class="bi bi-check-circle-fill" style="color:var(--accent-in)"></i>
      <p>QR Code Used</p>
      <small>This code has already been scanned.</small>
    </div>

    <!-- Expired state -->
    <div class="state-msg" id="expiredState">
      <i class="bi bi-clock-history" style="color:var(--accent-out)"></i>
      <p>QR Code Expired</p>
      <small>Generate a new code to continue.</small>
    </div>
  </div>
</div>

<script>
// ── State ──────────────────────────────────────────────────────────────
let currentType   = 'in';
let currentToken  = null;
let expiresAt     = null;
let timerInterval = null;
const EXPIRE_SECS = 60;

// ── Dropdown ───────────────────────────────────────────────────────────
function toggleDropdown() {
  const btn  = document.getElementById('dropdownBtn');
  const menu = document.getElementById('dropdownMenu');
  btn.classList.toggle('open');
  menu.classList.toggle('open');
}

document.addEventListener('click', e => {
  if (!document.getElementById('dropdown').contains(e.target)) {
    document.getElementById('dropdownBtn').classList.remove('open');
    document.getElementById('dropdownMenu').classList.remove('open');
  }
});

// ── Modal open/close ───────────────────────────────────────────────────
function openModal(type) {
  currentType = type;
  document.getElementById('dropdownBtn').classList.remove('open');
  document.getElementById('dropdownMenu').classList.remove('open');
  document.getElementById('overlay').classList.add('open');
  applyTypeTheme(type);
  generateQR();
}

function closeModal() {
  document.getElementById('overlay').classList.remove('open');
  clearInterval(timerInterval);
  resetStates();
}

function handleOverlayClick(e) {
  if (e.target === document.getElementById('overlay')) closeModal();
}

function applyTypeTheme(type) {
  const isIn = type === 'in';
  const badge = document.getElementById('modalBadge');
  const inner = document.getElementById('qrInner');
  const bar   = document.getElementById('timerBar');
  const rBtn  = document.getElementById('refreshBtn');

  badge.className = `modal-badge ${type}`;
  badge.querySelector('i').className = isIn ? 'bi bi-box-arrow-in-right' : 'bi bi-box-arrow-right';
  document.getElementById('badgeLabel').textContent = isIn ? 'Clock In' : 'Clock Out';
  document.getElementById('modalTitle').textContent = `Scan to Clock ${isIn ? 'In' : 'Out'}`;

  inner.className = `qr-inner ${type}`;
  bar.className   = `timer-bar ${type}`;
  rBtn.className  = `btn-refresh ${type}`;
}

// ── Generate QR ────────────────────────────────────────────────────────
function generateQR() {
  resetStates();
  showLoading(true);
  clearInterval(timerInterval);

  const formData = new FormData();
  formData.append('action', 'generate_qr');
  formData.append('type', currentType);

  fetch(window.location.href, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (!data.success) throw new Error('Server error');
      currentToken = data.token;
      expiresAt    = data.expires;
      renderQR(data.payload);
      startTimer();
      showLoading(false);
    })
    .catch(() => {
      showLoading(false);
      showError('Failed to generate QR code. Please try again.');
    });
}

function renderQR(payload) {
  const container = document.getElementById('qrcode');
  container.innerHTML = '';
  new QRCode(container, {
    text:         payload,
    width:        200,
    height:       200,
    colorDark:    '#0d0f14',
    colorLight:   '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
  });
}

// ── Timer ──────────────────────────────────────────────────────────────
function startTimer() {
  const bar   = document.getElementById('timerBar');
  const label = document.getElementById('timerLabel');
  const total = EXPIRE_SECS;

  timerInterval = setInterval(() => {
    const remaining = Math.max(0, expiresAt - Math.floor(Date.now() / 1000));
    const pct = (remaining / total) * 100;
    bar.style.width   = pct + '%';
    label.textContent = remaining;

    if (remaining <= 0) {
      clearInterval(timerInterval);
      showExpired();
    }
  }, 1000);
}

// ── Refresh ────────────────────────────────────────────────────────────
function refreshQR() {
  const icon = document.getElementById('refreshIcon');
  icon.classList.add('spin');
  setTimeout(() => icon.classList.remove('spin'), 700);
  generateQR();
}

// ── UI helpers ─────────────────────────────────────────────────────────
function showLoading(show) {
  document.getElementById('qrLoading').style.display = show ? 'flex' : 'none';
  document.getElementById('qrcode').style.display    = show ? 'none' : 'block';
}

function showError(msg) {
  document.getElementById('errorText').textContent = msg;
  document.getElementById('errorMsg').classList.add('show');
}

function showExpired() {
  document.getElementById('qrArea').style.display    = 'none';
  document.getElementById('expiredState').classList.add('show');
}

function resetStates() {
  document.getElementById('qrArea').style.display = 'block';
  document.getElementById('errorMsg').classList.remove('show');
  document.getElementById('usedState').classList.remove('show');
  document.getElementById('expiredState').classList.remove('show');
  document.getElementById('timerBar').style.width = '100%';
  document.getElementById('timerLabel').textContent = EXPIRE_SECS;
}

// ── Keyboard accessibility ─────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModal();
});
</script>
</body>
</html>

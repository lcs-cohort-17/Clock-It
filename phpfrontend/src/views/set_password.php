<?php
declare(strict_types=1);

$currentUser = is_array($user ?? null) ? $user : [];
$userName    = (string)($currentUser['name'] ?? 'there');
$firstName   = explode(' ', trim($userName))[0] ?? 'there';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Set Your Password | Clock-It') ?></title>
    <meta name="description" content="Set your personal password to secure your Clock-It account.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --navy: #093C5D;
            --navy-mid: #0f5c85;
            --navy-light: #1a7ab0;
            --bg: #f0f6fc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --green: #16a34a;
            --red: #dc2626;
            --yellow: #d97706;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background-image:
                radial-gradient(ellipse at 20% 20%, rgba(9,60,93,0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(15,92,133,0.10) 0%, transparent 50%);
        }

        .set-pwd-wrap {
            width: min(480px, 100%);
            animation: fadeSlideUp 0.4s ease both;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .set-pwd-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .set-pwd-badge .logo-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, var(--navy), var(--navy-mid));
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(9,60,93,0.35);
        }

        .set-pwd-badge .logo-icon i {
            font-size: 22px; color: #fff;
        }

        .set-pwd-badge .logo-text {
            font-size: 22px; font-weight: 800;
            color: var(--navy); letter-spacing: -0.5px;
        }

        .set-pwd-card {
            background: var(--card);
            border-radius: 20px;
            padding: 36px 32px 32px;
            box-shadow: 0 8px 40px rgba(9,60,93,0.12), 0 1px 4px rgba(0,0,0,0.06);
            border: 1px solid rgba(9,60,93,0.08);
        }

        .set-pwd-header {
            margin-bottom: 28px;
        }

        .set-pwd-header .notice-banner {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }

        .set-pwd-header .notice-banner i {
            color: #d97706; font-size: 18px; flex-shrink: 0; margin-top: 1px;
        }

        .set-pwd-header .notice-banner p {
            font-size: 13px; color: #92400e; line-height: 1.6; margin: 0;
        }

        .set-pwd-header h1 {
            font-size: 22px; font-weight: 800;
            color: var(--text); letter-spacing: -0.4px;
            margin-bottom: 4px;
        }

        .set-pwd-header p.subtitle {
            font-size: 14px; color: var(--muted);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label.form-label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: var(--text); margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap input {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 12px 44px 12px 14px;
            font: inherit;
            font-size: 14px;
            color: var(--text);
            background: #f8fafc;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .input-wrap input:focus {
            border-color: var(--navy-mid);
            box-shadow: 0 0 0 3px rgba(9,60,93,0.12);
            background: #fff;
        }

        .input-wrap .eye-btn {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--muted); cursor: pointer;
            font-size: 16px; padding: 4px;
            transition: color 0.2s;
        }

        .input-wrap .eye-btn:hover { color: var(--navy); }

        .strength-wrap {
            margin-top: 10px;
        }

        .strength-bar {
            height: 4px;
            background: var(--border);
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 4px;
        }

        .strength-fill {
            height: 100%; border-radius: 999px;
            transition: width 0.3s ease, background 0.3s ease;
        }

        .strength-label {
            font-size: 12px; font-weight: 600;
        }

        .strength-label.weak   { color: var(--red); }
        .strength-label.medium { color: var(--yellow); }
        .strength-label.strong { color: var(--green); }

        .match-hint {
            font-size: 12px; margin-top: 6px;
            color: var(--red);
            display: none;
        }

        .match-hint.visible { display: block; }

        .requirements {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 24px;
        }

        .requirements p {
            font-size: 12px; font-weight: 600;
            color: var(--navy); margin-bottom: 6px;
        }

        .requirements ul {
            list-style: none; margin: 0; padding: 0;
            display: grid; gap: 3px;
        }

        .requirements li {
            font-size: 12px; color: var(--muted);
            display: flex; align-items: center; gap: 6px;
            transition: color 0.2s;
        }

        .requirements li i { font-size: 11px; transition: color 0.2s; }
        .requirements li.ok { color: var(--green); }
        .requirements li.ok i { color: var(--green); }

        .submit-btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font: inherit;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(135deg, var(--navy), var(--navy-mid));
            color: #fff;
            transition: opacity 0.2s, transform 0.15s;
            box-shadow: 0 4px 16px rgba(9,60,93,0.3);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .submit-btn:hover:not(:disabled) { opacity: 0.92; transform: translateY(-1px); }
        .submit-btn:active:not(:disabled) { transform: scale(0.99); }
        .submit-btn:disabled { opacity: 0.55; cursor: not-allowed; }

        .error-msg {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--red);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 16px;
            display: none;
        }

        .error-msg.visible { display: block; }

        .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .logout-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .logout-link:hover { color: var(--navy); }
    </style>
</head>
<body>
    <div class="set-pwd-wrap">
        <div class="set-pwd-badge">
            <div class="logo-icon"><i class="bi bi-clock-history"></i></div>
            <span class="logo-text">Clock-It</span>
        </div>

        <div class="set-pwd-card">
            <div class="set-pwd-header">
                <div class="notice-banner">
                    <i class="bi bi-shield-exclamation"></i>
                    <p>For your security, you are required to set a personal password before accessing the system. Your temporary password will no longer work after this step.</p>
                </div>
                <h1>Welcome, <?= htmlspecialchars($firstName) ?>!</h1>
                <p class="subtitle">Choose a strong password to protect your account.</p>
            </div>

            <div class="error-msg" id="errorMsg"></div>

            <form id="setPwdForm" novalidate>
                <div class="form-group">
                    <label class="form-label" for="newPassword">New Password</label>
                    <div class="input-wrap">
                        <input type="password" id="newPassword" name="new_password"
                               autocomplete="new-password" placeholder="Create a strong password" required>
                        <button type="button" class="eye-btn" id="toggleNew" aria-label="Show password">
                            <i class="bi bi-eye" id="eyeIconNew"></i>
                        </button>
                    </div>
                    <div class="strength-wrap">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-label" id="strengthLabel"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirm Password</label>
                    <div class="input-wrap">
                        <input type="password" id="confirmPassword" name="confirm_password"
                               autocomplete="new-password" placeholder="Re-enter your password" required>
                        <button type="button" class="eye-btn" id="toggleConfirm" aria-label="Show password">
                            <i class="bi bi-eye" id="eyeIconConfirm"></i>
                        </button>
                    </div>
                    <span class="match-hint" id="matchHint">Passwords do not match.</span>
                </div>

                <div class="requirements" id="requirementsBox">
                    <p>Password must include:</p>
                    <ul>
                        <li id="req-len"><i class="bi bi-circle"></i> At least 8 characters</li>
                        <li id="req-upper"><i class="bi bi-circle"></i> One uppercase letter</li>
                        <li id="req-lower"><i class="bi bi-circle"></i> One lowercase letter</li>
                        <li id="req-num"><i class="bi bi-circle"></i> One number</li>
                    </ul>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                    <span id="btnText"><i class="bi bi-shield-check"></i>&nbsp; Set My Password</span>
                    <span class="spinner" id="spinner"></span>
                </button>
            </form>

            <a href="/logout" class="logout-link">
                <i class="bi bi-box-arrow-left"></i> Sign out and log in as a different user
            </a>
        </div>
    </div>

    <script>
    (function () {
        const newPwdInput   = document.getElementById('newPassword');
        const confirmInput  = document.getElementById('confirmPassword');
        const strengthFill  = document.getElementById('strengthFill');
        const strengthLabel = document.getElementById('strengthLabel');
        const matchHint     = document.getElementById('matchHint');
        const submitBtn     = document.getElementById('submitBtn');
        const errorMsg      = document.getElementById('errorMsg');
        const spinner       = document.getElementById('spinner');
        const btnText       = document.getElementById('btnText');

        const reqs = {
            len:   { el: document.getElementById('req-len'),   test: v => v.length >= 8 },
            upper: { el: document.getElementById('req-upper'), test: v => /[A-Z]/.test(v) },
            lower: { el: document.getElementById('req-lower'), test: v => /[a-z]/.test(v) },
            num:   { el: document.getElementById('req-num'),   test: v => /[0-9]/.test(v) },
        };

        function getStrength(v) {
            let score = 0;
            if (v.length >= 8)            score += 25;
            if (/[a-z]/.test(v))          score += 20;
            if (/[A-Z]/.test(v))          score += 20;
            if (/[0-9]/.test(v))          score += 20;
            if (/[^A-Za-z0-9]/.test(v))   score += 15;
            return Math.min(100, score);
        }

        function updateStrength() {
            const v = newPwdInput.value;
            const score = getStrength(v);

            let color = '#ef4444', label = '', cls = '';
            if (!v) { strengthFill.style.width = '0'; strengthLabel.textContent = ''; return; }
            if (score < 45)      { color = '#ef4444'; label = 'Weak';   cls = 'weak'; }
            else if (score < 75) { color = '#f59e0b'; label = 'Medium'; cls = 'medium'; }
            else                 { color = '#16a34a'; label = 'Strong'; cls = 'strong'; }

            strengthFill.style.width = score + '%';
            strengthFill.style.background = color;
            strengthLabel.textContent = label;
            strengthLabel.className = 'strength-label ' + cls;

            // Requirements
            for (const [key, req] of Object.entries(reqs)) {
                const ok = req.test(v);
                req.el.className = ok ? 'ok' : '';
                req.el.querySelector('i').className = ok ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            }

            validate();
        }

        function validate() {
            const v = newPwdInput.value;
            const c = confirmInput.value;
            const allReqsMet = Object.values(reqs).every(r => r.test(v));

            if (c && v !== c) {
                matchHint.classList.add('visible');
            } else {
                matchHint.classList.remove('visible');
            }

            submitBtn.disabled = !(allReqsMet && v === c && c.length > 0);
        }

        newPwdInput.addEventListener('input', updateStrength);
        confirmInput.addEventListener('input', validate);

        // Toggle visibility helpers
        function makeToggle(btn, input, icon) {
            btn.addEventListener('click', () => {
                const isText = input.type === 'text';
                input.type = isText ? 'password' : 'text';
                icon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
        }

        makeToggle(document.getElementById('toggleNew'),     newPwdInput, document.getElementById('eyeIconNew'));
        makeToggle(document.getElementById('toggleConfirm'), confirmInput, document.getElementById('eyeIconConfirm'));

        // Submit
        document.getElementById('setPwdForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            errorMsg.classList.remove('visible');
            btnText.style.display  = 'none';
            spinner.style.display  = 'block';
            submitBtn.disabled     = true;

            try {
                const res = await fetch('/api/set-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        new_password:     newPwdInput.value,
                        confirm_password: confirmInput.value,
                    }),
                });

                const data = await res.json();

                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    errorMsg.textContent = data.message || 'Something went wrong. Please try again.';
                    errorMsg.classList.add('visible');
                    btnText.style.display = '';
                    spinner.style.display = 'none';
                    submitBtn.disabled    = false;
                }
            } catch (err) {
                errorMsg.textContent = 'Network error. Please try again.';
                errorMsg.classList.add('visible');
                btnText.style.display = '';
                spinner.style.display = 'none';
                submitBtn.disabled    = false;
            }
        });
    })();
    </script>
</body>
</html>

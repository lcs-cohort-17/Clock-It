<?php
ob_start();

$user = [
    'name'        => 'Sarah Mthembu',
    'email'       => 'sarah@clockit.app',
    'employee_id' => 'S-101',
    'role'        => 'Staff',
    'photo'       => '',
];

function initials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach ($parts as $p) { if ($p !== '') $out .= strtoupper($p[0]); }
    return substr($out, 0, 2);
}

if (!isset($_SESSION['password_hash'])) {
    $_SESSION['password_hash'] = password_hash('password123', PASSWORD_DEFAULT);
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $file = $_FILES['profile_photo'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($file['error'] === UPLOAD_ERR_OK) {
        if (!in_array($file['type'], $allowed)) {
            $_SESSION['flash_error'] = 'Invalid file type. Please upload JPEG, PNG, GIF, or WebP.';
        } elseif ($file['size'] > $max_size) {
            $_SESSION['flash_error'] = 'File too large. Maximum 5MB allowed.';
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = __DIR__ . '/../../public/uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . uniqid() . '.' . $ext;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $photo_url = 'uploads/profiles/' . $filename;
                $_SESSION['user_photo'] = $photo_url;
                $_SESSION['flash_success'] = 'Profile photo updated successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to upload photo. Please try again.';
            }
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Load user photo from session if available
if (isset($_SESSION['user_photo'])) {
    $user['photo'] = $_SESSION['user_photo'];
}

// Handle password update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $field_errors = [];

    // Verify current password
    if (!password_verify($current, $_SESSION['password_hash'])) {
        $field_errors['current_password'] = 'Incorrect current password.';
    }

    // Validate new password
    if (strlen($new) < 8) {
        $field_errors['new_password'] = 'New password must be at least 8 characters.';
    }
    if ($new !== $confirm) {
        $field_errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!empty($field_errors)) {
        $_SESSION['field_errors'] = $field_errors;
        $_SESSION['flash_error'] = 'Please fix the errors and try again.';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Update password
    $_SESSION['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
    $_SESSION['flash_success'] = 'Password has been updated.';
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Read and clear flashes/field errors for display
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
$fieldErrors = $_SESSION['field_errors'] ?? [];
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['field_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="container py-4" x-data="profileApp()" x-init="init()">

    <!-- Header -->
    <div class="mb-4">
        <h1 class="h3 mb-1">Profile</h1>
        <p class="text-muted mb-0">Your account information.</p>
    </div>

    <!-- Account card -->
    <div class="card profile-card mb-4">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" style="display: none;" id="photoForm">
                <input type="file" id="photoInput" name="profile_photo" accept="image/*" @change="document.getElementById('photoForm').submit()">
            </form>
            <div class="d-flex align-items-center mb-4">
                <div class="avatar me-3 position-relative" style="cursor: pointer;" @click="document.getElementById('photoInput').click()">
                    <?php if (!empty($user['photo'])): ?>
                        <img src="<?= htmlspecialchars($user['photo']) ?>" alt="<?= htmlspecialchars($user['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span class="avatar-initials"><?= initials($user['name']) ?></span>
                    <?php endif; ?>
                    <div class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-2" style="cursor: pointer;">
                        <i class="bi bi-camera-fill text-white" style="font-size: 12px;"></i>
                    </div>
                </div>
                <div>
                    <h2 class="h5 mb-0"><?= htmlspecialchars($user['name']) ?></h2>
                    <span class="badge bg-secondary mt-1"><?= htmlspecialchars($user['role']) ?></span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="info-block">
                        <small class="text-muted d-block">Email</small>
                        <span><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-block">
                        <small class="text-muted d-block">Employee ID</small>
                        <span><?= htmlspecialchars($user['employee_id']) ?></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-block">
                        <small class="text-muted d-block">Role</small>
                        <span><?= htmlspecialchars($user['role']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change password card -->
    <div class="card profile-card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-1">Change password</h2>
            <p class="text-muted">Update your password securely.</p>

            <form method="post" action="" class="password-form">
                <div class="mb-3">
                    <label class="form-label">Current password</label>
                    <div class="input-group">
                        <input :type="showCurrent ? 'text' : 'password'" name="current_password" class="form-control" x-model="currentPassword" required>
                        <button class="btn btn-outline-secondary" type="button" @click="showCurrent = !showCurrent" tabindex="-1">
                            <i :class="showCurrent ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                        </button>
                    </div>
                    <?php if (!empty($fieldErrors['current_password'])): ?>
                        <small class="text-danger"><?= htmlspecialchars($fieldErrors['current_password']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">New password</label> 
                    <div class="input-group">
                        <input :type="showNew ? 'text' : 'password'" name="new_password" class="form-control" x-model="newPassword" minlength="8" required>
                        <button class="btn btn-outline-secondary" type="button" @click="showNew = !showNew" tabindex="-1">
                            <i :class="showNew ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                        </button>
                    </div>
                    <small class="text-muted">Minimum 8 characters.</small>
                    <div class="mt-2">
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar" role="progressbar" :class="passwordStrengthClass()" :style="{width: passwordStrength() + '%'}"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small :class="passwordStrengthTextClass()" x-text="passwordStrengthLabel()"></small>
                            <small class="text-muted" x-text="passwordStrength() + '%'" ></small>
                        </div>
                        <small class="text-danger" x-show="passwordStrength() < 40">Choose a stronger password.</small>
                    </div>
                    <?php if (!empty($fieldErrors['new_password'])): ?>
                        <div><small class="text-danger"><?= htmlspecialchars($fieldErrors['new_password']) ?></small></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm new password</label>
                    <div class="input-group">
                        <input :type="showConfirm ? 'text' : 'password'" name="confirm_password" class="form-control" x-model="confirmPassword" required>
                        <button class="btn btn-outline-secondary" type="button" @click="showConfirm = !showConfirm" tabindex="-1">
                            <i :class="showConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                        </button>
                    </div>
                    <small class="text-danger" x-show="confirmPassword && newPassword !== confirmPassword">
                        Passwords do not match.
                    </small>
                    <?php if (!empty($fieldErrors['confirm_password'])): ?>
                        <div><small class="text-danger"><?= htmlspecialchars($fieldErrors['confirm_password']) ?></small></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary" :disabled="loading || passwordStrength() < 40">
                    <span x-show="!loading">Update password</span>
                    <span x-show="loading">Updating...</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Support & data card -->
    <div class="card profile-card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-1">Support &amp; data</h2>
            <p class="text-muted">Get help or manage local app data.</p>

            <div class="d-grid gap-3">
                <a href="https://outlook.office.com/mail/deeplink/compose?to=admin@clockit.app" target="_blank" class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                    <i class="bi bi-envelope me-2"></i> Contact admin
                </a>

                <div class="d-flex justify-content-between align-items-center border rounded p-3">
                    <div>
                        <div class="fw-semibold">App version</div>
                        <small class="text-muted">v1.0.0</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold">Pending events</div>
                        <small class="text-muted" x-text="pendingEvents + ' queued'"></small>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-danger d-flex align-items-center justify-content-center" @click="clearCache()">
                    <i class="bi bi-trash me-2"></i> Clear cache
                </button>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function profileApp() {
    return {
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
        showCurrent: false,
        showNew: false,
        showConfirm: false,
        loading: false,
        pendingEvents: 0,
        flashSuccess: <?= json_encode($flashSuccess) ?>,
        flashError: <?= json_encode($flashError) ?>,
        toastVisible: false,
        toastMessage: '',
        init() {
            if (this.flashSuccess) {
                this.toastMessage = this.flashSuccess;
                this.toastVisible = true;
                setTimeout(() => this.toastVisible = false, 3500);
            } else if (this.flashError) {
                this.toastMessage = this.flashError;
                this.toastVisible = true;
                setTimeout(() => this.toastVisible = false, 3500);
            }
        },
        // Password strength helpers
        passwordStrength() {
            const p = this.newPassword || '';
            let score = 0;
            if (p.length >= 8) score += 30; // length
            if (/[a-z]/.test(p)) score += 15;
            if (/[A-Z]/.test(p)) score += 20;
            if (/[0-9]/.test(p)) score += 20;
            if (/[^A-Za-z0-9]/.test(p)) score += 15;
            return Math.min(100, score);
        },
        passwordStrengthLabel() {
            const s = this.passwordStrength();
            if (s < 40) return 'Weak';
            if (s < 70) return 'Medium';
            return 'Strong';
        },
        passwordStrengthClass() {
            const l = this.passwordStrengthLabel();
            return l === 'Weak' ? 'bg-danger' : (l === 'Medium' ? 'bg-warning' : 'bg-success');
        },
        passwordStrengthTextClass() {
            const l = this.passwordStrengthLabel();
            return l === 'Weak' ? 'text-danger' : (l === 'Medium' ? 'text-warning' : 'text-success');
        },
        async clearCache() {
            if (!confirm('Clear all cached data?')) return;
            try {
                localStorage.clear();
                sessionStorage.clear();
                if (window.caches) {
                    const keys = await caches.keys();
                    await Promise.all(keys.map(k => caches.delete(k)));
                }
                this.pendingEvents = 0;
                alert('Cache cleared.');
            } catch (e) {
                alert('Failed to clear cache: ' + e.message);
            }
        }
    }
}
</script>
<?php if ($flashSuccess): ?>
    <div id="flash-toast" class="alert alert-success position-fixed top-0 end-0 m-3 shadow" role="alert">
        <?= htmlspecialchars($flashSuccess) ?>
    </div>
    <script>setTimeout(()=>{const e=document.getElementById('flash-toast'); if(e) e.style.display='none';}, 3500);</script>
<?php elseif ($flashError): ?>
    <div id="flash-toast" class="alert alert-danger position-fixed top-0 end-0 m-3 shadow" role="alert">
        <?= htmlspecialchars($flashError) ?>
    </div>
    <script>setTimeout(()=>{const e=document.getElementById('flash-toast'); if(e) e.style.display='none';}, 3500);</script>
<?php endif; ?>
</body>
</html>

<?php

declare(strict_types=1);

$profileUser = [
    'name' => $user['name'] ?? 'Sarah Mthembu',
    'email' => $user['email'] ?? 'sarah@clockit.app',
    'employeeId' => $user['employeeId'] ?? 'S-101',
    'role' => ucfirst((string) ($user['role'] ?? 'staff')),
    'photo' => $_SESSION['user_photo'] ?? '',
];

function profile_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';

    foreach ($parts as $part) {
        if ($part !== '') {
            $initials .= strtoupper($part[0]);
        }
    }

    return substr($initials, 0, 2);
}

function profile_redirect(): never
{
    redirect_to('/profile');
}

if (!isset($_SESSION['password_hash'])) {
    $_SESSION['password_hash'] = password_hash('password123', PASSWORD_DEFAULT);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_FILES['profile_photo'])) {
    $file = $_FILES['profile_photo'];
    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $maxSize = 5 * 1024 * 1024;

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $_SESSION['flash_error'] = 'Please choose an image to upload.';
    } elseif (($file['size'] ?? 0) > $maxSize) {
        $_SESSION['flash_error'] = 'File too large. Maximum 5MB allowed.';
    } else {
        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);

        if (!isset($allowedMimeTypes[$mimeType])) {
            $_SESSION['flash_error'] = 'Invalid file type. Please upload JPEG, PNG, GIF, or WebP.';
        } else {
            $uploadDir = dirname(__DIR__, 4) . '/public/uploads/profiles/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'profile_' . bin2hex(random_bytes(8)) . '.' . $allowedMimeTypes[$mimeType];

            if (move_uploaded_file((string) $file['tmp_name'], $uploadDir . $filename)) {
                $_SESSION['user_photo'] = app_url('/uploads/profiles/' . $filename);
                $_SESSION['flash_success'] = 'Profile photo updated successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to upload photo. Please try again.';
            }
        }
    }

    profile_redirect();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $fieldErrors = [];

    if (!password_verify($currentPassword, (string) $_SESSION['password_hash'])) {
        $fieldErrors['current_password'] = 'Incorrect current password.';
    }

    if (strlen($newPassword) < 8) {
        $fieldErrors['new_password'] = 'New password must be at least 8 characters.';
    }

    if ($newPassword !== $confirmPassword) {
        $fieldErrors['confirm_password'] = 'Passwords do not match.';
    }

    if ($fieldErrors !== []) {
        $_SESSION['field_errors'] = $fieldErrors;
        $_SESSION['flash_error'] = 'Please fix the errors and try again.';
        profile_redirect();
    }

    $_SESSION['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
    $_SESSION['flash_success'] = 'Password has been updated.';
    profile_redirect();
}

$flashSuccess = (string) ($_SESSION['flash_success'] ?? '');
$flashError = (string) ($_SESSION['flash_error'] ?? '');
$fieldErrors = $_SESSION['field_errors'] ?? [];
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['field_errors']);

ob_start();
?>

<div class="app-shell">
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>

    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">
            <section class="container-fluid p-4 p-lg-5">
                <div x-data="profileApp()" x-init="init()">
                    <div class="mb-4">
                        <h1 class="staff-history-title mb-1">Profile</h1>
                        <p class="text-muted mb-0">Your account information.</p>
                    </div>

                    <div class="page-card profile-card mb-4">
                        <form method="post" enctype="multipart/form-data" class="d-none" id="photoForm">
                            <input type="file" id="photoInput" name="profile_photo" accept="image/jpeg,image/png,image/gif,image/webp" @change="document.getElementById('photoForm').submit()">
                        </form>

                        <div class="d-flex align-items-center mb-4">
                            <button type="button" class="avatar profile-avatar me-3 position-relative border-0" @click="document.getElementById('photoInput').click()" aria-label="Change profile photo">
                                <?php if ($profileUser['photo'] !== ''): ?>
                                    <img src="<?= e($profileUser['photo']) ?>" alt="<?= e($profileUser['name']) ?>">
                                <?php else: ?>
                                    <span class="avatar-initials"><?= e(profile_initials($profileUser['name'])) ?></span>
                                <?php endif; ?>
                                <span class="profile-camera position-absolute bottom-0 end-0 bg-primary rounded-circle">
                                    <i class="bi bi-camera-fill text-white" aria-hidden="true"></i>
                                </span>
                            </button>
                            <div>
                                <h2 class="h5 mb-0"><?= e($profileUser['name']) ?></h2>
                                <span class="badge text-bg-secondary mt-1"><?= e($profileUser['role']) ?></span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4"><small class="text-muted d-block">Email</small><span><?= e($profileUser['email']) ?></span></div>
                            <div class="col-12 col-md-4"><small class="text-muted d-block">Employee ID</small><span><?= e($profileUser['employeeId']) ?></span></div>
                            <div class="col-12 col-md-4"><small class="text-muted d-block">Role</small><span><?= e($profileUser['role']) ?></span></div>
                        </div>
                    </div>

                    <div class="page-card profile-card mb-4">
                        <h2 class="h5 mb-1">Change password</h2>
                        <p class="text-muted">Update your password securely.</p>

                        <form method="post" class="password-form">
                            <div class="mb-3">
                                <label class="form-label" for="current-password">Current password</label>
                                <div class="input-group">
                                    <input id="current-password" :type="showCurrent ? 'text' : 'password'" name="current_password" class="form-control" x-model="currentPassword" required>
                                    <button class="btn btn-outline-secondary" type="button" @click="showCurrent = !showCurrent" aria-label="Show or hide current password"><i :class="showCurrent ? 'bi bi-eye-slash' : 'bi bi-eye'"></i></button>
                                </div>
                                <?php if (!empty($fieldErrors['current_password'])): ?><small class="text-danger"><?= e($fieldErrors['current_password']) ?></small><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="new-password">New password</label>
                                <div class="input-group">
                                    <input id="new-password" :type="showNew ? 'text' : 'password'" name="new_password" class="form-control" x-model="newPassword" minlength="8" required>
                                    <button class="btn btn-outline-secondary" type="button" @click="showNew = !showNew" aria-label="Show or hide new password"><i :class="showNew ? 'bi bi-eye-slash' : 'bi bi-eye'"></i></button>
                                </div>
                                <small class="text-muted">Minimum 8 characters.</small>
                                <div class="progress mt-2" aria-label="Password strength"><div class="progress-bar" :class="passwordStrengthClass()" :style="{ width: passwordStrength() + '%' }"></div></div>
                                <small :class="passwordStrengthTextClass()" x-text="passwordStrengthLabel()"></small>
                                <?php if (!empty($fieldErrors['new_password'])): ?><div><small class="text-danger"><?= e($fieldErrors['new_password']) ?></small></div><?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="confirm-password">Confirm new password</label>
                                <div class="input-group">
                                    <input id="confirm-password" :type="showConfirm ? 'text' : 'password'" name="confirm_password" class="form-control" x-model="confirmPassword" required>
                                    <button class="btn btn-outline-secondary" type="button" @click="showConfirm = !showConfirm" aria-label="Show or hide password confirmation"><i :class="showConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i></button>
                                </div>
                                <small class="text-danger" x-show="confirmPassword && newPassword !== confirmPassword">Passwords do not match.</small>
                                <?php if (!empty($fieldErrors['confirm_password'])): ?><div><small class="text-danger"><?= e($fieldErrors['confirm_password']) ?></small></div><?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-main" :disabled="passwordStrength() < 40">Update password</button>
                        </form>
                    </div>

                    <div class="page-card profile-card">
                        <h2 class="h5 mb-1">Support &amp; data</h2>
                        <p class="text-muted">Get help or manage local app data.</p>
                        <div class="d-grid gap-3">
                            <a href="https://outlook.office.com/mail/deeplink/compose?to=admin@clockit.app" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary"><i class="bi bi-envelope me-2"></i>Contact admin</a>
                            <div class="d-flex justify-content-between align-items-center border rounded p-3"><span>App version</span><small class="text-muted">v1.0.0</small></div>
                            <button type="button" class="btn btn-outline-danger" @click="clearCache()"><i class="bi bi-trash me-2"></i>Clear cache</button>
                        </div>
                    </div>

                    <?php if ($flashSuccess !== ''): ?><div class="alert alert-success position-fixed top-0 end-0 m-3 shadow" role="status"><?= e($flashSuccess) ?></div><?php endif; ?>
                    <?php if ($flashError !== ''): ?><div class="alert alert-danger position-fixed top-0 end-0 m-3 shadow" role="alert"><?= e($flashError) ?></div><?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
function profileApp() {
    return {
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
        showCurrent: false,
        showNew: false,
        showConfirm: false,
        init() {
            const flash = document.querySelector('.alert.position-fixed');
            if (flash) setTimeout(() => flash.remove(), 3500);
        },
        passwordStrength() {
            const value = this.newPassword || '';
            let score = value.length >= 8 ? 30 : 0;
            if (/[a-z]/.test(value)) score += 15;
            if (/[A-Z]/.test(value)) score += 20;
            if (/[0-9]/.test(value)) score += 20;
            if (/[^A-Za-z0-9]/.test(value)) score += 15;
            return Math.min(100, score);
        },
        passwordStrengthLabel() {
            const score = this.passwordStrength();
            return score < 40 ? 'Weak' : score < 70 ? 'Medium' : 'Strong';
        },
        passwordStrengthClass() {
            return this.passwordStrengthLabel() === 'Weak' ? 'bg-danger' : this.passwordStrengthLabel() === 'Medium' ? 'bg-warning' : 'bg-success';
        },
        passwordStrengthTextClass() {
            return this.passwordStrengthLabel() === 'Weak' ? 'text-danger' : this.passwordStrengthLabel() === 'Medium' ? 'text-warning' : 'text-success';
        },
        async clearCache() {
            if (!window.confirm('Clear all cached data?')) return;
            localStorage.clear();
            sessionStorage.clear();
            if (window.caches) {
                const keys = await caches.keys();
                await Promise.all(keys.map((key) => caches.delete(key)));
            }
            window.alert('Cache cleared.');
        }
    };
}
</script>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';

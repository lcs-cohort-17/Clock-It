<?php

declare(strict_types=1);

$defaultName = (string) ($user['name'] ?? 'Sarah Mthembu');
$defaultNameParts = preg_split('/\s+/', trim($defaultName), 2) ?: [];
$profileDefaults = [
    'firstName' => $defaultNameParts[0] ?? '',
    'surname' => $defaultNameParts[1] ?? '',
    'email' => (string) ($user['email'] ?? 'sarah@clockit.app'),
];
$savedProfile = is_array($_SESSION['profile_details'] ?? null) ? $_SESSION['profile_details'] : [];
$profileDetails = array_merge($profileDefaults, $savedProfile);
$profileUser = [
    'name' => trim($profileDetails['firstName'] . ' ' . $profileDetails['surname']),
    'firstName' => $profileDetails['firstName'],
    'surname' => $profileDetails['surname'],
    'email' => $profileDetails['email'],
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

function profile_remove_uploaded_photo(string $photo): void
{
    $filename = basename((string) parse_url($photo, PHP_URL_PATH));
    $uploadDir = dirname(__DIR__, 4) . '/public/uploads/profiles/';

    if ($filename === '' || !str_starts_with($filename, 'profile_')) {
        return;
    }

    $photoPath = $uploadDir . $filename;

    if (is_file($photoPath)) {
        unlink($photoPath);
    }
}

if (!isset($_SESSION['password_hash'])) {
    $_SESSION['password_hash'] = password_hash('password123', PASSWORD_DEFAULT);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['profile_action'] ?? '') === 'clear_cache') {
    profile_remove_uploaded_photo((string) ($_SESSION['user_photo'] ?? ''));
    unset($_SESSION['profile_details'], $_SESSION['user_photo']);
    login_json_response(['success' => true]);
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
                profile_remove_uploaded_photo((string) ($_SESSION['user_photo'] ?? ''));
                $_SESSION['user_photo'] = app_url('/uploads/profiles/' . $filename);
                $_SESSION['flash_success'] = 'Profile photo updated successfully.';
            } else {
                $_SESSION['flash_error'] = 'Failed to upload photo. Please try again.';
            }
        }
    }

    profile_redirect();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['profile_action'] ?? '') === 'save_details') {
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $surname = trim((string) ($_POST['surname'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $fieldErrors = [];

    if ($firstName === '') {
        $fieldErrors['first_name'] = 'First name is required.';
    }

    if ($surname === '') {
        $fieldErrors['surname'] = 'Surname is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = 'Enter a valid email address.';
    }

    if ($fieldErrors !== []) {
        $_SESSION['field_errors'] = $fieldErrors;
        $_SESSION['flash_error'] = 'Please fix the errors and try again.';
        profile_redirect();
    }

    $_SESSION['profile_details'] = compact('firstName', 'surname', 'email');
    $_SESSION['flash_success'] = 'Profile details updated successfully.';
    profile_redirect();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['profile_action'] ?? '') === 'change_password') {
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
            <section class="container-fluid p-4 p-lg-5 profile-page">
                <div class="profile-page-inner" x-data="profileApp()">
                    <div class="profile-page-header mb-4">
                        <h1 class="staff-history-title mb-1">Profile</h1>
                        <p class="text-muted mb-0">Manage your account, security, and local app data.</p>
                    </div>

                    <div class="page-card profile-card mb-4">
                        <form method="post" enctype="multipart/form-data" class="d-none" id="photoForm">
                            <input type="file" id="photoInput" name="profile_photo" accept="image/jpeg,image/png,image/gif,image/webp" @change="openCropper($event)">
                        </form>

                        <div class="profile-identity d-flex align-items-center gap-3 mb-4">
                            <button type="button" class="profile-avatar position-relative border-0" @click="document.getElementById('photoInput').click()" aria-label="Change profile photo">
                                <?php if ($profileUser['photo'] !== ''): ?>
                                    <img src="<?= e($profileUser['photo']) ?>" alt="<?= e($profileUser['name']) ?>">
                                <?php else: ?>
                                    <span class="avatar-initials"><?= e(profile_initials($profileUser['name'])) ?></span>
                                <?php endif; ?>
                                <span class="profile-camera position-absolute bottom-0 end-0 bg-primary rounded-circle">
                                    <i class="bi bi-camera-fill text-white" aria-hidden="true"></i>
                                </span>
                            </button>
                            <div class="min-width-0">
                                <h2 class="profile-name mb-1"><?= e($profileUser['name']) ?></h2>
                                <span class="profile-role-badge"><?= e($profileUser['role']) ?></span>
                                <button type="button" class="profile-photo-link d-block mt-2" @click="document.getElementById('photoInput').click()">View or change photo</button>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                            <h3 class="h6 fw-bold mb-0">Personal details</h3>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="editingDetails = !editingDetails">
                                <i class="bi bi-pencil me-1" aria-hidden="true"></i>
                                <span x-text="editingDetails ? 'Cancel' : 'Edit'"></span>
                            </button>
                        </div>

                        <div class="profile-details-grid" x-show="!editingDetails">
                            <div class="profile-detail"><small>First name</small><span><?= e($profileUser['firstName']) ?></span></div>
                            <div class="profile-detail"><small>Surname</small><span><?= e($profileUser['surname']) ?></span></div>
                            <div class="profile-detail"><small>Email</small><span><?= e($profileUser['email']) ?></span></div>
                            <div class="profile-detail"><small>Employee ID</small><span><?= e($profileUser['employeeId']) ?></span></div>
                            <div class="profile-detail"><small>Role</small><span><?= e($profileUser['role']) ?></span></div>
                        </div>

                        <form method="post" class="profile-edit-form" x-show="editingDetails" x-cloak>
                            <input type="hidden" name="profile_action" value="save_details">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="profile-first-name">First name</label>
                                    <input id="profile-first-name" name="first_name" class="form-control" value="<?= e($profileUser['firstName']) ?>" required>
                                    <?php if (!empty($fieldErrors['first_name'])): ?><small class="text-danger"><?= e($fieldErrors['first_name']) ?></small><?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="profile-surname">Surname</label>
                                    <input id="profile-surname" name="surname" class="form-control" value="<?= e($profileUser['surname']) ?>" required>
                                    <?php if (!empty($fieldErrors['surname'])): ?><small class="text-danger"><?= e($fieldErrors['surname']) ?></small><?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="profile-email">Email</label>
                                    <input id="profile-email" type="email" name="email" class="form-control" value="<?= e($profileUser['email']) ?>" required>
                                    <?php if (!empty($fieldErrors['email'])): ?><small class="text-danger"><?= e($fieldErrors['email']) ?></small><?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Employee ID</label>
                                    <input class="form-control" value="<?= e($profileUser['employeeId']) ?>" disabled>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Role</label>
                                    <input class="form-control" value="<?= e($profileUser['role']) ?>" disabled>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-main mt-3">Save details</button>
                        </form>
                    </div>

                    <div class="page-card profile-card mb-4">
                        <div class="profile-section-heading">
                            <i class="bi bi-shield-lock" aria-hidden="true"></i>
                            <div>
                                <h2 class="h5 mb-1">Change password</h2>
                                <p class="text-muted mb-0">Update your password securely.</p>
                            </div>
                        </div>

                        <form method="post" class="password-form mt-4">
                            <input type="hidden" name="profile_action" value="change_password">
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
                        <div class="profile-section-heading">
                            <i class="bi bi-life-preserver" aria-hidden="true"></i>
                            <div>
                                <h2 class="h5 mb-1">Support &amp; data</h2>
                                <p class="text-muted mb-0">Get help or manage local app data.</p>
                            </div>
                        </div>
                        <div class="profile-support-grid mt-4">
                            <a href="https://outlook.office.com/mail/deeplink/compose?to=admin@clockit.app" target="_blank" rel="noopener noreferrer" class="profile-support-action"><i class="bi bi-envelope"></i><span>Contact admin</span></a>
                            <div class="profile-support-action"><i class="bi bi-info-circle"></i><span>App version</span><small>v1.0.0</small></div>
                            <button type="button" class="profile-support-action profile-support-danger" @click="clearCache()"><i class="bi bi-trash"></i><span>Clear cache</span></button>
                        </div>
                    </div>

                    <?php if ($flashSuccess !== ''): ?><div class="alert alert-success position-fixed top-0 end-0 m-3 shadow" role="status"><?= e($flashSuccess) ?></div><?php endif; ?>
                    <?php if ($flashError !== ''): ?><div class="alert alert-danger position-fixed top-0 end-0 m-3 shadow" role="alert"><?= e($flashError) ?></div><?php endif; ?>

                    <div class="profile-crop-backdrop" x-show="cropOpen" x-cloak @click.self="closeCropper()" @keydown.escape.window="closeCropper()">
                        <section class="profile-crop-modal" role="dialog" aria-modal="true" aria-labelledby="crop-title">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div>
                                    <h2 class="h5 mb-1" id="crop-title">Crop profile photo</h2>
                                    <p class="text-muted small mb-0">Preview and adjust the square crop before saving.</p>
                                </div>
                                <button type="button" class="btn-close" @click="closeCropper()" aria-label="Close"></button>
                            </div>
                            <canvas class="profile-crop-canvas" x-ref="cropCanvas" width="360" height="360"></canvas>
                            <label class="form-label mt-3" for="crop-zoom">Zoom</label>
                            <input id="crop-zoom" class="form-range" type="range" min="1" max="3" step="0.05" x-model.number="cropZoom" @input="drawCropPreview()">
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="btn btn-outline-secondary" @click="closeCropper()">Cancel</button>
                                <button type="button" class="btn btn-main" @click="saveCroppedPhoto()" :disabled="cropSaving">
                                    <span x-text="cropSaving ? 'Saving...' : 'Save photo'"></span>
                                </button>
                            </div>
                        </section>
                    </div>
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
        editingDetails: <?= json_encode(
            !empty($fieldErrors['first_name'])
            || !empty($fieldErrors['surname'])
            || !empty($fieldErrors['email'])
        ) ?>,
        cropOpen: false,
        cropSaving: false,
        cropZoom: 1,
        cropImage: null,
        cropObjectUrl: '',
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
        openCropper(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type)) {
                window.alert('Please choose a JPEG, PNG, GIF, or WebP image.');
                event.target.value = '';
                return;
            }

            this.closeCropper();
            this.cropObjectUrl = URL.createObjectURL(file);
            this.cropImage = new Image();
            this.cropImage.onload = () => {
                this.cropZoom = 1;
                this.cropOpen = true;
                this.$nextTick(() => this.drawCropPreview());
            };
            this.cropImage.src = this.cropObjectUrl;
        },
        drawCropPreview() {
            const canvas = this.$refs.cropCanvas;
            if (!canvas || !this.cropImage) return;

            const context = canvas.getContext('2d');
            const image = this.cropImage;
            const cropSize = Math.min(image.naturalWidth, image.naturalHeight) / this.cropZoom;
            const sourceX = (image.naturalWidth - cropSize) / 2;
            const sourceY = (image.naturalHeight - cropSize) / 2;
            context.clearRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, sourceX, sourceY, cropSize, cropSize, 0, 0, canvas.width, canvas.height);
        },
        closeCropper() {
            this.cropOpen = false;
            if (this.cropObjectUrl) URL.revokeObjectURL(this.cropObjectUrl);
            this.cropObjectUrl = '';
            this.cropImage = null;
            const input = document.getElementById('photoInput');
            if (input) input.value = '';
        },
        saveCroppedPhoto() {
            const canvas = this.$refs.cropCanvas;
            if (!canvas || this.cropSaving) return;

            this.cropSaving = true;
            canvas.toBlob(async (blob) => {
                if (!blob) {
                    this.cropSaving = false;
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('profile_photo', blob, 'profile-photo.jpg');
                    const response = await fetch(window.location.href, { method: 'POST', body: formData });
                    if (!response.ok) throw new Error('Photo upload failed.');
                    window.location.reload();
                } catch (error) {
                    this.cropSaving = false;
                    window.alert('Unable to save the photo. Please try again.');
                }
            }, 'image/jpeg', 0.9);
        },
        async clearCache() {
            if (!window.confirm('Clear all cached data?')) return;
            localStorage.clear();
            sessionStorage.clear();
            if (window.caches) {
                const keys = await caches.keys();
                await Promise.all(keys.map((key) => caches.delete(key)));
            }
            const formData = new FormData();
            formData.append('profile_action', 'clear_cache');
            const response = await fetch(window.location.href, { method: 'POST', body: formData });
            if (!response.ok) {
                window.alert('Unable to clear the saved profile data. Please try again.');
                return;
            }
            window.location.reload();
        }
    };
}
</script>

<?php
$content = ob_get_clean();

require __DIR__ . '/../layouts/app.php';

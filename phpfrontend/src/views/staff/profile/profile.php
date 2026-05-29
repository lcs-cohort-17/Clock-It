<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionSavePath = session_save_path();
    if ($sessionSavePath !== '' && !is_writable($sessionSavePath)) {
        session_save_path(sys_get_temp_dir());
    }

    session_start();
}

if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$profileUser = $user ?? $_SESSION['user'] ?? [];

$currentUser = [
    'fullName' => $profileUser['fullName'] ?? $profileUser['name'] ?? 'Demo Staff',
    'email' => $profileUser['email'] ?? 'staff@clockit.app',
    'employeeId' => $profileUser['employeeId'] ?? 'EMP-001',
    'role' => $profileUser['role'] ?? 'staff',
    'avatarUrl' => $profileUser['avatarUrl'] ?? null,
];

$csrfToken = $_SESSION['csrf_token'];
?>

<?php ob_start(); ?>

<div class="staff-shell">
    <?php $activeNav = 'profile'; require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="staff-main">
        <header class="staff-topbar">
            <div>
                <p class="eyebrow">Staff Profile</p>
                <h1>Profile</h1>
            </div>
            <a class="staff-topbar__profile" href="/staff-dashboard/">Dashboard</a>
        </header>

<div class="profile-wrapper">
    <div class="profile-toast-container" id="toastContainer"></div>

    <!-- Profile Information Card -->
    <div class="profile-card">
        <div class="profile-card__header">
            <h2>Profile Information</h2>
            <p>Your personal information and profile photo.</p>
        </div>
        
        <div class="profile-avatar-section">
            <div class="profile-avatar-container">
                <div class="profile-avatar-circle" id="avatarPreview">
                    <img id="avatarImg" src="" style="display: none;" alt="User Avatar">
                    <span id="avatarInitials" class="profile-avatar-initials"></span>
                </div>
                <label id="avatarUploadLabel" for="avatarUpload" class="profile-avatar-upload-label hidden">
                    Upload image
                    <input type="file" id="avatarUpload" accept="image/jpeg,image/png,image/webp" style="display: none;">
                </label>
            </div>
            <div class="profile-user-info">
                <h3 class="profile-user-name" id="displayName"></h3>
                <p class="profile-user-email" id="displayEmail"></p>
            </div>
        </div>
        
        <form id="profileForm" novalidate>
            <input type="hidden" id="csrfToken" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <div class="profile-form-grid">
                <div class="profile-form-field">
                    <label class="profile-form-label" for="fullNameInput">Full Name</label>
                    <div id="fullNameView" class="profile-form-value"></div>
                    <input type="text" id="fullNameInput" name="fullName" class="profile-form-input hidden" maxlength="100" required>
                </div>
                <div class="profile-form-field">
                    <label class="profile-form-label" for="emailInput">Email</label>
                    <div id="emailView" class="profile-form-value"></div>
                    <input type="email" id="emailInput" name="email" class="profile-form-input hidden" maxlength="150" required>
                </div>
                <div class="profile-form-field">
                    <span class="profile-form-label">Employee ID</span>
                    <div class="profile-form-value" id="employeeIdDisplay"></div>
                </div>
                <div class="profile-form-field">
                    <span class="profile-form-label">Role</span>
                    <div class="profile-form-value" id="roleDisplay"></div>
                </div>
            </div>

            <div class="profile-actions">
                <div id="viewButtons">
                    <button id="editBtn" type="button" class="profile-btn-secondary">Edit profile</button>
                </div>
                <div id="editButtons" class="hidden">
                    <button type="button" id="cancelBtn" class="profile-btn-secondary">✕ Cancel</button>
                    <button type="submit" id="profileSubmitBtn" class="profile-btn-primary">Save</button>
                </div>
            </div>
            <div id="profileMessage" role="alert"></div>
        </form>
    </div>

    <!-- Change Password Card -->
    <div class="profile-card">
        <div class="profile-card__header">
            <h2>Change password</h2>
            <p>Update your account security credentials.</p>
        </div>
        <form id="passwordForm" novalidate>
            <div class="profile-form-field">
                <label class="profile-form-label" for="currentPassword">Current password</label>
                <div class="profile-password-input-wrapper">
                    <input type="password" id="currentPassword" class="profile-password-input" placeholder="Enter current password" required>
                    <button type="button" class="profile-password-toggle" data-target="currentPassword" aria-label="Show password"></button>
                </div>
            </div>
            <div class="profile-form-field mt-6">
                <label class="profile-form-label" for="newPassword">New password</label>
                <div class="profile-password-input-wrapper">
                    <input type="password" id="newPassword" class="profile-password-input" placeholder="Enter new password" required>
                    <button type="button" class="profile-password-toggle" data-target="newPassword" aria-label="Show password"></button>
                </div>
                <div id="passwordStrength" class="hidden profile-password-strength"></div>
            </div>
            <div class="profile-form-field mt-6">
                <label class="profile-form-label" for="confirmPassword">Confirm new password</label>
                <div class="profile-password-input-wrapper">
                    <input type="password" id="confirmPassword" class="profile-password-input" placeholder="Confirm new password" required>
                    <button type="button" class="profile-password-toggle" data-target="confirmPassword" aria-label="Show password"></button>
                </div>
            </div>
            <div id="passwordMessage" role="alert"></div>
            <button type="submit" id="passwordSubmitBtn" class="profile-btn-primary" style="margin-top: 1rem;">Update password</button>
        </form>
    </div>

    <!-- Support & Data Card -->
    <div class="profile-card">
        <div class="profile-card__header">
            <h2>Support & Data</h2>
            <p>Manage support requests and application data.</p>
        </div>
        <div id="clearSuccessMessage" class="profile-alert-success hidden" role="alert"></div>
        <button id="contactAdminBtn" class="profile-btn-primary" style="margin-top: 1rem;">Contact admin</button>
        
        <div class="mt-6">
            <p style="color: #334155;">App version <strong><?php echo APP_VERSION; ?></strong></p>
        </div>
        
        <div class="mt-6">
            <p style="color: #334155;"><strong>What "Clear Cache" does:</strong></p>
            <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                <li>✓ Resets profile image to default</li>
                <li>✓ Clears app preferences</li>
                <li>✓ Removes temporary data</li>
            </ul>
        </div>
        
        <div class="danger-zone">
            <p class="danger-label">Danger Zone</p>
            <button id="clearCacheBtn" class="profile-btn-danger">Clear local cache</button>
            <p class="helper-text">Resets profile image and app preferences.</p>
        </div>
    </div>

    <!-- Crop Modal -->
    <div id="cropModal" class="profile-crop-modal-overlay hidden" role="dialog" aria-modal="true" aria-labelledby="cropTitle" aria-hidden="true">
        <div class="profile-crop-modal">
            <div class="profile-crop-header">
                <h3 id="cropTitle">Crop profile image</h3>
            </div>
            <div class="profile-crop-body">
                <div id="cropArea" class="profile-crop-preview-wrapper" tabindex="0">
                    <img id="cropImage" class="profile-crop-preview-image" alt="Crop workspace">
                </div>
                <div class="profile-crop-controls">
                    <label for="zoomSlider">Zoom image</label>
                    <input type="range" id="zoomSlider" class="profile-crop-slider" min="1" max="4" step="0.01" value="1">
                </div>
            </div>
            <div class="profile-crop-actions">
                <button type="button" id="cancelCropBtn" class="profile-btn-secondary">Cancel</button>
                <button type="button" id="saveCropBtn" class="profile-btn-primary">Save image</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Clear Cache Modal -->
<div id="confirmModal" class="profile-modal-overlay hidden" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle" aria-hidden="true">
    <div class="profile-modal-content">
        <div class="profile-modal-header">
            <h3 class="profile-modal-title" id="confirmModalTitle">Clear local cache?</h3>
            <button id="closeModalBtn" class="profile-close-btn">✕</button>
        </div>
        <p>This will reset your profile image and app preferences.</p>
        <div class="warning-box">
            <p>Your profile image will be reset to default. You can upload a new image anytime.</p>
        </div>
        <div class="profile-modal-buttons">
            <button id="confirmClearBtn" class="profile-modal-btn-primary">Yes, clear cache</button>
            <button id="cancelClearBtn" class="profile-modal-btn-secondary">Cancel</button>
        </div>
    </div>
</div>

    </main>
</div>

<script>
    window.ProfileBootstrap = {
        fullName: <?php echo json_encode($currentUser['fullName'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>,
        email: <?php echo json_encode($currentUser['email'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>,
        employeeId: <?php echo json_encode($currentUser['employeeId'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>,
        role: <?php echo json_encode($currentUser['role'], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>,
        avatarUrl: <?php echo json_encode($currentUser['avatarUrl'] ?? null, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>
    };
</script>
<script src="/assets/js/profile.js"></script>

<?php
$footerPath = __DIR__ . '/../../../includes/footer.php';
if (is_file($footerPath)) {
    require_once $footerPath;
}

$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>

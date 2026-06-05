<?php
declare(strict_types=1);
ob_start();
?>
<div class="app-shell" id="staffProfileApp">
    <?php require __DIR__ . '/../partials/staff_sidebar.php'; ?>
    <div class="main-panel">
        <?php require __DIR__ . '/../partials/header.php'; ?>
        <main class="content">
            <section class="container-fluid p-4 p-lg-5 profile-page">
                <div class="profile-page-inner">
                    <div class="profile-page-header mb-4">
                        <h1 class="staff-history-title staff-page-title mb-1">Profile</h1>
                        <p class="staff-page-subtitle mb-0">Manage your account, security, and local app data.</p>
                    </div>

                    <!-- Messages -->
                    <div x-show="successMessage" x-transition class="alert alert-success mb-3" x-text="successMessage"></div>
                    <div x-show="errorMessage" x-transition class="alert alert-danger mb-3" x-text="errorMessage"></div>

                    <!-- Personal Details Card -->
                    <div class="page-card profile-card mb-4">
                        <div class="profile-identity d-flex align-items-center gap-3 mb-4">
                            <div class="profile-avatar" x-text="initials"></div>
                            <div class="min-width-0">
                                <h2 class="profile-name mb-1" x-text="fullName"></h2>
                                <span class="profile-role-badge" x-text="role"></span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                            <h3 class="staff-card-title mb-0">Personal details</h3>
                            <button type="button" class="btn btn-sm btn-outline-primary" @click="editing = !editing">
                                <i class="bi bi-pencil me-1"></i>
                                <span x-text="editing ? 'Cancel' : 'Edit'"></span>
                            </button>
                        </div>

                        <!-- View mode -->
                        <div class="profile-details-grid" x-show="!editing">
                            <div class="profile-detail"><small>First name</small><span x-text="profile.first_name"></span></div>
                            <div class="profile-detail"><small>Surname</small><span x-text="profile.last_name"></span></div>
                            <div class="profile-detail"><small>Email</small><span x-text="profile.email"></span></div>
                            <div class="profile-detail"><small>Employee ID</small><span x-text="profile.employee_id"></span></div>
                            <div class="profile-detail"><small>Role</small><span x-text="role"></span></div>
                        </div>

                        <!-- Edit form -->
                        <form class="profile-edit-form" x-show="editing" @submit.prevent="saveProfile">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">First name</label>
                                    <input class="form-control" x-model="profile.first_name" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Surname</label>
                                    <input class="form-control" x-model="profile.last_name" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" x-model="profile.email" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Employee ID</label>
                                    <input class="form-control" x-model="profile.employee_id" disabled>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Role</label>
                                    <input class="form-control" x-model="role" disabled>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-main mt-3" :disabled="saving">
                                <span x-show="saving" class="spinner-border spinner-border-sm me-1"></span>
                                Save details
                            </button>
                        </form>
                    </div>

                    <!-- Change Password Card -->
                    <div class="page-card profile-card mb-4">
                        <div class="profile-section-heading">
                            <i class="bi bi-shield-lock" aria-hidden="true"></i>
                            <div>
                                <h2 class="staff-section-title mb-1">Change password</h2>
                                <p class="staff-section-subtitle mb-0">Update your password securely.</p>
                            </div>
                        </div>

                        <form class="password-form mt-4" @submit.prevent="changePassword">
                            <div class="mb-3">
                                <label class="form-label" for="current-password">Current password</label>
                                <div class="input-group">
                                    <input id="current-password" :type="showCurrent ? 'text' : 'password'" class="form-control" x-model="currentPassword" required>
                                    <button class="btn btn-outline-secondary" type="button" @click="showCurrent = !showCurrent" aria-label="Toggle current password visibility">
                                        <i :class="showCurrent ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="new-password">New password</label>
                                <div class="input-group">
                                    <input id="new-password" :type="showNew ? 'text' : 'password'" class="form-control" x-model="newPassword" minlength="8" required>
                                    <button class="btn btn-outline-secondary" type="button" @click="showNew = !showNew" aria-label="Toggle new password visibility">
                                        <i :class="showNew ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                    </button>
                                </div>
                                <small class="staff-section-subtitle">Minimum 8 characters.</small>
                                <div class="progress mt-2" aria-label="Password strength">
                                    <div class="progress-bar" :class="passwordStrengthClass()" :style="{ width: passwordStrength() + '%' }"></div>
                                </div>
                                <small :class="passwordStrengthTextClass()" x-text="passwordStrengthLabel()"></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="confirm-password">Confirm new password</label>
                                <div class="input-group">
                                    <input id="confirm-password" :type="showConfirm ? 'text' : 'password'" class="form-control" x-model="confirmPassword" required>
                                    <button class="btn btn-outline-secondary" type="button" @click="showConfirm = !showConfirm" aria-label="Toggle password confirmation visibility">
                                        <i :class="showConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                    </button>
                                </div>
                                <small class="text-danger" x-show="confirmPassword && newPassword !== confirmPassword">Passwords do not match.</small>
                            </div>

                            <button type="submit" class="btn btn-main" :disabled="passwordStrength() < 40 || newPassword !== confirmPassword">Update password</button>
                        </form>
                    </div>

                    <!-- Support & Data Card (kept simple) -->
                    <div class="page-card profile-card">
                        <div class="profile-section-heading">
                            <i class="bi bi-life-preserver" aria-hidden="true"></i>
                            <div>
                                <h2 class="staff-section-title mb-1">Support &amp; data</h2>
                                <p class="staff-section-subtitle mb-0">Get help or manage local app data.</p>
                            </div>
                        </div>
                        <div class="profile-support-grid mt-4">
                            <a href="https://outlook.office.com/mail/deeplink/compose?to=admin@clockit.app" target="_blank" rel="noopener noreferrer" class="profile-support-action"><i class="bi bi-envelope"></i><span>Contact admin</span></a>
                            <div class="profile-support-action"><i class="bi bi-info-circle"></i><span>App version</span><small>v1.0.0</small></div>
                            <button type="button" class="profile-support-action profile-support-danger" @click="clearCache()"><i class="bi bi-trash"></i><span>Clear cache</span></button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Alpine === 'undefined') return;

    Alpine.data('staffProfile', () => ({
        // Personal details
        profile: {
            first_name: '',
            last_name: '',
            email: '',
            employee_id: ''
        },
        role: '',
        fullName: '',
        initials: '',
        editing: false,
        saving: false,

        // Password change
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
        showCurrent: false,
        showNew: false,
        showConfirm: false,

        // Messages
        successMessage: '',
        errorMessage: '',

        async init() {
            const store = Alpine.store('app');
            await store.init();
            if (!store.isAuthenticated) {
                window.location.href = '/login';
                return;
            }
            const user = store.user;
            if (!user) {
                this.errorMessage = 'User data not available.';
                return;
            }
            this.profile.first_name = user.first_name || '';
            this.profile.last_name = user.last_name || '';
            this.profile.email = user.email || '';
            this.profile.employee_id = user.employee_id || '';
            this.role = user.role || 'staff';
            this.updateDisplay();
        },

        // ---- Personal details helpers ----
        updateDisplay() {
            const first = this.profile.first_name || '';
            const last = this.profile.last_name || '';
            this.fullName = (first + ' ' + last).trim();
            this.initials = first.charAt(0).toUpperCase() + (last.charAt(0) || '').toUpperCase();
        },

        async saveProfile() {
            this.saving = true;
            this.errorMessage = '';
            try {
                await api.patch('/api/user/profile', {
                    first_name: this.profile.first_name,
                    last_name: this.profile.last_name,
                    email: this.profile.email
                });
                // Update the store so the sidebar reflects changes
                const store = Alpine.store('app');
                if (store.user) {
                    store.user.first_name = this.profile.first_name;
                    store.user.last_name = this.profile.last_name;
                    store.user.email = this.profile.email;
                }
                this.successMessage = 'Profile updated successfully!';
                this.editing = false;
                this.updateDisplay();
                setTimeout(() => this.successMessage = '', 3000);
            } catch (error) {
                this.errorMessage = error.message || 'Failed to update profile.';
            } finally {
                this.saving = false;
            }
        },

        // ---- Password helpers ----
        passwordStrength() {
            const val = this.newPassword || '';
            let score = val.length >= 8 ? 30 : 0;
            if (/[a-z]/.test(val)) score += 15;
            if (/[A-Z]/.test(val)) score += 20;
            if (/[0-9]/.test(val)) score += 20;
            if (/[^A-Za-z0-9]/.test(val)) score += 15;
            return Math.min(100, score);
        },
        passwordStrengthLabel() {
            const s = this.passwordStrength();
            return s < 40 ? 'Weak' : s < 70 ? 'Medium' : 'Strong';
        },
        passwordStrengthClass() {
            const label = this.passwordStrengthLabel();
            return label === 'Weak' ? 'bg-danger' : label === 'Medium' ? 'bg-warning' : 'bg-success';
        },
        passwordStrengthTextClass() {
            const label = this.passwordStrengthLabel();
            return label === 'Weak' ? 'text-danger' : label === 'Medium' ? 'text-warning' : 'text-success';
        },

        async changePassword() {
            if (this.newPassword !== this.confirmPassword) {
                this.errorMessage = 'Passwords do not match.';
                return;
            }
            if (this.passwordStrength() < 40) {
                this.errorMessage = 'Password is too weak.';
                return;
            }
            this.saving = true;
            this.errorMessage = '';
            try {
                await api.patch('/api/user/update-password', {
                    old_password: this.currentPassword,
                    new_password: this.newPassword,
                    email: this.profile.email          // backend needs the user's email
                });
                this.successMessage = 'Password updated successfully!';
                this.currentPassword = '';
                this.newPassword = '';
                this.confirmPassword = '';
                setTimeout(() => this.successMessage = '', 3000);
            } catch (error) {
                this.errorMessage = error.message || 'Failed to update password.';
            } finally {
                this.saving = false;
            }
        },

        async clearCache() {
            if (!confirm('Clear all cached data?')) return;
            localStorage.clear();
            sessionStorage.clear();
            if (window.caches) {
                const keys = await caches.keys();
                await Promise.all(keys.map(key => caches.delete(key)));
            }
            try {
                await api.post('/api/user/cache/clear', {});
                this.successMessage = 'Cache cleared.';
                setTimeout(() => this.successMessage = '', 2000);
            } catch (e) {
                // even if the API call fails, the local cache is cleared
                this.successMessage = 'Local cache cleared.';
                setTimeout(() => this.successMessage = '', 2000);
            }
        }
    }));

    const el = document.getElementById('staffProfileApp');
    if (el) {
        el.setAttribute('x-data', 'staffProfile()');
        el.setAttribute('x-init', 'init()');
        Alpine.initTree(el);
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
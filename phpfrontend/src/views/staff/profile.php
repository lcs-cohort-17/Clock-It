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
                        <p class="staff-page-subtitle mb-0">Manage your account details.</p>
                    </div>

                    <!-- Messages -->
                    <div x-show="successMessage" x-transition class="alert alert-success mb-3" x-text="successMessage"></div>
                    <div x-show="errorMessage" x-transition class="alert alert-danger mb-3" x-text="errorMessage"></div>

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
                </div>
            </section>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Alpine === 'undefined') return;

    Alpine.data('staffProfile', () => ({
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
                const response = await api.patch('/api/user/profile', {
                    first_name: this.profile.first_name,
                    last_name: this.profile.last_name,
                    email: this.profile.email
                });
                // Update the store's user object so sidebar also updates
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
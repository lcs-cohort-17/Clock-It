<?php
/**
 * Staff Profile Page
 * User profile information and settings
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . route_url('/login'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Clock-It</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    profile: {
        name: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>',
        email: '<?php echo htmlspecialchars($_SESSION['user_email'] ?? 'user@example.com'); ?>',
        employeeId: '<?php echo htmlspecialchars($_SESSION['employee_id'] ?? 'EMP001'); ?>',
        department: 'Engineering',
        phone: '+1 (555) 123-4567',
        joinDate: '2023-01-15'
    },
    editMode: false,
    passwordForm: {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
    },
    photoPreview: '',
    passwordVisible: {
        currentPassword: false,
        newPassword: false,
        confirmPassword: false
    },
    get passwordStrength() {
        const password = this.passwordForm.newPassword;
        let score = 0;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        if (!password) return { label: 'Enter a new password', className: 'text-muted', percent: 0 };
        if (score <= 2) return { label: 'Weak password', className: 'text-danger', percent: 33 };
        if (score <= 4) return { label: 'Good password', className: 'text-warning', percent: 66 };
        return { label: 'Strong password', className: 'text-success', percent: 100 };
    },
    togglePassword(field) {
        this.passwordVisible[field] = !this.passwordVisible[field];
    },
    uploadPhoto(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('Please choose an image file.');
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            this.photoPreview = reader.result;
        };
        reader.readAsDataURL(file);
    },
    saveProfile() {
        alert('Profile saved successfully!');
        this.editMode = false;
    },
    changePassword() {
        if (this.passwordForm.newPassword !== this.passwordForm.confirmPassword) {
            alert('Passwords do not match!');
            return;
        }
        if (this.passwordStrength.percent < 66) {
            alert('Please choose a stronger password before saving.');
            return;
        }
        alert('Password changed successfully!');
        this.passwordForm = { currentPassword: '', newPassword: '', confirmPassword: '' };
    }
}" @init="window.themeManager.initTheme()">
    
    <div style="display: flex; min-height: 100vh;">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Page Content -->
            <main class="dashboard-section" style="padding: 2rem;">
                <div class="container-fluid">
                    <h2 class="mb-4">My Profile</h2>

                    <div class="row">
                        <!-- Profile Information -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Profile Information</h5>
                                    <button class="btn btn-sm btn-outline-primary" @click="editMode = !editMode" type="button">
                                        <span x-show="!editMode"><i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Edit</span>
                                        <span x-show="editMode">Cancel</span>
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" x-model="profile.name" :disabled="!editMode">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" x-model="profile.email" :disabled="!editMode">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Employee ID</label>
                                            <input type="text" class="form-control" x-model="profile.employeeId" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Department</label>
                                            <input type="text" class="form-control" x-model="profile.department" :disabled="!editMode">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="tel" class="form-control" x-model="profile.phone" :disabled="!editMode">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Join Date</label>
                                            <input type="date" class="form-control" x-model="profile.joinDate" disabled>
                                        </div>
                                    </div>
                                    <button 
                                        class="btn btn-primary" 
                                        @click="saveProfile()" 
                                        x-show="editMode"
                                        type="button"
                                    >
                                        <i class="bi bi-save me-1" aria-hidden="true"></i>Save Changes
                                    </button>
                                </div>
                            </div>

                            <!-- Change Password -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Change Password</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" x-model="passwordForm.currentPassword">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" x-model="passwordForm.newPassword">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" x-model="passwordForm.confirmPassword">
                                    </div>
                                    <button class="btn btn-primary" @click="changePassword()" type="button">
                                        <i class="bi bi-shield-lock me-1" aria-hidden="true"></i>Change Password
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Info -->
                        <div class="col-lg-4">
                            <!-- Avatar -->
                            <div class="card border-0 shadow-sm mb-4 text-center">
                                <div class="card-body">
                                    <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-navy), var(--secondary-blue)); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                                        <i class="bi bi-person" aria-hidden="true"></i>
                                    </div>
                                    <p class="text-muted small">Profile Picture</p>
                                    <button class="btn btn-sm btn-outline-secondary" type="button">
                                        <i class="bi bi-upload me-1" aria-hidden="true"></i>Upload Photo
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Account Details</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        <small class="text-muted">Status</small>
                                        <div><span class="badge bg-success">Active</span></div>
                                    </div>
                                    <div class="list-group-item">
                                        <small class="text-muted">Role</small>
                                        <div>Staff Member</div>
                                    </div>
                                    <div class="list-group-item">
                                        <small class="text-muted">Last Login</small>
                                        <div><?php echo date('M d, Y h:i A'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

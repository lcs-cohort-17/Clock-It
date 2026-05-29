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
    saveProfile() {
        alert('Profile saved successfully!');
        this.editMode = false;
    },
    changePassword() {
        if (this.passwordForm.newPassword !== this.passwordForm.confirmPassword) {
            alert('Passwords do not match!');
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
                                        <span x-show="!editMode">✏️ Edit</span>
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
                                        💾 Save Changes
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
                                        🔐 Change Password
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
                                        👤
                                    </div>
                                    <p class="text-muted small">Profile Picture</p>
                                    <button class="btn btn-sm btn-outline-secondary" type="button">
                                        📤 Upload Photo
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

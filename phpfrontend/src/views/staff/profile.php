<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// If not a staff member, redirect them to login or their admin panel
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    // Elegant fallback: If an admin accidentally accesses a staff link, send them back to admin base
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: ' . route_url('/admin-dashboard'));
        exit;
    }
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
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Application Style Layer -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    
    <!-- Alpine.js & Utility Core -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>

    <style>
        /* Design System Token Mapping */
        :root {
            --deep-navy: #093C5D;
            --mid-blue: #3B7597;
            --olive-green: #9CB07A;
            --light-gray: #F5F5F5;
        }

        body {
            background-color: var(--light-gray);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--deep-navy);
        }

        /* Workspace Content Offsets for Fixed Sidebar */
        .main-workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: 280px; /* Layout sync with fixed sidebar frame */
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .main-workspace {
                margin-left: 0;
            }
        }

        /* Container Cards */
        .custom-card {
            background-color: #FFFFFF;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(9, 60, 93, 0.04);
            overflow: hidden;
        }

        .custom-card .card-header {
            background-color: rgba(9, 60, 93, 0.02) !important;
            border-bottom: 1px solid rgba(9, 60, 93, 0.06);
            padding: 1.2rem 1.5rem;
            color: var(--deep-navy);
            font-weight: 700;
        }

        .custom-card .card-body {
            padding: 1.5rem;
        }

        /* Form Layout & Control Elements */
        .form-label {
            color: var(--mid-blue);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .custom-input {
            border: 1px solid rgba(9, 60, 93, 0.15);
            border-radius: 10px;
            padding: 0.6rem 1rem;
            color: var(--deep-navy);
            background-color: #FFFFFF;
            transition: all 0.2s ease;
        }

        .custom-input:focus {
            border-color: var(--mid-blue);
            box-shadow: 0 0 0 3px rgba(59, 117, 151, 0.15);
            outline: none;
        }

        .custom-input:disabled {
            background-color: var(--light-gray);
            border-color: rgba(9, 60, 93, 0.06);
            color: rgba(9, 60, 93, 0.6);
            cursor: not-allowed;
        }

        /* Action Buttons */
        .btn-brand-primary {
            background-color: var(--deep-navy);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-brand-primary:hover {
            background-color: var(--mid-blue);
            color: #FFFFFF;
        }

        .btn-brand-outline {
            border: 1px solid var(--mid-blue);
            color: var(--mid-blue);
            background: transparent;
            border-radius: 8px;
            padding: 0.4rem 1rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-brand-outline:hover {
            background-color: rgba(59, 117, 151, 0.08);
            color: var(--deep-navy);
            border-color: var(--deep-navy);
        }

        .btn-brand-outline:active {
            background-color: rgb(255, 0, 0);
            color: #fff;
            border-color: #fff;
        }

        /* Identity Elements */
        .avatar-frame {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--deep-navy), var(--mid-blue));
            margin: 0 auto 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            box-shadow: 0 4px 15px rgba(9, 60, 93, 0.15);
        }

        .status-badge-active {
            background-color: rgba(156, 176, 122, 0.15);
            color: var(--olive-green);
            font-weight: 700;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .list-group-item {
            border-color: rgba(9, 60, 93, 0.05);
            padding: 1rem 1.2rem;
            background-color: transparent;
        }

        [x-cloak] { display: none !important; }
    </style>
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
}" x-init="window.themeManager.initTheme()">

    <div style="display: flex; min-height: 100vh;">
        <!-- Navigation Sidebar Include -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Workspace Frame -->
        <div class="main-workspace">
            <!-- Global Application Top Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Page Content -->
            <main class="p-4 p-md-5">
                <div class="container-fluid p-0">
                    
                    <div class="mb-4">
                        <h2 class="fw-bold tracking-tight" style="color: var(--deep-navy);">My Profile</h2>
                        <p class="text-muted small">Manage your account credentials, directory visibility, and security settings.</p>
                    </div>

                    <div class="row g-4">
                        <!-- Profile Form Matrix Details -->
                        <div class="col-lg-8">
                            <div class="card custom-card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-bold fs-6">Profile Settings</h5>
                                    <button class="btn btn-sm btn-brand-outline" @click="editMode = !editMode" type="button">
                                        <span x-show="!editMode"><i class="bi bi-pencil me-1"></i> Edit Profile</span>
                                        <span x-show="editMode" x-cloak>Cancel Changes</span>
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control custom-input" x-model="profile.name" :disabled="!editMode">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Architecture</label>
                                        <input type="email" class="form-control custom-input" x-model="profile.email" :disabled="!editMode">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Employee Core ID</label>
                                            <input type="text" class="form-control custom-input" x-model="profile.employeeId" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Assigned Department</label>
                                            <input type="text" class="form-control custom-input" x-model="profile.department" :disabled="!editMode">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Contact Phone</label>
                                            <input type="tel" class="form-control custom-input" x-model="profile.phone" :disabled="!editMode">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">System Enrolment Date</label>
                                            <input type="date" class="form-control custom-input" x-model="profile.joinDate" disabled>
                                        </div>
                                    </div>
                                    <button 
                                        class="btn btn-brand-primary mt-2" 
                                        @click="saveProfile()" 
                                        x-show="editMode"
                                        x-cloak
                                        type="button"
                                    >
                                        <i class="bi bi-check2-circle me-1"></i> Commit Configuration Changes
                                    </button>
                                </div>
                            </div>

                            <!-- Change Passcode Protocols -->
                            <div class="card custom-card">
                                <div class="card-header">
                                    <h5 class="mb-0 fw-bold fs-6">Security Access Verification</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Current Account Password</label>
                                        <input type="password" class="form-control custom-input" x-model="passwordForm.currentPassword">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Target New Password</label>
                                        <input type="password" class="form-control custom-input" x-model="passwordForm.newPassword">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Confirm Target Password Configuration</label>
                                        <input type="password" class="form-control custom-input" x-model="passwordForm.confirmPassword">
                                    </div>
                                    <button class="btn btn-brand-primary mt-2" @click="changePassword()" type="button">
                                        <i class="bi bi-shield-lock me-1"></i> Force Password Rotation
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Metadata Info Column -->
                        <div class="col-lg-4">
                            <!-- User Identity Frame -->
                            <div class="card custom-card mb-4 text-center">
                                <div class="card-body py-4">
                                    <div class="avatar-frame">
                                        <i class="bi bi-person-fill display-5"></i>
                                    </div>
                                    <h5 class="fw-bold mb-1" style="color: var(--deep-navy);" x-text="profile.name"></h5>
                                    <p class="text-muted small mb-3" x-text="profile.department"></p>
                                    <button class="btn btn-sm btn-brand-outline px-3" type="button">
                                        <i class="bi bi-cloud-upload me-1"></i> Modify Avatar
                                    </button>
                                </div>
                            </div>

                            <!-- Dynamic Context Account Metric Logs -->
                            <div class="card custom-card">
                                <div class="card-header">
                                    <h6 class="mb-0 fw-bold small text-uppercase tracking-wider">Operational Parameters</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted d-block font-monospace">VERIFICATION STATE</small>
                                            <span class="status-badge-active d-inline-block mt-1">ACTIVE IDENTITY</span>
                                        </div>
                                    </div>
                                    <div class="list-group-item">
                                        <small class="text-muted d-block font-monospace">ACCESS LEVEL GROUP</small>
                                        <div class="fw-semibold mt-1" style="color: var(--deep-navy);">Staff Personnel Member</div>
                                    </div>
                                    <div class="list-group-item">
                                        <small class="text-muted d-block font-monospace">LAST DEVICE HANDSHAKE</small>
                                        <div class="text-muted small mt-1"><?php echo date('M d, Y h:i A'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JavaScript Engine Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
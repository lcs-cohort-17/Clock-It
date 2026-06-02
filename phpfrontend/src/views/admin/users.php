<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// If not an admin, kick them completely out of the admin routing context
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . route_url('/login'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Clock-It</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>

    <style>
        /* Clock-It Custom Corporate Design System Tokens */
        :root {
            --deep-navy: #093C5D;
            --mid-blue: #3B7597;
            --olive-green: #9CB07A;
            --light-gray: #F5F5F5;
            --transition-speed: 0.3s;
        }

        body {
            background-color: var(--light-gray);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--deep-navy);
            overflow-x: hidden;
        }

        /* Responsive Animated Sidebar Core Layout Frame */
        .app-sidebar {
            background-color: var(--deep-navy);
            color: #FFFFFF;
            min-height: 100vh;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 25px rgba(9, 60, 93, 0.15);
            overflow: hidden;
        }

        /* Sidebar Item Links styling */
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            padding: 0.85rem 1.5rem;
            margin: 0.2rem 1rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .sidebar-nav-link:hover {
            color: #FFFFFF;
            background-color: rgba(255, 255, 255, 0.08);
        }

        .sidebar-nav-link.active {
            color: #FFFFFF;
            background-color: var(--mid-blue);
            box-shadow: 0 4px 12px rgba(59, 117, 151, 0.3);
        }

        .sidebar-logout {
            background: transparent;
            border: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #FFA3A3;
            padding: 1rem 1.5rem;
            margin: auto 1rem 1.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            text-align: left;
            transition: all 0.2s ease;
        }

        .sidebar-logout:hover {
            background-color: rgba(255, 163, 163, 0.1);
            color: #FF6B6B;
        }

        /* Dynamic Main Workspace Engine Wrapper */
        .main-workspace {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        .app-header {
            background-color: #FFFFFF;
            border-bottom: 1px solid rgba(9, 60, 93, 0.06);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(9, 60, 93, 0.02);
        }

        /* Custom Structure Component Blocks */
        .custom-card {
            background-color: #FFFFFF;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(9, 60, 93, 0.03);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .custom-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(9, 60, 93, 0.06);
        }

        .custom-card .card-header {
            background-color: rgba(9, 60, 93, 0.01) !important;
            border-bottom: 1px solid rgba(9, 60, 93, 0.06);
            padding: 1.2rem 1.5rem;
            color: var(--deep-navy);
            font-weight: 700;
        }

        /* Custom Action System Buttons */
        .btn-brand-primary {
            background-color: var(--deep-navy);
            color: #FFFFFF;
            border: none;
            font-weight: 600;
            transition: background-color 0.2s ease;
        }
        .btn-brand-primary:hover {
            background-color: var(--mid-blue);
            color: #FFFFFF;
        }

        /* Native App Dark Mode Matrix Overrides */
        [data-bs-theme="dark"] {
            --light-gray: #0B131A;
            --deep-navy: #E6F0F7;
            --mid-blue: #6FAAD0;
            
            .app-header {
                background-color: #121F2B;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }

            .custom-card {
                background-color: #121F2B !important;
                box-shadow: 0 4px 25px rgba(0, 0, 0, 0.2);
            }

            .custom-card .card-header {
                background-color: rgba(255, 255, 255, 0.02) !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }

            .table {
                --bs-table-bg: transparent;
                color: var(--deep-navy);
            }

            .text-muted {
                color: #A3B8CC !important;
            }

            .modal-content {
                background-color: #121F2B;
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: var(--deep-navy);
            }
            .form-control, .form-select {
                background-color: #0B131A;
                border-color: rgba(255, 255, 255, 0.1);
                color: #FFFFFF;
            }
            .form-control:focus, .form-select:focus {
                background-color: #0B131A;
                color: #FFFFFF;
            }
        }

        /* Alpine Modal Custom Wrapper Layer Styles */
        .custom-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(9, 60, 93, 0.4);
            backdrop-filter: blur(4px);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ 
    sidebarOpen: true,
    searchQuery: '',
    showModal: false,
    isEditing: false,
    editingUserId: null,
    
    users: [
        { id: 1, employeeId: 'EMP-001', name: 'John Doe', email: 'john@example.com', role: 'staff', status: 'active' },
        { id: 2, employeeId: 'EMP-002', name: 'Jane Smith', email: 'jane@example.com', role: 'staff', status: 'active' },
        { id: 3, employeeId: 'EMP-003', name: 'Bob Johnson', email: 'bob@example.com', role: 'admin', status: 'active' }
    ],
    
    userForm: {
        name: '',
        email: '',
        role: 'staff',
        employeeId: '',
        status: 'active'
    },

    get filteredUsers() {
        return this.users.filter(user => 
            user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            user.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            user.employeeId.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    },

    openAddModal() {
        this.isEditing = false;
        this.editingUserId = null;
        this.userForm = { name: '', email: '', role: 'staff', employeeId: '', status: 'active' };
        this.showModal = true;
    },

    openEditModal(user) {
        this.isEditing = true;
        this.editingUserId = user.id;
        this.userForm = { ...user };
        this.showModal = true;
    },

    saveUser() {
        if (!this.userForm.name || !this.userForm.email || !this.userForm.employeeId) {
            alert('Please fill out all required parameters.');
            return;
        }

        if (this.isEditing) {
            // Update mapping operation
            const idx = this.users.findIndex(u => u.id === this.editingUserId);
            if (idx !== -1) {
                this.users[idx] = { id: this.editingUserId, ...this.userForm };
            }
        } else {
            // Create insertion operation
            const newId = this.users.length ? Math.max(...this.users.map(u => u.id)) + 1 : 1;
            this.users.push({ id: newId, ...this.userForm });
        }
        this.showModal = false;
    },

    deleteUser(id) {
        if (confirm('Are you absolutely certain you want to purge this record matrix from the registry?')) {
            this.users = this.users.filter(u => u.id !== id);
        }
    }
}" x-init="window.themeManager.initTheme()">
    
    <div style="display: flex; min-height: 100vh;">
        
        <aside class="app-sidebar" :style="{ width: sidebarOpen ? '280px' : '0px' }">
            <div class="p-4 border-bottom border-secondary border-opacity-25" style="min-width: 280px;">
                <h4 class="fw-bold mb-1" style="color: #FFFFFF;"><i class="bi bi-clock-history me-2"></i>Clock-It</h4>
                <p class="small text-white text-opacity-50 mb-0 uppercase tracking-wider font-monospace">Administrative Panel</p>
            </div>

            <nav class="sidebar-nav mt-4" style="min-width: 280px;">
                <a href="<?= route_url('/admin-dashboard') ?>" class="sidebar-nav-link">
                    <i class="bi bi-grid-1x2-fill fs-5"></i>
                    <span>Dashboard Overview</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/users') ?>" class="sidebar-nav-link active">
                    <i class="bi bi-people-fill fs-5"></i>
                    <span>User Management</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/attendance') ?>" class="sidebar-nav-link">
                    <i class="bi bi-journal-check fs-5"></i>
                    <span>Attendance Log</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/qr-generator') ?>" class="sidebar-nav-link">
                    <i class="bi bi-qr-code fs-5"></i>
                    <span>QR Code Generator</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/settings') ?>" class="sidebar-nav-link">
                    <i class="bi bi-sliders fs-5"></i>
                    <span>System Settings</span>
                </a>
            </nav>

            <button class="sidebar-logout" @click="logoutUser()" type="button" style="min-width: 280px;">
                <i class="bi bi-box-arrow-left fs-5"></i>
                <span>Terminate Session</span>
            </button>
        </aside>

        <div class="main-workspace" :style="{ marginLeft: sidebarOpen ? '280px' : '0px' }">
            
            <header class="app-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="btn btn-sm btn-light border d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 8px;" type="button">
                        <i class="bi bi-list fs-5 text-dark"></i>
                    </button>
                    <h5 class="mb-0 fw-bold" style="color: var(--deep-navy);">User Management Matrix</h5>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <?php include __DIR__ . '/../partials/theme-toggle.php'; ?>
                    
                    <div class="vr mx-1 opacity-25"></div>
                    
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-secondary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold text-uppercase" style="width: 36px; height: 36px; border-radius: 50%; font-size: 0.85rem; border: 1px solid rgba(9, 60, 93, 0.1);">
                            <?= substr(htmlspecialchars($_SESSION['user_name'] ?? 'A'), 0, 2); ?>
                        </div>
                        <div class="small fw-semibold d-none d-sm-block text-muted">
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin System'); ?>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 p-md-5 flex-grow-1">
                <div class="container-fluid p-0">
                    
                    <div class="mb-4">
                        <h2 class="fw-bold mb-1" style="color: var(--deep-navy);">Manage Security Access</h2>
                        <p class="text-muted small">Configure profiles, credential groupings, and global identity authorizations.</p>
                    </div>

                    <div class="card custom-card p-3 mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8 col-lg-9">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        class="form-control border-start-0 ps-0" 
                                        placeholder="Query entries via name, index ID, or explicit address domain..."
                                        x-model="searchQuery"
                                    >
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3 text-md-end">
                                <button class="btn btn-brand-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" @click="openAddModal()" type="button">
                                    <i class="bi bi-person-plus-fill fs-5"></i>
                                    <span>Add User Profile</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 fw-bold fs-6"><i class="bi bi-shield-lock me-2"></i>Authorized Personnel Registry</h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 small fw-semibold" x-text="`${filteredUsers.length} Logged Object(s)`"></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                                    <thead class="bg-light table-light">
                                        <tr>
                                            <th class="ps-4 py-3 text-uppercase font-monospace small tracking-wider text-muted">Employee ID</th>
                                            <th class="py-3 text-uppercase font-monospace small tracking-wider text-muted">Full Name</th>
                                            <th class="py-3 text-uppercase font-monospace small tracking-wider text-muted">Email Identity</th>
                                            <th class="py-3 text-uppercase font-monospace small tracking-wider text-muted">Role Domain</th>
                                            <th class="py-3 text-uppercase font-monospace small tracking-wider text-muted">Status</th>
                                            <th class="pe-4 py-3 text-end text-uppercase font-monospace small tracking-wider text-muted">Actions Control</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="user in filteredUsers" :key="user.id">
                                            <tr>
                                                <td class="ps-4 font-monospace fw-semibold text-muted" x-text="user.employeeId"></td>
                                                <td class="fw-bold" style="color: var(--deep-navy);" x-text="user.name"></td>
                                                <td class="text-secondary" x-text="user.email"></td>
                                                <td>
                                                    <span class="badge px-2.5 py-1.5 rounded-2 font-monospace fw-bold" 
                                                          :class="user.role === 'admin' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-info bg-opacity-10 text-dark'" 
                                                          x-text="user.role.toUpperCase()">
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge px-2.5 py-1.5 rounded-2 font-monospace fw-bold" 
                                                          :class="user.status === 'active' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-20 text-muted'" 
                                                          x-text="user.status.toUpperCase()">
                                                    </span>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <div class="d-inline-flex gap-2">
                                                        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 px-2.5 py-1.5" @click="openEditModal(user)" type="button">
                                                            <i class="bi bi-pencil-square"></i> Modify
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 px-2.5 py-1.5" @click="deleteUser(user.id)" type="button">
                                                            <i class="bi bi-trash3-fill"></i> Purge
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="filteredUsers.length === 0" x-cloak>
                                            <td colspan="6" class="text-center text-muted py-5 font-monospace">
                                                <i class="bi bi-exclamation-triangle-fill text-warning fs-3 mb-2 d-block"></i>
                                                Zero active profile records fit current tracking telemetry parameters.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <div class="custom-modal-backdrop" x-show="showModal" x-transition.opacity x-cloak>
        <div class="modal-dialog w-100 style-config" style="max-width: 520px; z-index: 1060;" @click.away="showModal = false">
            <div class="card custom-card shadow-lg border">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                    <h5 class="fw-bold mb-0 fs-6" style="color: var(--deep-navy);" x-text="isEditing ? 'Modify Personnel Matrix Record' : 'Register New Personnel Unit'"></h5>
                    <button type="button" class="btn-close" @click="showModal = false"></button>
                </div>
                <div class="card-body p-4">
                    <form @submit.prevent="saveUser()">
                        
                        <div class="mb-3">
                            <label class="form-label font-monospace small text-muted text-uppercase fw-bold">Employee System Identification Number *</label>
                            <input type="text" class="form-control py-2" placeholder="e.g., EMP-0943" x-model="userForm.employeeId" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-monospace small text-muted text-uppercase fw-bold">Full Legal Name *</label>
                            <input type="text" class="form-control py-2" placeholder="e.g., Jonathan Vance" x-model="userForm.name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-monospace small text-muted text-uppercase fw-bold">Corporate Identity Email Address *</label>
                            <input type="email" class="form-control py-2" placeholder="e.g., vance@clockit.corp" x-model="userForm.email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-monospace small text-muted text-uppercase fw-bold">Privilege Tier Access Level *</label>
                            <select class="form-select py-2" x-model="userForm.role">
                                <option value="staff">Staff (Standard Tracking Gateway privileges)</option>
                                <option value="admin">Admin (Full Telemetry Dashboard control layout)</option>
                            </select>
                        </div>

                        <div class="mb-4" x-show="isEditing">
                            <label class="form-label font-monospace small text-muted text-uppercase fw-bold">Registry Authorization Status</label>
                            <select class="form-select py-2" x-model="userForm.status">
                                <option value="active">Active Operational Status</option>
                                <option value="suspended">Suspended Security Hold</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-4">
                            <button type="button" class="btn btn-light border px-4 py-2 fw-semibold" @click="showModal = false">Abort</button>
                            <button type="submit" class="btn btn-brand-primary px-4 py-2" x-text="isEditing ? 'Commit Changes' : 'Initialize Profile'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logoutUser() {
            if (confirm('Are you sure you want to sign out?')) {
                window.location.href = '<?= route_url('/logout') ?>';
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
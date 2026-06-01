<?php
/**
 * User Management Page
 * Admin page for managing users and roles
 */
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="<?= asset_url('js/utilities.js') ?>"></script>
</head>
<body x-data="{ 
    sidebarOpen: true,
    searchQuery: '',
    users: [
        { id: 1, name: 'John Doe', email: 'john@example.com', role: 'staff', status: 'active' },
        { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'staff', status: 'active' },
        { id: 3, name: 'Bob Johnson', email: 'bob@example.com', role: 'admin', status: 'active' }
    ],
    get filteredUsers() {
        return this.users.filter(user => 
            user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            user.email.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    },
    showAddModal: false,
    newUser: {
        name: '',
        email: '',
        role: 'staff',
        employeeId: ''
    }
}" @init="window.themeManager.initTheme()">
    
    <div style="display: flex;">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <!-- Page Content -->
            <main class="dashboard-section" style="padding: 2rem;">
                <div class="container-fluid">
                    <!-- Toolbar -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div style="flex: 1; margin-right: 1rem;">
                            <input 
                                type="text" 
                                class="form-control" 
                                placeholder="Search users by name or email..."
                                x-model="searchQuery"
                            >
                        </div>
                        <button class="btn btn-primary" @click="showAddModal = true" type="button">
                            <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Add User
                        </button>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="user in filteredUsers" :key="user.id">
                                    <tr>
                                        <td x-text="user.name"></td>
                                        <td x-text="user.email"></td>
                                        <td>
                                            <span class="badge" :class="user.role === 'admin' ? 'bg-danger' : 'bg-secondary'" x-text="user.role"></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success" x-text="user.status"></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary">Edit</button>
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredUsers.length === 0">
                                    <td colspan="5" class="text-center text-muted py-4">No users found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
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

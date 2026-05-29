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
        <aside class="app-sidebar" :style="{ width: sidebarOpen ? '16rem' : '0' }">
            <div>
                <h1>Clock-It</h1>
                <p>Admin Panel</p>
            </div>

            <nav class="sidebar-nav">
                <a href="<?= route_url('/admin-dashboard') ?>" class="sidebar-nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/users') ?>" class="sidebar-nav-link active">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0-6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 7c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4zm6 5h-12v-2c0-1.5 3.5-2.5 6-2.5s6 1 6 2.5v2z"/></svg>
                    <span>User Management</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/attendance') ?>" class="sidebar-nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.46.37.84-1.39-.46-.37L12 13V8h-2z"/></svg>
                    <span>Attendance Log</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/qr-generator') ?>" class="sidebar-nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M3 11h8V3H3v8zm2-6h4v4H5V5zm8-2v8h8V3h-8zm6 6h-4V5h4v4zM3 21h8v-8H3v8zm2-6h4v4H5v-4zm13-2h1v4h-1v-4zm-4 4h4v1h-4v-1zm1-3h1v2h-1v-2z"/></svg>
                    <span>QR Generator</span>
                </a>

                <a href="<?= route_url('/admin-dashboard/settings') ?>" class="sidebar-nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l1.72-1.35c.19-.15.24-.42.12-.64l-1.63-2.83c-.12-.22-.39-.3-.61-.22l-2.03.81c-.42-.32-.86-.58-1.35-.78L15 2.5c-.04-.25-.25-.43-.5-.43h-3.26c-.25 0-.46.18-.49.43L10.88 5.5c-.48.2-.93.47-1.35.78l-2.03-.81c-.22-.09-.49 0-.61.22L5.25 8.54c-.13.22-.07.49.12.64l1.72 1.35c-.05.3-.07.62-.07.94s.02.64.07.94l-1.72 1.35c-.19.15-.24.42-.12.64l1.63 2.83c.12.22.39.3.61.22l2.03-.81c.42.32.86.58 1.35.78l.32 2.15c.03.25.25.43.5.43h3.26c.25 0 .46-.18.49-.43l.32-2.15c.48-.2.93-.47 1.35-.78l2.03.81c.22.09.49 0 .61-.22l1.63-2.83c.13-.22.07-.49-.12-.64l-1.72-1.35zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                    <span>Settings</span>
                </a>
            </nav>

            <button class="sidebar-logout" @click="logoutUser()" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                <span>Sign Out</span>
            </button>
        </aside>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <header class="app-header">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button @click="sidebarOpen = !sidebarOpen" class="btn btn-sm btn-outline-secondary" type="button">
                        <span>☰</span>
                    </button>
                    <h2 style="margin: 0;">User Management</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <?php include __DIR__ . '/../partials/theme-toggle.php'; ?>
                </div>
            </header>

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
                            + Add User
                        </button>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead class="table-light">
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

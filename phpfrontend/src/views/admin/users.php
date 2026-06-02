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
        { id: 1, name: 'John Doe', email: 'john@example.com', role: 'staff', generatedPassword: 'ClockIt-8K2mPq', status: 'active' },
        { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'staff', generatedPassword: 'ClockIt-4Vn7Rt', status: 'active' },
        { id: 3, name: 'Bob Johnson', email: 'bob@example.com', role: 'admin', generatedPassword: 'ClockIt-9Xa3Ld', status: 'active' }
    ],
    nextUserId: 4,
    get filteredUsers() {
        return this.users.filter(user => 
            user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            user.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            user.role.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    },
    showUserModal: false,
    editingUserId: null,
    userForm: {
        name: '',
        email: '',
        role: 'staff'
    },
    get isEditingUser() {
        return this.editingUserId !== null;
    },
    get userModalTitle() {
        return this.isEditingUser ? 'Edit User' : 'Add User';
    },
    resetUserForm() {
        this.userForm = { name: '', email: '', role: 'staff' };
    },
    openAddUserModal() {
        this.editingUserId = null;
        this.resetUserForm();
        this.showUserModal = true;
    },
    openEditUserModal(user) {
        this.editingUserId = user.id;
        this.userForm = {
            name: user.name,
            email: user.email,
            role: user.role
        };
        this.showUserModal = true;
    },
    closeUserModal() {
        this.showUserModal = false;
        this.editingUserId = null;
        this.resetUserForm();
    },
    generatePassword(length = 12) {
        const characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
        const values = new Uint32Array(length);
        window.crypto.getRandomValues(values);

        return Array.from(values, value => characters[value % characters.length]).join('');
    },
    async sendInviteEmail(user) {
        const response = await fetch('<?= route_url('/api/users/invite') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: user.name,
                email: user.email,
                role: user.role,
                password: user.generatedPassword
            })
        });
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || 'Invite email could not be sent.');
        }
    },
    async saveUser() {
        const name = this.userForm.name.trim();
        const email = this.userForm.email.trim();
        const role = this.userForm.role;

        if (!name || !email || !role) {
            alert('Please fill in Name, Email, and Role.');
            return;
        }

        if (this.isEditingUser) {
            this.users = this.users.map(user => user.id === this.editingUserId
                ? { ...user, name, email, role }
                : user
            );
        } else {
            const user = {
                id: this.nextUserId++,
                name,
                email,
                role,
                generatedPassword: this.generatePassword(),
                status: 'active'
            };

            this.users.push(user);

            try {
                await this.sendInviteEmail(user);
                alert(`User added and invite email sent to ${email}.`);
            } catch (error) {
                alert(error.message);
            }
        }

        this.closeUserModal();
    },
    deleteUser(user) {
        if (!confirm(`Delete ${user.name}?`)) return;
        this.users = this.users.filter(existingUser => existingUser.id !== user.id);
    },
    userInitials(name) {
        return name
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map(part => part[0]?.toUpperCase() || '')
            .join('') || 'U';
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
                        <button class="btn btn-primary" @click="openAddUserModal()" type="button">
                            <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Add User
                        </button>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Generated Password</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="user in filteredUsers" :key="user.id">
                                    <tr>
                                        <td>
                                            <div class="user-profile-photo" x-text="userInitials(user.name)" aria-label="User profile picture"></div>
                                        </td>
                                        <td x-text="user.name"></td>
                                        <td x-text="user.email"></td>
                                        <td>
                                            <span class="badge" :class="user.role === 'admin' ? 'bg-danger' : 'bg-secondary'" x-text="user.role"></span>
                                        </td>
                                        <td>
                                            <code x-text="user.generatedPassword"></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-success" x-text="user.status"></span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-secondary" @click="openEditUserModal(user)" type="button">
                                                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" @click="deleteUser(user)" type="button">
                                                    <i class="bi bi-trash me-1" aria-hidden="true"></i>Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredUsers.length === 0">
                                    <td colspan="7" class="text-center text-muted py-4">No users found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Add/Edit User Modal -->
                    <div class="modal fade show user-form-modal" x-show="showUserModal" x-cloak tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="userFormTitle">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form @submit.prevent="saveUser()">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="userFormTitle" x-text="userModalTitle"></h5>
                                        <button type="button" class="btn-close" aria-label="Close" @click="closeUserModal()"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label" for="userName">Name</label>
                                            <input id="userName" class="form-control" type="text" x-model="userForm.name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="userEmail">Email</label>
                                            <input id="userEmail" class="form-control" type="email" x-model="userForm.email" required>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label" for="userRole">Role</label>
                                            <select id="userRole" class="form-select" x-model="userForm.role" required>
                                                <option value="staff">Staff</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" @click="closeUserModal()">Cancel</button>
                                        <button type="submit" class="btn btn-primary" x-text="isEditingUser ? 'Save Changes' : 'Add User'"></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-backdrop fade show" x-show="showUserModal" x-cloak></div>
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

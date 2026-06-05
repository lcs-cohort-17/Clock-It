<?php ob_start(); ?>

<!-- Add x-data and x-init directly in HTML -->
<div class="app-shell" id="userManagementApp" data-api-user-management 
     x-data="userManager()" x-init="init()">
    
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-panel">
        <?php include __DIR__ . '/../partials/header.php'; ?>

        <div class="content">
            <div class="container-fluid py-5 px-4">
                
                <!-- PAGE CARD -->
                <div class="page-card p-4 p-xl-5 shadow-sm">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <h1 class="main-title">User Management</h1>
                            <p class="subtitle">Add, edit, or disable accounts. Connected to live database.</p>
                        </div>

                        <button class="btn btn-main" type="button" @click="openAddModal()">
                            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
                            Add User
                        </button>
                    </div>

                    <!-- Success Alert -->
                    <div x-show.important="successMessage" 
                         x-cloak
                         x-transition
                         class="alert alert-success d-flex align-items-center gap-2 position-relative" 
                         role="alert">
                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                        <span x-text="successMessage"></span>
                        <button type="button" class="btn-close position-absolute end-0 me-3" @click="successMessage = ''" aria-label="Close"></button>
                    </div>

                    <!-- Error Alert -->
                    <div x-show.important="errorMessage" 
                         x-cloak
                         x-transition
                         class="alert alert-danger d-flex align-items-center gap-2 position-relative" 
                         role="alert">
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                        <span x-text="errorMessage"></span>
                        <button type="button" class="btn-close position-absolute end-0 me-3" @click="errorMessage = ''" aria-label="Close"></button>
                    </div>

                    <!-- Loading -->
                    <div x-show="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading users...</p>
                    </div>

                    <!-- Search -->
                    <div class="search-box position-relative w-100 mb-4">
                        <i class="bi bi-search search-icon" aria-hidden="true"></i>
                        <input type="text"
                               x-model="searchQuery"
                               @input="onSearchInput()"
                               placeholder="Search by name, email, or employee ID"
                               class="form-control search-input ps-5">
                        <button x-show="searchQuery.length > 0"
                                @click="clearSearch()"
                                class="btn btn-sm btn-link position-absolute end-0 top-50 translate-middle-y text-muted"
                                type="button">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div x-show="searchQuery.length > 0" class="mb-3 text-muted">
                        Found <strong x-text="users.length"></strong> result(s)
                    </div>

                    <!-- Table -->
                    <div x-show="!loading" class="table-wrapper">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Employee ID</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="user in users" :key="user.employeeId">
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar" x-text="getInitials(user.name)"></div>
                                                <div class="fw-semibold" x-text="user.name"></div>
                                            </div>
                                        </td>
                                        <td x-text="user.email"></td>
                                        <td x-text="user.employeeId"></td>
                                        <td>
                                            <span class="badge-admin" x-show="user.role === 'Admin'">Admin</span>
                                            <span class="badge-staff" x-show="user.role !== 'Admin'">Staff</span>
                                        </td>
                                        <td>
                                            <span class="status" :class="user.status.toLowerCase()" x-text="user.status"></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="action-buttons">
                                                <!-- EDIT BUTTON -->
                                                <button class="btn btn-light btn-icon"
                                                        type="button"
                                                        @click="openEditModal(user)"
                                                        title="Edit user">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <!-- RESET PASSWORD BUTTON -->
                                                <button class="btn btn-light btn-icon"
                                                        type="button"
                                                        @click="resetPassword(user)"
                                                        title="Reset password">
                                                    <i class="bi bi-key"></i>
                                                </button>

                                                <!-- TOGGLE STATUS BUTTON -->
                                                <button class="btn btn-outline-danger btn-icon"
                                                        type="button"
                                                        @click="toggleStatus(user)"
                                                        :title="user.status === 'Active' ? 'Deactivate' : 'Activate'">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="users.length === 0">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        No users found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="!loading" class="d-flex justify-content-between align-items-center mt-4">
                        <span class="text-muted" x-text="`Showing ${users.length} user(s)`"></span>
                        <button @click="fetchUsers()" class="btn btn-outline-secondary btn-sm" type="button" :disabled="loading">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            Refresh
                        </button>
                    </div>
                </div>
                <!-- END PAGE CARD -->

                <!-- ADD USER MODAL -->
                <div class="modal-overlay" x-show="showAddModal" x-cloak @click.self="showAddModal = false">
                    <div class="modal-box">
                        <div class="d-flex justify-content-between mb-4">
                            <h3>Add User</h3>
                            <button class="btn-close" type="button" @click="showAddModal = false"></button>
                        </div>
                        <form @submit.prevent="addUser()">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" x-model="newUser.first_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" x-model="newUser.last_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" x-model="newUser.email" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Role</label>
                                <select x-model="newUser.role" class="form-select">
                                    <option value="staff">Staff</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" @click="showAddModal = false">Cancel</button>
                                <button type="submit" class="btn btn-main" :disabled="saving">
                                    <span x-show="saving" class="spinner-border spinner-border-sm me-1"></span>
                                    <span x-text="saving ? 'Saving...' : 'Save User'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- EDIT USER MODAL -->
                <div class="modal-overlay" x-show="showEditModal" x-cloak @click.self="showEditModal = false">
                    <div class="modal-box">
                        <div class="d-flex justify-content-between mb-4">
                            <h3>Edit User</h3>
                            <button class="btn-close" type="button" @click="showEditModal = false"></button>
                        </div>
                        <form @submit.prevent="updateUser()">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" x-model="editUser.first_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" x-model="editUser.last_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" x-model="editUser.email" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Role</label>
                                <select x-model="editUser.role" class="form-select">
                                    <option value="staff">Staff</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" @click="showEditModal = false">Cancel</button>
                                <button type="submit" class="btn btn-main" :disabled="saving">
                                    <span x-show="saving" class="spinner-border spinner-border-sm me-1"></span>
                                    <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- PASSWORD RESET MODAL -->
                <div class="modal-overlay" x-show="showPasswordModal" x-cloak @click.self="closePasswordModal()">
                    <div class="modal-box">
                        <div class="d-flex justify-content-between mb-4">
                            <h3>Password Reset</h3>
                            <button class="btn-close" type="button" @click="closePasswordModal()"></button>
                        </div>
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-key-fill me-2"></i>
                            <strong>Temporary password for <span x-text="passwordResetUser?.name"></span></strong>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Temporary Password</label>
                            <div class="input-group">
                                <input type="text" x-model="generatedPassword" class="form-control font-monospace" readonly>
                                <button class="btn btn-outline-secondary" type="button" @click="copyPassword()">
                                    <i class="bi bi-clipboard me-1"></i>Copy
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between gap-2">
                            <button type="button" class="btn btn-outline-secondary" @click="closePasswordModal()">Close</button>
                            <button type="button" class="btn btn-main" @click="sendPasswordEmail()">
                                <i class="bi bi-envelope me-1"></i>Send via Email
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>

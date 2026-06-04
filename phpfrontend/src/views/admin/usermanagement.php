<?php
$query = $_GET['q'] ?? '';

/* Pagination - kept for fallback */
$page = max(1, (int) ($_GET['page'] ?? 1));
$pageSize = 5;
$totalPages = max(1, ceil(count($filtered) / $pageSize));
$page = min($page, $totalPages);
$start = ($page - 1) * $pageSize;
$pageData = array_slice($filtered, $start, $pageSize);

ob_start(); 
?>

<div class="app-shell" id="userManagementApp">
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-panel">
        <?php include __DIR__ . '/../partials/header.php'; ?>

        <div class="content">
            <div class="container-fluid py-5 px-4">
                <div class="page-card p-4 p-xl-5 shadow-sm">
                    
                    <!-- HEADER -->
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <h1 class="main-title">User Management</h1>
                            <p class="subtitle">Add, edit, or disable accounts. Connected to live database.</p>
                        </div>
                        <button class="btn btn-main" @click="openAddModal()">
                            <i class="bi bi-plus-lg me-1"></i> Add User
                        </button>
                    </div>

                    <!-- Messages -->
                    <div x-show="successMessage" x-transition class="alert alert-success d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span x-text="successMessage"></span>
                    </div>
                    <div x-show="errorMessage" x-transition class="alert alert-danger d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span x-text="errorMessage"></span>
                    </div>

                    <!-- Loading -->
                    <div x-show="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading users...</p>
                    </div>

                    <!-- SEARCH - Alpine.js powered, no page reload -->
                    <div class="search-box position-relative w-100 mb-4">
                        <i class="bi bi-search search-icon" aria-hidden="true"></i>
                        
                        <input type="text"
                               x-model="searchQuery"
                               @input="onSearchInput()"
                               @keydown.enter.prevent="onSearchSubmit()"
                               placeholder="Search by name, email, or employee ID"
                               autocomplete="off"
                               class="form-control search-input ps-5">
                        
                        <!-- Clear search button -->
                        <button x-show="searchQuery.length > 0"
                                @click="clearSearch()"
                                class="btn btn-sm btn-link position-absolute end-0 top-50 translate-middle-y text-muted"
                                type="button"
                                aria-label="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Search results count -->
                    <div x-show="searchQuery.length > 0" class="mb-3 text-muted">
                        Found <strong x-text="users.length"></strong> result(s) for "<span x-text="searchQuery"></span>"
                    </div>

                    <!-- TABLE -->
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
                                            <span class="status" 
                                                  :class="user.status.toLowerCase()"
                                                  x-text="user.status"></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="action-buttons">
                                                <!-- Edit -->
                                                <button class="btn btn-light btn-icon"
                                                        @click="openEditModal(user)"
                                                        title="Edit user"
                                                        :aria-label="'Edit ' + user.name">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                
                                                <!-- Reset Password -->
                                                <button class="btn btn-light btn-icon"
                                                        @click="resetPassword(user)"
                                                        title="Reset password"
                                                        :aria-label="'Reset password for ' + user.name">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                                
                                                <!-- Toggle Status -->
                                                <button class="btn btn-outline-danger btn-icon"
                                                        @click="toggleStatus(user)"
                                                        :title="user.status === 'Active' ? 'Deactivate user' : 'Activate user'"
                                                        :aria-label="(user.status === 'Active' ? 'Deactivate' : 'Activate') + ' ' + user.name">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                
                                <!-- Empty state -->
                                <tr x-show="users.length === 0">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-people" style="font-size: 3rem;"></i>
                                        <p class="mt-2" x-show="searchQuery.length > 0">No users match your search.</p>
                                        <p class="mt-2" x-show="searchQuery.length === 0">No users found. Click "Add User" to create one.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Users count -->
                    <div x-show="!loading" class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                        <div class="text-muted">
                            Showing <span x-text="users.length"></span> user<span x-show="users.length !== 1">s</span>
                        </div>
                        <button @click="fetchUsers()" class="btn btn-outline-secondary btn-sm" :disabled="loading">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- ADD MODAL -->
                <div class="modal-overlay" x-show="showAddModal" x-cloak @click.self="showAddModal = false">
                    <div class="modal-box">
                        <div class="d-flex justify-content-between mb-4">
                            <h3>Add User</h3>
                            <button class="btn-close" @click="showAddModal = false" aria-label="Close"></button>
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
                                <label class="form-label">Employee ID <small class="text-muted">(optional, auto-generated if empty)</small></label>
                                <input type="text" x-model="newUser.employee_id" class="form-control" placeholder="e.g., A-011">
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
                            <!-- Show generated password after creating -->
                            <div x-show="generatedPassword" class="alert alert-info mb-3">
                                <strong>🔑 Generated Password:</strong> 
                                <code class="user-select-all" x-text="generatedPassword"></code>
                                <br><small class="text-muted">Copy this password now. It won't be shown again.</small>
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

                <!-- EDIT MODAL -->
                <div class="modal-overlay" x-show="showEditModal" x-cloak @click.self="showEditModal = false">
                    <div class="modal-box">
                        <div class="d-flex justify-content-between mb-4">
                            <h3>Edit User</h3>
                            <button class="btn-close" @click="showEditModal = false" aria-label="Close"></button>
                        </div>
                        <form @submit.prevent="updateUser()">
                            <input type="hidden" x-model="editUser.employee_id">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" x-model="editUser.first_name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" x-model="editUser.last_name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" x-model="editUser.email" class="form-control">
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
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
<?php
$query = $_GET['q'] ?? '';

/* Pagination - kept for fallback */
$page = max(1, (int) ($_GET['page'] ?? 1));
$pageSize = 5;
<<<<<<< HEAD

$filteredUsers = array_values($filtered ?? $users ?? []);
$totalPages = max(1, (int) ceil(count($filteredUsers) / $pageSize));

$page = min($page, $totalPages);

$pageData = array_slice($filteredUsers, ($page - 1) * $pageSize, $pageSize);
=======
$totalPages = max(1, ceil(count($filtered) / $pageSize));
$page = min($page, $totalPages);
$start = ($page - 1) * $pageSize;
$pageData = array_slice($filtered, $start, $pageSize);

ob_start(); 
?>
>>>>>>> 579e75d1125dd71394d1c92fa188610e28ed1ede

<div class="app-shell" id="userManagementApp">
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-panel">
        <?php include __DIR__ . '/../partials/header.php'; ?>

        <div class="content">
<<<<<<< HEAD
<div class="container-fluid py-5 px-4"
     x-data="userManager()">

    <div class="page-card p-4 p-xl-5 shadow-sm">

        <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

        <div>

            <h1 class="main-title">
                User Management
            </h1>

            <p class="subtitle">
                Add, edit, or disable accounts. Self-registration is disabled.
            </p>

        </div>

        <button class="btn btn-main"
                type="button"
                data-open-user-modal="add"
                @click="showAddModal = true">

            + Add User

        </button>

    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success d-flex align-items-center gap-2" role="status" data-auto-dismiss-alert>
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <?= e($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <!-- SEARCH -->

    <form method="GET" action="<?= e(app_url('/admin-dashboard/users')) ?>"
          class="mb-4">

        <div class="search-box position-relative w-100">

            <i class="bi bi-search search-icon" aria-hidden="true"></i>

            <input type="text"
                   name="q"
                   value="<?= e($query) ?>"
                   placeholder="Search by name, email, or employee ID"
                   autocomplete="off"
                   data-user-search
                   class="form-control search-input ps-5">

        </div>

    </form>

    <!-- TABLE -->

    <div class="table-wrapper">

        <table class="table align-middle mb-0">

            <thead>

            <tr>

                <th>Name</th>

                <th>Email</th>

                <th>Employee ID</th>

                <th>Role</th>

                <th>Status</th>

                <th class="text-end">
                    Actions
                </th>

            </tr>

            </thead>

            <tbody>

            <?php foreach ($pageData as $u): ?>

                <tr data-user-row
                    data-user-search-value="<?= e(strtolower($u['name'] . ' ' . $u['email'] . ' ' . $u['employeeId'])) ?>">

                    <td>

                        <div class="d-flex align-items-center gap-3">

                            <div class="avatar">

                                <?= e(initials($u['name'])) ?>

                            </div>

                            <div class="fw-semibold">

                                <?= e($u['name']) ?>

                            </div>

                        </div>

                    </td>

                    <td><?= e($u['email']) ?></td>

                    <td><?= e($u['employeeId']) ?></td>

                    <td>

                        <?php if ($u['role'] === 'Admin'): ?>

                            <span class="badge-admin">
                                Admin
                            </span>

                        <?php else: ?>

                            <span class="badge-staff">
                                Staff
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <span class="status <?= strtolower($u['status']) ?>">

                            <?= e($u['status']) ?>

                        </span>

                    </td>


                    <td class="text-end">
                        <div class="action-buttons">

                        <!-- EDIT -->

                        <button
                          type="button"
                          class="btn btn-light btn-icon"
                          title="Edit user"
                          aria-label="Edit <?= e($u['name']) ?>"
                          data-open-user-modal="edit"
                          data-edit-user='<?= e(json_encode([
                              'id' => $u['id'],
                              'name' => $u['name'],
                              'email' => $u['email'],
                              'role' => $u['role'],
                          ], JSON_THROW_ON_ERROR | JSON_HEX_APOS)) ?>'

                                @click='openEditModal(<?= e(json_encode([
                                    'id' => $u['id'],
                                    'name' => $u['name'],
                                    'email' => $u['email'],
                                    'role' => $u['role'],
                                ], JSON_THROW_ON_ERROR | JSON_HEX_APOS)) ?>)'>

                            <i class="bi bi-pencil" aria-hidden="true"></i>

=======
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
>>>>>>> 579e75d1125dd71394d1c92fa188610e28ed1ede
                        </button>
                    </div>

<<<<<<< HEAD
                        <!-- COPY INVITE -->

                        <button
                          type="button"
                          class="btn btn-light btn-icon"
                          title="Copy invite details"
                          aria-label="Copy invite details for <?= e($u['name']) ?>"
                          data-copy-user
                          data-copy-value="<?= e($u['name'] . ' | ' . $u['email'] . ' | ' . $u['employeeId']) ?>">

                            <i class="bi bi-clipboard" aria-hidden="true"></i>

                        </button>

                        <!-- RESET PASSWORD -->

                        <form method="POST" action="<?= e(app_url('/admin-dashboard/users')) ?>"
                              class="d-inline-flex">
                            <input type="hidden"
                                   name="id"
                                   value="<?= e($u['id']) ?>">

                            <button
    type="submit"
    name="reset_password"
    value="1"
    class="btn btn-light btn-icon"
    title="Reset password"
    aria-label="Reset password for <?= e($u['name']) ?>">

                                <i class="bi bi-key" aria-hidden="true"></i>

                            </button>

                        </form>

                        <!-- TOGGLE -->

                        <form method="POST" action="<?= e(app_url('/admin-dashboard/users')) ?>"
                              class="d-inline-flex">

                            <input type="hidden"
                                   name="id"
                                   value="<?= e($u['id']) ?>">

                           <button
    type="submit"
    name="toggle_status"
    value="1"
    class="btn btn-outline-danger btn-icon"
    title="<?= $u['status'] === 'Active' ? 'Disable user' : 'Enable user' ?>"
    aria-label="<?= $u['status'] === 'Active' ? 'Disable' : 'Enable' ?> <?= e($u['name']) ?>">

                                <i class="bi bi-slash-circle" aria-hidden="true"></i>

                            </button>

                        </form>
=======
                    <!-- Messages -->
                    <div x-show="successMessage" x-transition class="alert alert-success d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span x-text="successMessage"></span>
                    </div>
                    <div x-show="errorMessage" x-transition class="alert alert-danger d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span x-text="errorMessage"></span>
                    </div>
>>>>>>> 579e75d1125dd71394d1c92fa188610e28ed1ede

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

<<<<<<< HEAD
    </div>

    <!-- PAGINATION -->

    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">

        <div class="text-muted">

            Showing <span data-user-results><?= count($pageData) ?></span> of <?= count($filteredUsers) ?> employees
=======
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
>>>>>>> 579e75d1125dd71394d1c92fa188610e28ed1ede

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
<<<<<<< HEAD

        <div class="d-none align-items-center gap-2">

            <a href="<?= e(app_url('/admin-dashboard/users')) ?>?page=<?= max(1, $page - 1) ?>&q=<?= urlencode($query) ?>"
               class="btn btn-outline-secondary <?= $page <= 1 ? 'disabled' : '' ?>">
                Previous
            </a>

            <span class="text-muted px-2">
                Page <?= $page ?> of <?= $totalPages ?>
            </span>

            <a href="<?= e(app_url('/admin-dashboard/users')) ?>?page=<?= min($totalPages, $page + 1) ?>&q=<?= urlencode($query) ?>"
               class="btn btn-outline-secondary <?= $page >= $totalPages ? 'disabled' : '' ?>">
                Next
            </a>

        </div>

=======
>>>>>>> 579e75d1125dd71394d1c92fa188610e28ed1ede
    </div>
</div>

<<<<<<< HEAD
    <!-- ADD MODAL -->

    <div class="modal-overlay"
         data-user-modal="add"
         x-show="showAddModal"
         x-cloak>

        <div class="modal-box">

            <div class="d-flex justify-content-between mb-4">

                <h3>Add User</h3>

                <button class="btn-close"
                        type="button"
                        data-close-user-modal
                        @click="showAddModal = false">
                </button>

            </div>

            <form method="POST" action="<?= e(app_url('/admin-dashboard/users')) ?>">

                <div class="mb-3">

                    <label class="form-label">
                        Full Name
                    </label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           required>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input type="email"
                           name="email"
                           class="form-control"
                           required>

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Role
                    </label>

                    <select name="role"
                            class="form-select">

                        <option>Staff</option>

                        <option>Admin</option>

                    </select>

                </div>

                <div class="d-flex justify-content-end gap-2">

                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-close-user-modal
                            @click="showAddModal = false">

                        Cancel

                    </button>

                    <button name="add_user"
                            class="btn btn-main">

                        Save User

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- EDIT MODAL -->

    <div class="modal-overlay"
         data-user-modal="edit"
         x-show="showEditModal"
         x-cloak>

        <div class="modal-box">

            <div class="d-flex justify-content-between mb-4">

                <h3>Edit User</h3>

                <button class="btn-close"
                        type="button"
                        data-close-user-modal
                        @click="showEditModal = false">
                </button>

            </div>

            <form method="POST" action="<?= e(app_url('/admin-dashboard/users')) ?>">

                <input type="hidden"
                       name="id"
                       x-model="editId">

                <div class="mb-3">

                    <label class="form-label">
                        Full Name
                    </label>

                    <input type="text"
                           name="name"
                           x-model="editName"
                           class="form-control">

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input type="email"
                           name="email"
                           x-model="editEmail"
                           class="form-control">

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Role
                    </label>

                    <select name="role"
                            x-model="editRole"
                            class="form-select">

                        <option>Staff</option>

                        <option>Admin</option>

                    </select>

                </div>

                <div class="d-flex justify-content-end gap-2">

                    <button
    type="button"
    class="btn btn-outline-secondary"
    data-close-user-modal
    @click="showEditModal = false">

                        Cancel

                    </button>

                    <button name="edit_user"
                            class="btn btn-main">

                        Save Changes

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- PASSWORD MODAL -->

    <?php if (isset($_SESSION['generated_password'])): ?>

        <div class="password-modal">

            <div class="password-box">

                <h3 class="mb-3">
                    Temporary Password
                </h3>

                <p class="text-muted">
                    Share this password securely.
                </p>

                <div class="password-display">

                    <?= e($_SESSION['generated_password']) ?>

                </div>

                <div class="text-end mt-4">

                    <a href="<?= e(app_url('/admin-dashboard/users/clear-password')) ?>"
                       class="btn btn-main">

                        Close

                    </a>

                </div>

            </div>

        </div>

    <?php endif; ?>

    </div>
</div>
</div>


<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
=======
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
>>>>>>> 579e75d1125dd71394d1c92fa188610e28ed1ede

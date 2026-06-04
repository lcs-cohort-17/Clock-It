<?php
$query = $_GET['q'] ?? '';
/* -----------------------------
   PAGINATION
------------------------------*/

$page = max(1, (int) ($_GET['page'] ?? 1));

$pageSize = 5;

$filteredUsers = array_values($filtered ?? $users ?? []);
$totalPages = max(1, (int) ceil(count($filteredUsers) / $pageSize));

$page = min($page, $totalPages);

$pageData = array_slice($filteredUsers, ($page - 1) * $pageSize, $pageSize);

ob_start(); ?>
<div class="app-shell">
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-panel">
        <?php include __DIR__ . '/../partials/header.php'; ?>

        <div class="content">
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

                        </button>

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

                        </div>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <!-- PAGINATION -->

    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">

        <div class="text-muted">

            Showing <span data-user-results><?= count($pageData) ?></span> of <?= count($filteredUsers) ?> employees

        </div>

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

    </div>

</div>

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

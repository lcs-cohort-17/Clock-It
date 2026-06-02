<section class="page-stack" x-data="userManager()">
    <section class="surface">
        <div class="surface-header">
            <div>
                <h2>Employee Directory</h2>
                <p class="surface-subtitle">Manage staff, admins, account state, and employee profile data.</p>
            </div>
            <button class="btn btn-primary" type="button" x-on:click="openAdd()">
                <?= ui_icon('plus') ?>
                Add user
            </button>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <label class="form-label" for="user-search">Search</label>
                <input id="user-search" class="form-control" type="search" placeholder="Name, email, department..." x-model="search">
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label" for="role-filter">Role</label>
                <select id="role-filter" class="form-select" x-model="roleFilter">
                    <option value="">All roles</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <label class="form-label" for="user-status-filter">Status</label>
                <select id="user-status-filter" class="form-select" x-model="statusFilter">
                    <option value="">All statuses</option>
                    <option>Active</option>
                    <option>Disabled</option>
                </select>
            </div>
        </div>
    </section>

    <section class="surface">
        <div class="table-shell">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Attendance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="person in filteredUsers" :key="person.id">
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm" x-text="person.avatar"></span>
                                    <div>
                                        <strong x-text="person.name"></strong>
                                        <div class="table-muted" x-text="`${person.employeeId} / ${person.email}`"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span x-text="person.department"></span>
                                <div class="table-muted" x-text="person.jobTitle"></div>
                            </td>
                            <td><span class="badge-soft violet" x-text="person.role"></span></td>
                            <td><span :class="statusClass(employeeStatus(person.employeeId))" x-text="employeeStatus(person.employeeId)"></span></td>
                            <td><span :class="statusClass(person.status)" x-text="person.status"></span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm btn-outline-light" type="button" x-on:click="openDetails(person)"><?= ui_icon('eye') ?></button>
                                    <button class="btn btn-sm btn-outline-light" type="button" x-on:click="openEdit(person)"><?= ui_icon('edit') ?></button>
                                    <button class="btn btn-sm" type="button" :class="person.status === 'Active' ? 'btn-danger' : 'btn-outline-light'" x-on:click="toggleStatus(person)" x-text="person.status === 'Active' ? 'Disable' : 'Activate'"></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>

    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="mode === 'add' ? 'Add user' : 'Edit user'"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Name</label>
                            <input class="form-control" x-model="form.name" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" x-model="form.email" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Employee ID</label>
                            <input class="form-control" x-model="form.employeeId">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Account role</label>
                            <select class="form-select" x-model="form.accountRole">
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" x-model="form.status">
                                <option>Active</option>
                                <option>Disabled</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Department</label>
                            <input class="form-control" x-model="form.department" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Job title</label>
                            <input class="form-control" x-model="form.jobTitle">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone</label>
                            <input class="form-control" x-model="form.phone">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Location</label>
                            <input class="form-control" x-model="form.location">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Manager</label>
                            <input class="form-control" x-model="form.manager">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Start date</label>
                            <input class="form-control" type="date" x-model="form.startDate">
                        </div>
                        <template x-if="error">
                            <div class="col-12">
                                <div class="invalid-note" x-text="error"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-light" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="button" x-on:click="saveUser()"><?= ui_icon('check') ?> Save user</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" x-if="selected">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="selected.name"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="metadata-grid">
                        <div class="metadata-item"><span>Employee ID</span><strong x-text="selected.employeeId"></strong></div>
                        <div class="metadata-item"><span>Department</span><strong x-text="selected.department"></strong></div>
                        <div class="metadata-item"><span>Email</span><strong x-text="selected.email"></strong></div>
                        <div class="metadata-item"><span>Phone</span><strong x-text="selected.phone"></strong></div>
                        <div class="metadata-item"><span>Location</span><strong x-text="selected.location"></strong></div>
                        <div class="metadata-item"><span>Start date</span><strong x-text="formatDate(selected.startDate)"></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

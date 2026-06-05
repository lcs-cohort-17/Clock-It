function userManager() {
  return {

    // ── State ────────────────────────────────────────────────
    users: [],
    loading: false,
    error: '',
    success: '',

    // Add modal
    showAddModal: false,
    addName: '',
    addEmail: '',
    addRole: 'staff',
    addEmployeeId: '',
    addError: '',
    addLoading: false,
    generatedPassword: '',

    // Edit modal
    showEditModal: false,
    editId: '',
    editName: '',
    editEmail: '',
    editRole: 'staff',
    editEmployeeId: '',
    editError: '',
    editLoading: false,

    // Reset password modal
    showResetModal: false,
    resetUserId: '',
    resetUserName: '',
    resetPassword: '',
    resetLoading: false,
    resetError: '',

    // Search
    search: '',

    // ── Computed ─────────────────────────────────────────────
    get filteredUsers() {
      const term = this.search.toLowerCase().trim();
      if (!term) return this.users;
      return this.users.filter((u) =>
        (u.name || '').toLowerCase().includes(term) ||
        (u.email || '').toLowerCase().includes(term) ||
        (u.employeeId || '').toLowerCase().includes(term)
      );
    },

    // ── Bootstrap ────────────────────────────────────────────
    async init() {
      await this.loadUsers();
    },

    // ── Load all users ────────────────────────────────────────
    async loadUsers() {
      this.loading = true;
      this.error = '';
      try {
        const res = await fetch('/api/admin/users', {
          headers: { Accept: 'application/json' },
        });
        const payload = await res.json();
        if (!payload.success) throw new Error(payload.message || 'Failed to load users.');
        this.users = payload.data || [];
      } catch (e) {
        this.error = e.message || 'Failed to load users.';
      } finally {
        this.loading = false;
      }
    },

    // ── Add user ──────────────────────────────────────────────
    openAddModal() {
      this.addName = '';
      this.addEmail = '';
      this.addRole = 'staff';
      this.addEmployeeId = '';
      this.addError = '';
      this.generatedPassword = '';
      this.showAddModal = true;
    },

    async submitAddUser() {
      if (!this.addName.trim() || !this.addEmail.trim()) {
        this.addError = 'Name and email are required.';
        return;
      }
      this.addLoading = true;
      this.addError = '';
      try {
        const res = await fetch('/api/admin/users', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
          body: JSON.stringify({
            name: this.addName.trim(),
            email: this.addEmail.trim(),
            role: this.addRole,
            employee_id: this.addEmployeeId.trim() || undefined,
          }),
        });
        const payload = await res.json();
        if (!payload.success) throw new Error(payload.message || 'Failed to create user.');
        this.generatedPassword = payload.generated_password || '';
        this.showAddModal = false;
        await this.loadUsers();
        this.success = `User created.${this.generatedPassword ? ' Temp password: ' + this.generatedPassword : ''}`;
        setTimeout(() => { this.success = ''; }, 6000);
      } catch (e) {
        this.addError = e.message || 'Failed to create user.';
      } finally {
        this.addLoading = false;
      }
    },

    // ── Edit user ─────────────────────────────────────────────
    openEditModal(user) {
      this.editId = user.id || user.user_id || '';
      this.editName = user.name || '';
      this.editEmail = user.email || '';
      this.editRole = (user.role_key || user.role || 'staff').toLowerCase();
      this.editEmployeeId = user.employeeId || user.employee_id || '';
      this.editError = '';
      this.showEditModal = true;
    },

    async submitEditUser() {
      if (!this.editName.trim() || !this.editEmail.trim()) {
        this.editError = 'Name and email are required.';
        return;
      }
      this.editLoading = true;
      this.editError = '';
      try {
        const res = await fetch('/api/admin/users/update', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
          body: JSON.stringify({
            id: this.editId,
            name: this.editName.trim(),
            email: this.editEmail.trim(),
            role: this.editRole,
            employee_id: this.editEmployeeId.trim() || undefined,
          }),
        });
        const payload = await res.json();
        if (!payload.success) throw new Error(payload.message || 'Failed to update user.');
        this.showEditModal = false;
        await this.loadUsers();
        this.success = 'User updated.';
        setTimeout(() => { this.success = ''; }, 4000);
      } catch (e) {
        this.editError = e.message || 'Failed to update user.';
      } finally {
        this.editLoading = false;
      }
    },

    // ── Toggle active/inactive ────────────────────────────────
    async toggleStatus(user) {
      try {
        const res = await fetch('/api/admin/users/toggle-status', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
          body: JSON.stringify({ id: user.id || user.user_id }),
        });
        const payload = await res.json();
        if (!payload.success) throw new Error(payload.message || 'Failed to toggle status.');
        await this.loadUsers();
      } catch (e) {
        this.error = e.message || 'Failed to toggle status.';
        setTimeout(() => { this.error = ''; }, 4000);
      }
    },

    // ── Reset password ────────────────────────────────────────
    openResetModal(user) {
      this.resetUserId = user.id || user.user_id || '';
      this.resetUserName = user.name || '';
      this.resetPassword = '';
      this.resetError = '';
      this.showResetModal = true;
    },

    async submitResetPassword() {
      this.resetLoading = true;
      this.resetError = '';
      try {
        const res = await fetch('/api/admin/users/reset-password', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
          body: JSON.stringify({ id: this.resetUserId }),
        });
        const payload = await res.json();
        if (!payload.success) throw new Error(payload.message || 'Failed to reset password.');
        this.resetPassword = payload.generated_password || '';
        setTimeout(() => {
          this.showResetModal = false;
          this.resetPassword = '';
        }, 8000);
      } catch (e) {
        this.resetError = e.message || 'Failed to reset password.';
      } finally {
        this.resetLoading = false;
      }
    },
  };
}
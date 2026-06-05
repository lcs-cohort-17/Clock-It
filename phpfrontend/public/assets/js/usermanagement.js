function registerUserManager() {
    Alpine.data('userManager', () => ({
        allUsers: [],
        users: [],
        loading: true,
        saving: false,

        showAddModal: false,
        showEditModal: false,
        showPasswordModal: false,

        successMessage: '',
        errorMessage: '',

        generatedPassword: '',
        passwordResetUser: null,

        searchQuery: '',

        newUser: {
            first_name: '',
            last_name: '',
            email: '',
            role: 'staff'
        },

        editUser: {
            employee_id: '',
            first_name: '',
            last_name: '',
            email: '',
            role: 'staff',
            _original: null
        },

        async init() {
            this.loading = true
            try {
                await this.fetchUsers()
            } catch (err) {
                this.errorMessage = 'Failed to load users'
            } finally {
                this.loading = false
            }
        },

        // =========================
        // FETCH USERS
        // =========================
        async fetchUsers() {
            this.loading = true
            try {
                await Alpine.store('app').fetchAllUsers()

                this.allUsers = Alpine.store('app').users.map(u => {
                    const firstName = u.first_name || u.firstName || ''
                    const lastName = u.last_name || u.lastName || ''
                    const name = u.name || `${firstName} ${lastName}`.trim() || 'Unnamed User'
                    const role = String(u.role || 'staff').toLowerCase()
                    const isActive = u.is_active ?? u.isActive ?? u.active ?? 1

                    return {
                        id: u.user_id || u.userId || u.id,
                        name,
                        email: u.email || '',
                        employeeId: u.employee_id || u.employeeId || u.employeeID || '',
                        role: role === 'admin' ? 'Admin' : 'Staff',
                        status: isActive == 1 || isActive === true ? 'Active' : 'Inactive',
                        _original: u
                    }
                })

                this.users = [...this.allUsers]

            } catch (err) {
                this.errorMessage = err.message || 'Failed to fetch users'
                this.autoClear()
            } finally {
                this.loading = false
            }
        },

        // =========================
        // SEARCH
        // =========================
        filterUsers() {
            const q = this.searchQuery.toLowerCase().trim()
            if (!q) {
                this.users = [...this.allUsers]
                return
            }

            this.users = this.allUsers.filter(u =>
                u.name.toLowerCase().includes(q) ||
                u.email.toLowerCase().includes(q) ||
                String(u.employeeId).toLowerCase().includes(q)
            )
        },

        onSearchInput() { this.filterUsers() },
        clearSearch() {
            this.searchQuery = ''
            this.users = [...this.allUsers]
        },

        // =========================
        // ADD USER
        // =========================
        openAddModal() {
            this.newUser = {
                first_name: '',
                last_name: '',
                email: '',
                role: 'staff'
            }
            this.showAddModal = true
        },

        async addUser() {
            this.saving = true
            try {
                const res = await Alpine.store('app').createUser(this.newUser)

                this.generatedPassword = res.password || 'Generated on server'

                this.successMessage = `User created! Temporary password: ${this.generatedPassword}`

                this.showAddModal = false
                await this.fetchUsers()

                this.autoClear()

            } catch (err) {
                this.errorMessage = err.message || 'Failed to create user'
                this.autoClear()
            } finally {
                this.saving = false
            }
        },

        // =========================
        // EDIT USER
        // =========================
        openEditModal(user) {
            const o = user._original || {}

            this.editUser = {
                employee_id: user.employeeId,
                first_name: o.first_name || '',
                last_name: o.last_name || '',
                email: user.email,
                role: user.role === 'Admin' ? 'admin' : 'staff',
                _original: o
            }

            this.showEditModal = true
        },

        async updateUser() {
            this.saving = true
            try {
                await Alpine.store('app').updateUser(this.editUser.employee_id, {
                    first_name: this.editUser.first_name,
                    last_name: this.editUser.last_name,
                    email: this.editUser.email,
                    role: this.editUser.role
                })

                this.successMessage = 'User updated successfully'
                this.showEditModal = false

                await this.fetchUsers()
                this.autoClear()

            } catch (err) {
                this.errorMessage = err.message || 'Update failed'
                this.autoClear()
            } finally {
                this.saving = false
            }
        },

        // =========================
        // TOGGLE STATUS
        // =========================
        async toggleStatus(user) {
            try {
                if (user.status === 'Active') {
                    await Alpine.store('app').deactivateUser(user.employeeId)
                } else {
                    await Alpine.store('app').activateUser(user.employeeId)
                }

                this.successMessage =
                    user.status === 'Active'
                        ? `${user.name} deactivated`
                        : `${user.name} activated`

                await this.fetchUsers()
                this.autoClear()

            } catch (err) {
                this.errorMessage = err.message || 'Status update failed'
                this.autoClear()
            }
        },

        // =========================
        // RESET PASSWORD
        // =========================
        async resetPassword(user) {
            if (!confirm(`Reset password for ${user.name}?`)) return

            try {
                const res = await Alpine.store('app').resetUserPassword(user.employeeId)

                this.generatedPassword = res.password || res.temporary_password || res.generated_password || ''
                if (!this.generatedPassword) {
                    throw new Error('Password reset succeeded but no temporary password was returned')
                }

                this.passwordResetUser = {
                    name: user.name,
                    email: user.email
                }

                this.successMessage = ''
                this.errorMessage = ''
                this.showPasswordModal = true

            } catch (err) {
                this.errorMessage = err.message || 'Reset failed'
                this.autoClear()
            }
        },

        closePasswordModal() {
            this.showPasswordModal = false
            this.generatedPassword = ''
            this.passwordResetUser = null
        },

        async copyPassword() {
            try {
                await navigator.clipboard.writeText(this.generatedPassword)
            } catch {
                this.errorMessage = 'Copy failed'
                this.autoClear()
            }
        },

        // =========================
        // EMAIL PASSWORD
        // =========================
        sendPasswordEmail() {
            if (!this.passwordResetUser?.email || !this.generatedPassword) return

            const subject = 'Your temporary Clock-It password'
            const body = [
                `Hi ${this.passwordResetUser.name},`,
                '',
                'Your Clock-It password has been reset.',
                `Temporary password: ${this.generatedPassword}`,
                '',
                'Please sign in and change it as soon as possible.'
            ].join('\n')

            window.location.href = `mailto:${encodeURIComponent(this.passwordResetUser.email)}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
            this.successMessage = 'Email draft opened'
            this.autoClear()
        },

        // =========================
        // UTIL
        // =========================
        getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase()
        },

        autoClear() {
            clearTimeout(this._t)
            this._t = setTimeout(() => {
                this.successMessage = ''
                this.errorMessage = ''
            }, 4000)
        }
    }))
}

if (typeof Alpine !== 'undefined') {
    registerUserManager()
} else {
    document.addEventListener('alpine:init', registerUserManager)
}

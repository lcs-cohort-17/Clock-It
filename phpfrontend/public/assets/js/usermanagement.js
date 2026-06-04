document.addEventListener('DOMContentLoaded', function() {
    if (typeof Alpine === 'undefined') return
    
    Alpine.data('userManager', () => ({
        allUsers: [],
        users: [],
        loading: true,
        saving: false,
        showAddModal: false,
        showEditModal: false,
        successMessage: '',
        errorMessage: '',
        generatedPassword: '',
        searchQuery: '',
        
        newUser: {
            first_name: '',
            last_name: '',
            employee_id: '',
            email: '',
            role: 'staff'
        },
        
        editUser: {
            employee_id: '',
            first_name: '',
            last_name: '',
            email: '',
            role: 'staff'
        },
        
        async init() {
            if (!Alpine.store('app')) {
                this.errorMessage = 'Application not ready. Please refresh.'
                this.loading = false
                return
            }
            
            await Alpine.store('app').init()
            
            if (!Alpine.store('app').isAuthenticated) {
                window.location.href = '/login'
                return
            }
            await this.fetchUsers()
        },
        
        async fetchUsers() {
            this.loading = true
            try {
                await Alpine.store('app').fetchAllUsers()
                this.allUsers = Alpine.store('app').users.map(u => ({
                    id: u.user_id,
                    name: `${u.first_name || ''} ${u.last_name || ''}`.trim(),
                    email: u.email,
                    employeeId: u.employee_id,
                    role: u.role === 'admin' ? 'Admin' : 'Staff',
                    status: u.is_active == 1 ? 'Active' : 'Inactive',
                    _original: u
                }))
                this.filterUsers()
            } catch (error) {
                this.errorMessage = 'Failed to load users: ' + error.message
            } finally {
                this.loading = false
            }
        },
        
        filterUsers() {
            const query = this.searchQuery.toLowerCase().trim()
            if (!query) {
                this.users = [...this.allUsers]
                return
            }
            this.users = this.allUsers.filter(user => {
                return user.name.toLowerCase().includes(query)
                    || user.email.toLowerCase().includes(query)
                    || user.employeeId.toLowerCase().includes(query)
            })
        },
        
        onSearchInput() { this.filterUsers() },
        onSearchSubmit() { this.filterUsers() },
        
        clearSearch() {
            this.searchQuery = ''
            this.filterUsers()
        },
        
        getInitials(name) {
            return name.split(' ').map(w => w[0] || '').join('').toUpperCase() || '?'
        },
        
        openAddModal() {
            this.newUser = { first_name: '', last_name: '', employee_id: '', email: '', role: 'staff' }
            this.generatedPassword = ''
            this.errorMessage = ''
            this.showAddModal = true
        },
        
        async addUser() {
            this.saving = true
            this.errorMessage = ''
            try {
                const result = await Alpine.store('app').createUser({
                    first_name: this.newUser.first_name,
                    last_name: this.newUser.last_name,
                    employee_id: this.newUser.employee_id || undefined,
                    email: this.newUser.email,
                    role: this.newUser.role
                })
                this.generatedPassword = result.password || 'Check server response'
                this.successMessage = 'User created successfully!'
                this.showAddModal = false
                await this.fetchUsers()
                setTimeout(() => this.successMessage = '', 5000)
            } catch (error) {
                this.errorMessage = error.message || 'Failed to create user'
            } finally {
                this.saving = false
            }
        },
        
        openEditModal(user) {
            this.editUser = {
                employee_id: user.employeeId,
                first_name: user.name.split(' ')[0] || '',
                last_name: user.name.split(' ').slice(1).join(' ') || '',
                email: user.email,
                role: user.role === 'Admin' ? 'admin' : 'staff'
            }
            this.errorMessage = ''
            this.showEditModal = true
        },
        
        async updateUser() {
            this.saving = true
            this.errorMessage = ''
            try {
                await Alpine.store('app').updateUser(this.editUser.employee_id, {
                    first_name: this.editUser.first_name,
                    last_name: this.editUser.last_name,
                    email: this.editUser.email,
                    role: this.editUser.role
                })
                this.successMessage = 'User updated successfully!'
                this.showEditModal = false
                await this.fetchUsers()
                setTimeout(() => this.successMessage = '', 3000)
            } catch (error) {
                this.errorMessage = error.message || 'Failed to update user'
            } finally {
                this.saving = false
            }
        },
        
        async toggleStatus(user) {
            try {
                if (user.status === 'Active') {
                    await Alpine.store('app').deactivateUser(user.employeeId)
                    this.successMessage = `${user.name} has been deactivated`
                } else {
                    await Alpine.store('app').activateUser(user.employeeId)
                    this.successMessage = `${user.name} has been activated`
                }
                await this.fetchUsers()
                setTimeout(() => this.successMessage = '', 3000)
            } catch (error) {
                this.errorMessage = error.message || 'Failed to update user status'
            }
        },
        
        async resetPassword(user) {
            if (!confirm(`Reset password for ${user.name}?`)) return
            try {
                // Try the API call
                const response = await api.patch(`/api/admin/users/${user.employeeId}/reset-password`)
                this.successMessage = 'Password reset successfully!'
                setTimeout(() => this.successMessage = '', 3000)
            } catch (error) {
                // If it fails, show the message anyway (might be a backend issue)
                console.error('Reset password error:', error)
                this.errorMessage = 'Password reset failed. Backend route may need fixing.'
                setTimeout(() => this.errorMessage = '', 3000)
            }
        }
    }))
    
    // Initialize Alpine on the element
    const el = document.getElementById('userManagementApp')
    if (el) {
        el.setAttribute('x-data', 'userManager()')
        el.setAttribute('x-init', 'init()')
        Alpine.initTree(el)
    }
})
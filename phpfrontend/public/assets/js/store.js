// Register store IMMEDIATELY, not inside alpine:init
Alpine.store('app', {
    // =============================================
    // STATE
    // =============================================
    user: null,
    token: localStorage.getItem('token') || null,
    users: [],
    loading: false,
    error: null,
    successMessage: null,
    
    // =============================================
    // GETTERS
    // =============================================
    get isAuthenticated() {
        return !!this.token && !!this.user
    },
    
    get isAdmin() {
        return this.user?.role === 'admin'
    },
    
    get userName() {
        return this.user?.first_name || 'User'
    },
    
    get userEmail() {
        return this.user?.email || ''
    },
    
    get employeeId() {
        return this.user?.employee_id || ''
    },
    
    get activeUsers() {
        return this.users.filter(u => u.is_active == 1)
    },
    
    get inactiveUsers() {
        return this.users.filter(u => u.is_active == 0)
    },
    
    get adminUsers() {
        return this.users.filter(u => u.role === 'admin')
    },
    
    get staffUsers() {
        return this.users.filter(u => u.role === 'staff')
    },
    
    // =============================================
    // INIT
    // =============================================
    async init() {
        if (this.token && !this.user) {
            try {
                await this.fetchUserProfile()
            } catch (error) {
                console.log('Session expired')
                this.clearAuth()
            }
        }
    },
    
    // =============================================
    // AUTH ACTIONS
    // =============================================
    async login(email, password) {
        this.loading = true
        this.error = null
        
        try {
            const response = await api.post('/api/login', { email, password })
            console.log('Login response:', response)
            this.token = response.token
            this.user = response.user
            localStorage.setItem('token', response.token)
            return this.user
        } catch (error) {
            this.error = error.message || 'Invalid email or password'
            throw error
        } finally {
            this.loading = false
        }
    },
    
    async fetchUserProfile() {
        try {
            const response = await api.get('/api/user/profile')
            this.user = response.data || response.user || response
            return this.user
        } catch (error) {
            this.clearAuth()
            throw error
        }
    },
    
    clearAuth() {
        this.user = null
        this.token = null
        localStorage.removeItem('token')
    },
    
    logout() {
        this.clearAuth()
        window.location.href = '/login'
    },
    
    // =============================================
    // USER MANAGEMENT ACTIONS
    // =============================================
    async fetchAllUsers() {
        this.loading = true
        this.error = null
        try {
            const response = await api.get('/api/admin/users')
            console.log('Users response:', response)
            this.users = response.data || response.users || []
            return this.users
        } catch (error) {
            this.error = error.message || 'Failed to fetch users'
            throw error
        } finally {
            this.loading = false
        }
    },
    
    async createUser(userData) {
        this.loading = true
        this.error = null
        try {
            const response = await api.post('/api/admin/users', {
                first_name: userData.first_name,
                last_name: userData.last_name,
                employee_id: userData.employee_id,
                role: userData.role,
                email: userData.email,
                img: userData.img || null
            })
            console.log('Create user response:', response)
            const newUser = response.data || response
            this.users.push(newUser)
            return newUser
        } catch (error) {
            this.error = error.message || 'Failed to create user'
            throw error
        } finally {
            this.loading = false
        }
    },
    
    async updateUser(employeeId, updates) {
        this.loading = true
        this.error = null
        try {
            const response = await api.patch(`/api/admin/users/${employeeId}`, updates)
            console.log('Update user response:', response)
            const updatedUser = response.data || response
            const index = this.users.findIndex(u => u.employee_id === employeeId)
            if (index !== -1) {
                this.users[index] = { ...this.users[index], ...updatedUser }
            }
            return updatedUser
        } catch (error) {
            this.error = error.message || 'Failed to update user'
            throw error
        } finally {
            this.loading = false
        }
    },
    
    async deactivateUser(employeeId) {
        this.loading = true
        this.error = null
        try {
            const response = await api.patch(`/api/admin/users/${employeeId}/deactivate`)
            console.log('Deactivate response:', response)
            const index = this.users.findIndex(u => u.employee_id === employeeId)
            if (index !== -1) {
                this.users[index].is_active = 0
            }
            return response
        } catch (error) {
            this.error = error.message || 'Failed to deactivate user'
            throw error
        } finally {
            this.loading = false
        }
    },
    
    async activateUser(employeeId) {
        this.loading = true
        this.error = null
        try {
            const response = await api.patch(`/api/admin/users/${employeeId}/activate`)
            console.log('Activate response:', response)
            const index = this.users.findIndex(u => u.employee_id === employeeId)
            if (index !== -1) {
                this.users[index].is_active = 1
            }
            return response
        } catch (error) {
            this.error = error.message || 'Failed to activate user'
            throw error
        } finally {
            this.loading = false
        }
    }
})
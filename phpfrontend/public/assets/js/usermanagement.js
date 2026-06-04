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
<<<<<<< HEAD

}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-auto-dismiss-alert]').forEach(function (alert) {
        window.setTimeout(function () {
            alert.classList.add('user-alert-hiding');
            window.setTimeout(function () {
                alert.remove();
            }, 250);
        }, 2800);
    });

    var userModals = Array.prototype.slice.call(document.querySelectorAll('[data-user-modal]'));

    function closeUserModals() {
        userModals.forEach(function (modal) {
            modal.style.display = 'none';
            modal.setAttribute('x-cloak', '');
        });
    }

    function fillEditModal(user) {
        var modal = document.querySelector('[data-user-modal="edit"]');
        if (!modal || !user) {
            return;
        }

        var idInput = modal.querySelector('input[name="id"]');
        var nameInput = modal.querySelector('input[name="name"]');
        var emailInput = modal.querySelector('input[name="email"]');
        var roleInput = modal.querySelector('select[name="role"]');

        if (idInput) idInput.value = user.id || '';
        if (nameInput) nameInput.value = user.name || '';
        if (emailInput) emailInput.value = user.email || '';
        if (roleInput) roleInput.value = user.role || 'Staff';
    }

    function openUserModal(name, user) {
        var modal = document.querySelector('[data-user-modal="' + name + '"]');
        if (!modal) {
            return;
        }

        if (name === 'edit') {
            fillEditModal(user);
        }

        modal.removeAttribute('x-cloak');
        modal.style.display = 'flex';
    }

    closeUserModals();

    document.querySelectorAll('[data-open-user-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            var modalName = button.getAttribute('data-open-user-modal');
            var user = null;

            if (button.dataset.editUser) {
                try {
                    user = JSON.parse(button.dataset.editUser);
                } catch (_) {
                    user = null;
                }
            }

            openUserModal(modalName, user);
        });
    });

    document.querySelectorAll('[data-close-user-modal]').forEach(function (button) {
        button.addEventListener('click', closeUserModals);
    });

    userModals.forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeUserModals();
            }
        });
    });

    document.querySelectorAll('[data-copy-user]').forEach(function (button) {
        button.addEventListener('click', function () {
            var value = button.getAttribute('data-copy-value') || '';
            var originalTitle = button.getAttribute('title') || 'Copy invite details';

            function markCopied() {
                button.setAttribute('title', 'Copied');
                button.setAttribute('aria-label', 'Copied invite details');
                button.classList.add('user-copy-done');
                window.setTimeout(function () {
                    button.setAttribute('title', originalTitle);
                    button.setAttribute('aria-label', originalTitle);
                    button.classList.remove('user-copy-done');
                }, 1400);
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value).then(markCopied).catch(markCopied);
                return;
            }

            markCopied();
        });
    });

    var input = document.querySelector('[data-user-search]');

    if (!input) {
        return;
    }

    var rows = Array.prototype.slice.call(document.querySelectorAll('[data-user-row]'));
    var results = document.querySelector('[data-user-results]');

    function updateUrl(value) {
        if (!window.history || !window.history.replaceState) {
            return;
        }

        var url = new URL(window.location.href);
        if (value.trim() !== '') {
            url.searchParams.set('q', value.trim());
        } else {
            url.searchParams.delete('q');
        }
        url.searchParams.delete('page');
        window.history.replaceState({}, '', url.toString());
    }

    function filterUsers() {
        var value = (input.value || '').toLowerCase().trim();
        var visibleCount = 0;

        rows.forEach(function (row) {
            var haystack = row.getAttribute('data-user-search-value') || '';
            var visible = !value || haystack.indexOf(value) !== -1;
            row.hidden = !visible;
            if (visible) {
                visibleCount += 1;
            }
        });

        if (results) {
            results.textContent = String(visibleCount);
        }
        updateUrl(value);
    }

    if (input.form) {
        input.form.addEventListener('submit', function (event) {
            event.preventDefault();
            filterUsers();
        });
    }

    input.addEventListener('input', filterUsers);
    input.addEventListener('search', filterUsers);

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            filterUsers();
        }
    });

    filterUsers();
});
=======
})
>>>>>>> 579e75d1125dd71394d1c92fa188610e28ed1ede

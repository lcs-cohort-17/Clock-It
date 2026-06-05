// public/assets/js/login.js
(function () {
    var REMEMBERED_EMAIL_KEY = 'clockit.rememberedEmail';
    var FORGOT_SUCCESS_MESSAGE = "If that email exists in our system, we've sent a password reset link.";
    var EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function normalizeEmail(value) {
        return String(value || '').trim().toLowerCase();
    }

    function pageConfig() {
        var page = document.querySelector('[data-login-page]');

        return {
            // Point to REAL backend API
            loginApi: window.APP_CONFIG?.API_BASE_URL + '/api/login' || 'http://localhost:8000/api/login',
            forgotPasswordApi: window.APP_CONFIG?.API_BASE_URL + '/api/forgot-password' || 'http://localhost:8000/api/forgot-password',
            adminRoute: page && page.dataset.adminRoute ? page.dataset.adminRoute : '/admin-dashboard',
            staffRoute: page && page.dataset.staffRoute ? page.dataset.staffRoute : '/staff-dashboard'
        };
    }

    async function postJson(url, payload) {
        var token = localStorage.getItem('token');
        var headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }
        
        var response = await fetch(url, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload)
        });

        var data = {};
        try {
            data = await response.json();
        } catch (error) {
            data = {};
        }

        if (!response.ok) {
            throw new Error(data.error || data.message || 'Request failed.');
        }

        return data;
    }

    window.clockitLogin = function () {
        return {
            loginMethod: 'email',
            email: '',
            password: '',
            employeeId: '',
            showPassword: false,
            rememberMe: false,
            loading: false,
            errorMessage: '',
            forgotEmail: '',
            forgotError: '',
            forgotLoading: false,
            forgotMessage: '',
            validation: {
                email: '',
                password: '',
                employeeId: ''
            },

            init: function () {
                try {
                    var rememberedEmail = window.localStorage.getItem(REMEMBERED_EMAIL_KEY);
                    if (rememberedEmail) {
                        this.email = rememberedEmail;
                        this.rememberMe = true;
                    }
                } catch (error) {
                    this.rememberMe = false;
                }

                this.forgotEmail = this.email;
            },

            isValidEmail: function (value) {
                return EMAIL_PATTERN.test(normalizeEmail(value));
            },

            setLoginMethod: function (method) {
                this.loginMethod = method;
                this.errorMessage = '';
                this.validation.email = '';
                this.validation.password = '';
                this.validation.employeeId = '';
            },

            togglePassword: function () {
                this.showPassword = !this.showPassword;
            },

            validateLogin: function () {
                this.validation.email = '';
                this.validation.password = '';
                this.validation.employeeId = '';
                this.errorMessage = '';

                if (this.loginMethod === 'email') {
                    if (!normalizeEmail(this.email)) {
                        this.validation.email = 'Email is required.';
                    } else if (!this.isValidEmail(this.email)) {
                        this.validation.email = 'Enter a valid email address.';
                    }

                    if (!String(this.password || '').trim()) {
                        this.validation.password = 'Password is required.';
                    }

                    if (this.validation.email || this.validation.password) {
                        this.errorMessage = 'Please fill in all required fields.';
                        return false;
                    }
                }

                return true;
            },

            persistRememberedEmail: function () {
                try {
                    if (this.rememberMe) {
                        window.localStorage.setItem(REMEMBERED_EMAIL_KEY, normalizeEmail(this.email));
                    } else {
                        window.localStorage.removeItem(REMEMBERED_EMAIL_KEY);
                    }
                } catch (error) {
                    // Silently fail
                }
            },

            handleRememberMeChange: function () {
                if (!this.rememberMe) {
                    this.persistRememberedEmail();
                    return;
                }

                if (this.isValidEmail(this.email)) {
                    this.persistRememberedEmail();
                }
            },

            redirectForUser: function (user) {
                var role = String((user && user.role) || 'staff').toLowerCase();
                var config = pageConfig();
                var target = role === 'admin' ? config.adminRoute : config.staffRoute;
                window.location.assign(target);
            },

            submitLogin: async function () {
                if (!this.validateLogin()) {
                    return;
                }

                this.loading = true;
                this.errorMessage = '';

                try {
                    var config = pageConfig();
                    
                    // Call REAL backend
                    var result = await postJson(config.loginApi, {
                        email: normalizeEmail(this.email),
                        password: this.password
                    });

                    console.log('Login success:', result);
                    
                    // Store token and user using our Alpine store if available
                    if (window.Alpine && Alpine.store('app')) {
                        Alpine.store('app').token = result.token;
                        Alpine.store('app').user = result.user;
                    }
                    
                    // Always save token to localStorage
                    localStorage.setItem('token', result.token);
                    this.persistRememberedEmail();
                    
                    // Redirect
                    this.redirectForUser(result.user || { role: result.user?.role });
                    
                } catch (error) {
                    console.error('Login error:', error);
                    this.errorMessage = error.message || 'Invalid email or password.';
                    this.loading = false;
                }
            },

            prefillForgotEmail: function () {
                this.forgotError = '';
                this.forgotEmail = this.forgotEmail || this.email;
            },

            validateForgotPassword: function () {
                this.forgotError = '';

                if (!normalizeEmail(this.forgotEmail)) {
                    this.forgotError = 'Email is required.';
                    return false;
                }

                if (!this.isValidEmail(this.forgotEmail)) {
                    this.forgotError = 'Enter a valid email address.';
                    return false;
                }

                return true;
            },

            hideForgotPasswordModal: function () {
                var modalEl = document.getElementById('forgotPasswordModal');

                if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                    var modal = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                    modal.hide();
                }
            },

            submitForgotPassword: async function () {
                if (!this.validateForgotPassword()) {
                    return;
                }

                this.forgotLoading = true;
                this.forgotMessage = '';

                try {
                    await postJson(pageConfig().forgotPasswordApi, {
                        email: normalizeEmail(this.forgotEmail)
                    });
                } catch (error) {
                    // Show same message regardless
                }

                this.forgotLoading = false;
                this.forgotMessage = FORGOT_SUCCESS_MESSAGE;
                this.hideForgotPasswordModal();
            },

            socialLogin: async function (provider) {
                // Keep as is or remove
                alert('Social login not implemented yet');
            }
        };
    };

    window.clockitApp = window.clockitLogin;
}());
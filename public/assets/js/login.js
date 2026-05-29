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
            loginApi: page && page.dataset.loginApi ? page.dataset.loginApi : '/api/login',
            forgotPasswordApi: page && page.dataset.forgotPasswordApi ? page.dataset.forgotPasswordApi : '/api/forgot-password',
            adminRoute: page && page.dataset.adminRoute ? page.dataset.adminRoute : '/admin-dashboard',
            staffRoute: page && page.dataset.staffRoute ? page.dataset.staffRoute : '/staff-dashboard'
        };
    }

    async function postJson(url, payload) {
        var response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        var data = {};
        try {
            data = await response.json();
        } catch (error) {
            data = {};
        }

        if (!response.ok) {
            throw new Error(data.message || 'Request failed.');
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
                // Restore the remembered email only on this browser; passwords are never stored.
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
                window.clockitAppReady = true;
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
                        this.errorMessage = this.validation.email && this.validation.password
                            ? 'Email and password are required.'
                            : (this.validation.email || this.validation.password);
                        return false;
                    }
                }

                if (this.loginMethod === 'employeeId' && !String(this.employeeId || '').trim()) {
                    this.validation.employeeId = 'Employee ID is required.';
                    this.errorMessage = this.validation.employeeId;
                    return false;
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
                    // Private browsing or locked storage should not block sign in.
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

            findMockUser: function () {
                var users = window.clockItMockUsers || [];
                var email = normalizeEmail(this.email);
                var employeeId = String(this.employeeId || '').trim().toLowerCase();

                for (var i = 0; i < users.length; i += 1) {
                    var user = users[i];
                    if (this.loginMethod === 'email' && normalizeEmail(user.email) === email && String(user.password || '') === String(this.password || '')) {
                        return user;
                    }

                    if (this.loginMethod === 'employeeId' && String(user.employeeId || '').trim().toLowerCase() === employeeId) {
                        return user;
                    }
                }

                return null;
            },

            redirectForUser: function (user, fallbackRedirect) {
                var config = pageConfig();
                var role = String((user && user.role) || '').toLowerCase();
                var target = fallbackRedirect || (role === 'admin' ? config.adminRoute : config.staffRoute);

                window.location.assign(target);
            },

            submitLogin: async function () {
                if (!this.validateLogin()) {
                    return;
                }

                this.loading = true;
                this.forgotMessage = '';

                try {
                    var config = pageConfig();
                    var result = await postJson(config.loginApi, {
                        loginMethod: this.loginMethod,
                        email: normalizeEmail(this.email),
                        password: this.password,
                        employeeId: String(this.employeeId || '').trim(),
                        rememberMe: this.rememberMe
                    });

                    this.persistRememberedEmail();
                    this.redirectForUser({ role: result.role }, result.redirect);
                } catch (error) {
                    // Static-file demos still work if the placeholder API is unavailable.
                    var fallbackUser = this.findMockUser();
                    if (fallbackUser) {
                        this.persistRememberedEmail();
                        this.redirectForUser(fallbackUser);
                        return;
                    }

                    this.errorMessage = 'Invalid email or password.';
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
                    // The user sees the same message even if the demo endpoint is missing or the email is unknown.
                }

                this.forgotLoading = false;
                this.forgotMessage = FORGOT_SUCCESS_MESSAGE;
                this.hideForgotPasswordModal();
            },

            socialLogin: async function (provider) {
                // Demo: ask user which account to use for social login (this simulates
                // the provider returning an account for the current device).
                var picked = prompt('Demo social login — enter the email for ' + provider + ' account:');
                if (!picked) return;

                try {
                    var result = await postJson('/api/social-login', { provider: provider, email: normalizeEmail(picked) });
                    this.redirectForUser({ role: result.role }, result.redirect);
                } catch (err) {
                    this.errorMessage = err.message || 'Social login failed.';
                }
            }
        };
    };

    // Backward-compatible alias for older local test pages.
    window.clockitApp = window.clockitLogin;
}());

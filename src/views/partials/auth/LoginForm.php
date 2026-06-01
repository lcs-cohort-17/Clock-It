<section class="login-form-shell" aria-labelledby="loginHeading">
    <div class="login-heading">
        <h2 id="loginHeading">Sign in to Clock It</h2>
        <p>Welcome back! Please enter your details.</p>
    </div>

    <div class="login-tabs" role="tablist" aria-label="Login method">
        <button
            id="emailTab"
            class="login-tab"
            type="button"
            role="tab"
            :class="{ 'is-active': loginMethod === 'email' }"
            :aria-selected="loginMethod === 'email'"
            aria-controls="emailFields"
            @click="setLoginMethod('email')"
        >
            Email
        </button>
        <button
            id="employeeTab"
            class="login-tab"
            type="button"
            role="tab"
            :class="{ 'is-active': loginMethod === 'employeeId' }"
            :aria-selected="loginMethod === 'employeeId'"
            aria-controls="employeeFields"
            @click="setLoginMethod('employeeId')"
        >
            Employee ID
        </button>
    </div>

    <!-- Alpine owns login validation, remember-me storage, and demo API state. -->
    <div x-cloak x-show="forgotMessage" class="alert alert-success" role="alert" x-text="forgotMessage"></div>
    <div x-cloak x-show="errorMessage" id="loginError" class="login-error alert alert-danger" role="alert" x-text="errorMessage"></div>

    <form id="loginForm" class="login-form" method="post" action="<?= htmlspecialchars($loginRoute ?? clockit_route('/login'), ENT_QUOTES, 'UTF-8') ?>" novalidate @submit.prevent="submitLogin">
        <div id="emailFields" class="login-panel" role="tabpanel" aria-labelledby="emailTab" :hidden="loginMethod !== 'email'">
            <div class="form-field">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <?= clockit_icon('mail', 'icon input-icon') ?>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="form-control form-control-lg"
                        :class="{ 'is-invalid': validation.email }"
                        autocomplete="email"
                        placeholder="you@example.com"
                        x-model.trim="email"
                        @input="validation.email = ''"
                        :disabled="loginMethod !== 'email'"
                        required
                    >
                </div>
                <div class="invalid-feedback d-block" x-cloak x-show="validation.email" x-text="validation.email"></div>
            </div>

            <div class="form-field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <?= clockit_icon('lock', 'icon input-icon') ?>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="form-control form-control-lg"
                        :class="{ 'is-invalid': validation.password }"
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        x-model="password"
                        @input="validation.password = ''"
                        :disabled="loginMethod !== 'email'"
                        required
                    >
                    <button
                        id="togglePassword"
                        class="password-toggle"
                        type="button"
                        @click="togglePassword"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                        :aria-pressed="String(showPassword)"
                    >
                        <span x-show="!showPassword"><?= clockit_icon('eye', 'icon icon-button') ?></span>
                        <span x-cloak x-show="showPassword"><?= clockit_icon('eye-off', 'icon icon-button') ?></span>
                    </button>
                </div>
                <div class="invalid-feedback d-block" x-cloak x-show="validation.password" x-text="validation.password"></div>
            </div>

            <div class="form-row">
                <label class="checkbox-label" for="rememberMe">
                    <input
                        id="rememberMe"
                        name="rememberMe"
                        type="checkbox"
                        class="form-check-input"
                        x-model="rememberMe"
                        @change="handleRememberMeChange"
                    >
                    <span>Remember me</span>
                </label>

                <button
                    id="openForgotPassword"
                    class="forgot-button btn btn-link"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#forgotPasswordModal"
                    @click="prefillForgotEmail"
                >
                    Forgot Password?
                </button>
            </div>
        </div>

        <div id="employeeFields" class="login-panel" role="tabpanel" aria-labelledby="employeeTab" :hidden="loginMethod !== 'employeeId'">
            <div class="form-field">
                <label for="employeeId">Employee ID</label>
                <div class="input-wrap">
                    <?= clockit_icon('key', 'icon input-icon') ?>
                    <input
                        id="employeeId"
                        name="employeeId"
                        type="text"
                        class="form-control form-control-lg"
                        :class="{ 'is-invalid': validation.employeeId }"
                        autocomplete="username"
                        placeholder="e.g. EMP001"
                        x-model.trim="employeeId"
                        @input="validation.employeeId = ''"
                        :disabled="loginMethod !== 'employeeId'"
                    >
                </div>
                <div class="invalid-feedback d-block" x-cloak x-show="validation.employeeId" x-text="validation.employeeId"></div>
            </div>
        </div>

        <button id="submitLogin" class="submit-button btn btn-primary btn-lg w-100" type="submit" :disabled="loading">
            <span class="button-label" x-show="!loading">Sign In</span>
            <span class="loading-label" x-cloak x-show="loading">
                <span class="spinner" aria-hidden="true"></span>
                Signing in...
            </span>
        </button>
    </form>

    <div class="alternate-login">
        <div class="divider" aria-hidden="true">
            <span></span>
            <p>Or continue with</p>
            <span></span>
        </div>

        <div class="social-grid">
            <button id="microsoftLogin" class="social-button" type="button" @click="socialLogin('microsoft')">
                <svg class="brand-icon" viewBox="0 0 21 21" fill="none" aria-hidden="true">
                    <rect x="1" y="1" width="9" height="9" fill="#F25022"></rect>
                    <rect x="11" y="1" width="9" height="9" fill="#7FBA00"></rect>
                    <rect x="1" y="11" width="9" height="9" fill="#00A4EF"></rect>
                    <rect x="11" y="11" width="9" height="9" fill="#FFB900"></rect>
                </svg>
                Microsoft
            </button>
            <button id="googleLogin" class="social-button" type="button" @click="socialLogin('google')">
                <?= clockit_icon('user-circle', 'icon brand-icon') ?>
                Google
            </button>
        </div>
    </div>

    <div class="demo-section">
        <?php include __DIR__ . '/DemoAccounts.php'; ?>
    </div>
</section>

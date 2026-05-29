<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="forgotPasswordForm" novalidate @submit.prevent="submitForgotPassword">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="forgotPasswordTitle">Reset password</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-secondary mb-3">
                        Enter your email address and we will send password reset instructions if the account exists.
                    </p>

                    <div class="mb-0">
                        <label for="forgotEmail" class="form-label">Email address</label>
                        <input
                            id="forgotEmail"
                            name="forgotEmail"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': forgotError }"
                            autocomplete="email"
                            placeholder="you@example.com"
                            x-model.trim="forgotEmail"
                            @input="forgotError = ''"
                            required
                        >
                        <div class="invalid-feedback" x-text="forgotError"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="sendResetLink" type="submit" class="btn btn-primary" :disabled="forgotLoading">
                        <span x-show="!forgotLoading">Send Reset Link</span>
                        <span x-cloak x-show="forgotLoading">
                            <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                            Sending...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

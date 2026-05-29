<?php
$title = 'Change Password';
$headContent = '
    <script src="https://cdn.tailwindcss.com"></script>
    <style>[x-cloak] { display: none !important; }</style>
    <script>
        function passwordForm(initialPassword) {
            return {
                newPassword: initialPassword,
                get strength() {
                    if (!this.newPassword) return 0;

                    let score = 0;
                    if (/[A-Z]/.test(this.newPassword)) score++;
                    if (/[a-z]/.test(this.newPassword)) score++;
                    if (/[0-9]/.test(this.newPassword)) score++;
                    if (/[^A-Za-z0-9]/.test(this.newPassword)) score++;
                    if (this.newPassword.length > 7) score++;

                    return score;
                }
            };
        }
    </script>
';

ob_start();
// Initialize state variables for backend messages and inputs
$error = '';
$success = '';
$currentPassword = '';
$newPassword = '';
$confirmPassword = '';

require_once __DIR__ . '/password_logic.php';

// Handle backend form submission validation using shared logic
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $currentPassword = $_POST['current-password'] ?? '';
    $newPassword = $_POST['new-password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';

    $result = validatePasswordChange($currentPassword, $newPassword, $confirmPassword);

    if ($result['success']) {
        $success = $result['message'];
        $currentPassword = '';
        $newPassword = '';
        $confirmPassword = '';
    } else {
        $error = $result['error'];
    }
}

/**
 * Helper function to render a PasswordInput field using Alpine.js
 */
function renderPasswordInput($id, $label, $placeholder, $value, $alpineModel = '') {
    // If we pass an alpine model, we link it up using x-model
    $xModelAttr = $alpineModel ? "x-model=\"$alpineModel\"" : "";
    $safeId = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    
    echo '
    <div x-data="{ show: false }">
        <label for="' . $safeId . '" class="block text-sm font-medium text-gray-700 mb-2">
            ' . htmlspecialchars($label) . '
        </label>

        <div class="relative w-full max-w-sm">
            <input
                id="' . $safeId . '"
                name="' . $safeId . '"
                :type="show ? \'text\' : \'password\'"
                placeholder="' . htmlspecialchars($placeholder) . '"
                value="' . htmlspecialchars($value) . '"
                ' . $xModelAttr . '
                data-testid="' . $safeId . '-input"
                class="
                    w-full rounded-xl border border-gray-300
                    px-3 py-2 pr-10 text-sm text-gray-900
                    placeholder:text-gray-400
                    focus:outline-none focus:ring-2 focus:ring-blue-500
                    [&::-ms-reveal]:hidden
                    [&::-ms-clear]:hidden
                "
            />

            <button
                type="button"
                @click="show = !show"
                :aria-label="show ? \'Hide password\' : \'Show password\'"
                :title="show ? \'Hide password\' : \'Show password\'"
                :aria-pressed="show.toString()"
                aria-controls="' . $safeId . '"
                data-testid="' . $safeId . '-toggle-button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
            >
                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
            </button>
        </div>
    </div>';
}
?>

<main class="bg-gray-50 p-8 flex justify-center items-center min-h-screen">
<section
    class="w-full max-w-xl bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6"
    x-data='passwordForm(<?php echo json_encode($newPassword); ?>)'
>
    <div>
        <h2 id="password-section-heading" class="text-2xl font-semibold text-[#0A3A5A]">
            Change password
        </h2>
        <p class="text-gray-500 mt-1">
            Update your password securely.
        </p>
    </div>

    <form
        method="POST"
        action=""
        class="mt-6 grid gap-4"
        aria-labelledby="password-section-heading"
        data-testid="password-form"
    >
        <?php renderPasswordInput('current-password', 'Current password', 'Enter current password', $currentPassword); ?>

        <div>
            <?php renderPasswordInput('new-password', 'New password', 'Enter new password', $newPassword, 'newPassword'); ?>
            
            <div 
                x-show="newPassword.length > 0" 
                x-cloak
                class="mt-3 max-w-sm" 
                data-testid="password-strength" 
                role="status" 
                aria-live="polite"
            >
                <p 
                    class="text-sm font-medium"
                    :class="{
                        'text-red-500': strength <= 2,
                        'text-yellow-500': strength > 2 && strength <= 4,
                        'text-green-600': strength > 4
                    }"
                    x-text="'Password strength: ' + (strength <= 2 ? 'Weak' : strength <= 4 ? 'Medium' : 'Strong')"
                >
                </p>
            </div>
        </div>

        <?php renderPasswordInput('confirm-password', 'Confirm new password', 'Confirm new password', $confirmPassword); ?>

        <?php if (!empty($error)): ?>
            <p class="text-sm font-medium text-red-500" role="alert" data-testid="password-error">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p class="text-sm font-medium text-green-600" role="status" data-testid="password-success">
                <?php echo htmlspecialchars($success); ?>
            </p>
        <?php endif; ?>

        <button
            type="submit"
            data-testid="password-submit"
            class="inline-flex w-fit rounded-xl bg-[#093B5D] px-5 py-2 text-white font-medium transition hover:bg-[#082F49] self-start"
        >
            Update password
        </button>
    </form>
</section>
</main>

<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>

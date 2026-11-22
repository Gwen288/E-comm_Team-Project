<?php

require_once __DIR__ . '/../core/core.php';

if (is_logged_in()) {
    redirect(cc_home_for_role());
}

$pageTitle = 'Reset your password';
require_once __DIR__ . '/layout/header.php';
?>

<main class="cc-auth">
    <div class="cc-auth-card">
        <a class="cc-back" href="<?php echo BASE_URL; ?>views/login.php" data-back>Back</a>

        <h1>Reset your password</h1>
        <p class="cc-auth-lead">Enter the email address of your account and we will send you a link to choose a new password.</p>

        <form
            class="cc-form"
            action="<?php echo BASE_URL; ?>actions/forgot_password_action.php"
            method="POST"
            data-ajax
            novalidate
        >
            <?php echo cc_csrf_field(); ?>
            <div class="cc-form-alert" role="alert" hidden></div>

            <div class="cc-field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?php echo e(cc_old('email')); ?>" autocomplete="email" required>
            </div>

            <button type="submit" class="cc-button" data-busy="Sending...">Send reset link</button>
        </form>

        <p class="cc-auth-switch">
            Remembered it?
            <a class="cc-link" href="<?php echo BASE_URL; ?>views/login.php">Sign in</a>
        </p>
    </div>
</main>

<?php
cc_forget_old();
require_once __DIR__ . '/layout/footer.php';

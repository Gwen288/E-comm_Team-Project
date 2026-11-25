<?php

require_once __DIR__ . '/../core/core.php';

if (is_logged_in()) {
    redirect(cc_home_for_role());
}

// "Have an account? Log in" on the checkout page brings the customer back to checkout.
if (($_GET['return'] ?? '') === 'checkout') {
    $_SESSION['redirect_after_login'] = BASE_URL . 'views/checkout.php';
}

$pageTitle = 'Sign in';
require_once __DIR__ . '/layout/header.php';
?>

<main class="cc-auth">
    <div class="cc-auth-card">
        <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>

        <h1>Sign in</h1>
        <p class="cc-auth-lead">Sign in to track your orders and manage your consultations.</p>

        <form
            class="cc-form"
            action="<?php echo BASE_URL; ?>actions/login_action.php"
            method="POST"
            data-ajax
            novalidate
        >
            <?php echo cc_csrf_field(); ?>
            <div class="cc-form-alert" role="alert" hidden></div>

            <div class="cc-field">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo e(cc_old('email')); ?>"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="cc-field">
                <label for="pass">Password</label>
                <input type="password" id="pass" name="pass" autocomplete="current-password" required>
            </div>

            <a class="cc-link cc-auth-forgot" href="<?php echo BASE_URL; ?>views/forgot_password.php">Forgot your password?</a>

            <button type="submit" class="cc-button" data-busy="Signing in...">Sign in</button>
        </form>

        <p class="cc-auth-switch">
            New to CrownCare?
            <a class="cc-link" href="<?php echo BASE_URL; ?>views/register.php">Create an account</a>
        </p>
    </div>
</main>

<?php
cc_forget_old();
require_once __DIR__ . '/layout/footer.php';

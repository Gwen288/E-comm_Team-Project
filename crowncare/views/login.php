<?php
// Sign-in page for customers and staff (role decides where they land).

require_once __DIR__ . '/../core/core.php';

// Already signed in? Skip the form.
if (is_logged_in()) {
    redirect(cc_home_for_role());
}

$pageTitle = 'Sign in';
require_once __DIR__ . '/layout/header.php';
?>

<main class="cc-auth">
    <div class="cc-auth-card">
        <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>

        <h1>Sign in</h1>
        <p class="cc-auth-lead">Sign in to check out, track your orders and manage your consultations.</p>

        <?php /* login_action.php checks the password, then cc_sign_in() puts the ID and role in the session. */ ?>
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
                <?php /* Only the email is refilled after a failed attempt, never the password. */ ?>
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

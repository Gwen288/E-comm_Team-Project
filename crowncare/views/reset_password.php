<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

$token = (string) ($_GET['token'] ?? '');
$reset = (new CustomerController())->findPasswordReset($token);

$pageTitle = 'Choose a new password';
require_once __DIR__ . '/layout/header.php';
?>

<main class="cc-auth">
    <div class="cc-auth-card">
        <?php if (!$reset): ?>
            <h1>This link has expired</h1>
            <p class="cc-auth-lead">Password reset links work once, for one hour. Ask for a new link to continue.</p>
            <a class="cc-button cc-button--block" href="<?php echo BASE_URL; ?>views/forgot_password.php">Send a new link</a>
            <p class="cc-auth-switch">
                <a class="cc-link" href="<?php echo BASE_URL; ?>views/login.php">Back to sign in</a>
            </p>
        <?php else: ?>
            <h1>Choose a new password</h1>
            <p class="cc-auth-lead">For the account <?php echo e($reset['customer_email']); ?>.</p>

            <form
                class="cc-form"
                action="<?php echo BASE_URL; ?>actions/reset_password_action.php"
                method="POST"
                data-ajax
                novalidate
            >
                <?php echo cc_csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo e($token); ?>">
                <div class="cc-form-alert" role="alert" hidden></div>

                <div class="cc-field">
                    <label for="pass">New password</label>
                    <input type="password" id="pass" name="pass" autocomplete="new-password" required data-password-rules="reset-rules">
                    <span class="cc-field-error" data-error-for="pass"></span>
                </div>

                <ul class="cc-password-rules" id="reset-rules" aria-label="Password rules">
                    <li data-rule="length">At least 8 characters</li>
                    <li data-rule="upper">An uppercase letter</li>
                    <li data-rule="lower">A lowercase letter</li>
                    <li data-rule="number">A number</li>
                    <li data-rule="symbol">A symbol, such as ! or @</li>
                </ul>

                <div class="cc-field">
                    <label for="pass_confirm">Confirm new password</label>
                    <input type="password" id="pass_confirm" name="pass_confirm" autocomplete="new-password" required data-match="pass">
                    <span class="cc-field-error" data-error-for="pass_confirm"></span>
                </div>

                <button type="submit" class="cc-button" data-busy="Saving...">Save new password</button>
            </form>
        <?php endif; ?>
    </div>
</main>

<script src="<?php echo BASE_URL; ?>js/validate.js?v=20261122"></script>
<?php require_once __DIR__ . '/layout/footer.php'; ?>

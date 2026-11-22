<?php

require_once __DIR__ . '/../core/core.php';

if (is_logged_in()) {
    redirect(cc_home_for_role());
}

$pageTitle = 'Create an account';
require_once __DIR__ . '/layout/header.php';
?>

<main class="cc-auth">
    <div class="cc-auth-card cc-auth-card--wide">
        <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>

        <h1>Create an account</h1>
        <p class="cc-auth-lead">Save your cart, check out faster and keep track of your orders. We deliver across Accra.</p>

        <form
            id="register-form"
            class="cc-form"
            action="<?php echo BASE_URL; ?>actions/register_action.php"
            method="POST"
            data-ajax
            novalidate
        >
            <?php echo cc_csrf_field(); ?>
            <div class="cc-form-alert" role="alert" hidden></div>

            <div class="cc-field">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="<?php echo e(cc_old('name')); ?>" autocomplete="name" maxlength="100" required>
                <span class="cc-field-error" data-error-for="name"></span>
            </div>

            <div class="cc-field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?php echo e(cc_old('email')); ?>" autocomplete="email" maxlength="100" required>
                <span class="cc-field-error" data-error-for="email"></span>
            </div>

            <div class="cc-form-row">
                <div class="cc-field">
                    <label for="pass">Password</label>
                    <input type="password" id="pass" name="pass" autocomplete="new-password" required data-password-rules="register-rules">
                    <span class="cc-field-error" data-error-for="pass"></span>
                </div>

                <div class="cc-field">
                    <label for="pass_confirm">Confirm password</label>
                    <input type="password" id="pass_confirm" name="pass_confirm" autocomplete="new-password" required data-match="pass">
                    <span class="cc-field-error" data-error-for="pass_confirm"></span>
                </div>
            </div>

            <ul class="cc-password-rules" id="register-rules" aria-label="Password rules">
                <li data-rule="length">At least 8 characters</li>
                <li data-rule="upper">An uppercase letter</li>
                <li data-rule="lower">A lowercase letter</li>
                <li data-rule="number">A number</li>
                <li data-rule="symbol">A symbol, such as ! or @</li>
            </ul>

            <div class="cc-form-row">
                <div class="cc-field">
                    <label for="contact">Phone number</label>
                    <input type="tel" id="contact" name="contact" value="<?php echo e(cc_old('contact')); ?>" autocomplete="tel" placeholder="024 123 4567" required>
                    <span class="cc-field-error" data-error-for="contact"></span>
                </div>

                <div class="cc-field">
                    <span class="cc-label">Country</span>
                    <div class="cc-fixed-value">Ghana</div>
                </div>
            </div>

            <div class="cc-field">
                <label class="cc-check">
                    <input type="checkbox" name="agree" value="1" required>
                    <span>
                        I accept the
                        <a class="cc-link" href="<?php echo BASE_URL; ?>views/pages/terms.php" target="_blank" rel="noopener">terms of use</a>
                        and the
                        <a class="cc-link" href="<?php echo BASE_URL; ?>views/pages/privacy.php" target="_blank" rel="noopener">privacy policy</a>.
                    </span>
                </label>
                <span class="cc-field-error" data-error-for="agree"></span>
            </div>

            <button type="submit" class="cc-button" data-busy="Creating your account...">Create account</button>
        </form>

        <p class="cc-auth-switch">
            Already have an account?
            <a class="cc-link" href="<?php echo BASE_URL; ?>views/login.php">Sign in</a>
        </p>
    </div>
</main>

<script src="<?php echo BASE_URL; ?>js/validate.js?v=20261122"></script>
<?php
cc_forget_old();
require_once __DIR__ . '/layout/footer.php';

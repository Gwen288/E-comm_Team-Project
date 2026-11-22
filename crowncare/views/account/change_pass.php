<?php

require_once __DIR__ . '/../../core/core.php';

require_customer();

$settingsPage = 'password';
$pageTitle = 'Change password';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-account">
    <?php require __DIR__ . '/../layout/account_settings_nav.php'; ?>

        <section class="cc-panel">
            <h2>Change password</h2>

            <form
                class="cc-form"
                action="<?php echo BASE_URL; ?>actions/change_pass_action.php"
                method="POST"
                data-ajax
                data-reset-on-success
                novalidate
            >
                <?php echo cc_csrf_field(); ?>
                <div class="cc-form-alert" role="alert" hidden></div>

                <div class="cc-field">
                    <label for="current_password">Current password</label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                    <span class="cc-field-error" data-error-for="current_password"></span>
                </div>

                <div class="cc-field">
                    <label for="new_password">New password</label>
                    <input type="password" id="new_password" name="new_password" autocomplete="new-password" required data-password-rules="change-rules">
                    <span class="cc-field-error" data-error-for="new_password"></span>
                </div>

                <ul class="cc-password-rules" id="change-rules" aria-label="Password rules">
                    <li data-rule="length">At least 8 characters</li>
                    <li data-rule="upper">An uppercase letter</li>
                    <li data-rule="lower">A lowercase letter</li>
                    <li data-rule="number">A number</li>
                    <li data-rule="symbol">A symbol, such as ! or @</li>
                </ul>

                <div class="cc-field">
                    <label for="confirm_password">Confirm new password</label>
                    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required data-match="new_password">
                    <span class="cc-field-error" data-error-for="confirm_password"></span>
                </div>

                <button type="submit" class="cc-button" data-busy="Saving...">Change password</button>
            </form>
        </section>

    </div>
</div>
</main>

<script src="<?php echo BASE_URL; ?>js/validate.js?v=20261122"></script>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>

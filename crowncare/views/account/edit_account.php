<?php
// Account settings page where a customer updates their name, email and phone.

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';

require_customer();

// Saved details come from the database, not the session, so edits show up at once.
$customer = (new CustomerController())->getCustomer(get_customer_id());
if (!$customer) {
    // The session points at an account that no longer exists.
    cc_force_sign_out('We could not find your account. Please sign in again.');
}

// Tells the shared settings menu which tab to highlight.
$settingsPage = 'details';
$pageTitle = 'Your details';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-account">
    <?php require __DIR__ . '/../layout/account_settings_nav.php'; ?>

        <section class="cc-panel">
            <h2>Your details</h2>

            <form
                class="cc-form"
                action="<?php echo BASE_URL; ?>actions/edit_account_action.php"
                method="POST"
                data-ajax
                novalidate
            >
                <?php echo cc_csrf_field(); ?>
                <div class="cc-form-alert" role="alert" hidden></div>

                <?php /* cc_old() refills a reloaded form; otherwise fall back to the saved details. */ ?>
                <div class="cc-field">
                    <label for="name">Full name</label>
                    <input type="text" id="name" name="name" value="<?php echo e(cc_old('name', $customer['customer_name'])); ?>" autocomplete="name" maxlength="100" required>
                    <span class="cc-field-error" data-error-for="name"></span>
                </div>

                <div class="cc-field">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="<?php echo e(cc_old('email', $customer['customer_email'])); ?>" autocomplete="email" maxlength="100" required data-original="<?php echo e($customer['customer_email']); ?>">
                    <span class="cc-field-error" data-error-for="email"></span>
                </div>

                <?php /* Hidden until the email actually changes; the script at the bottom reveals it and the action checks it. */ ?>
                <div class="cc-field" id="email-password-field" hidden>
                    <label for="current_password">Current password</label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password">
                    <p class="cc-hint">Needed to change your email address.</p>
                    <span class="cc-field-error" data-error-for="current_password"></span>
                </div>

                <div class="cc-form-row">
                    <div class="cc-field">
                        <label for="contact">Phone number</label>
                        <input type="tel" id="contact" name="contact" value="<?php echo e(cc_old('contact', $customer['customer_contact'])); ?>" autocomplete="tel" required>
                        <span class="cc-field-error" data-error-for="contact"></span>
                    </div>

                    <div class="cc-field">
                        <span class="cc-label">Country</span>
                        <div class="cc-fixed-value">Ghana</div>
                    </div>
                </div>

                <button type="submit" class="cc-button" data-busy="Saving...">Save changes</button>
            </form>
        </section>

    </div>
</div>
</main>

<script>
// Ask for the current password only when the email address is being changed.
(function () {
    var email = document.getElementById('email');
    var field = document.getElementById('email-password-field');
    function update() {
        field.hidden = email.value.trim().toLowerCase() === email.dataset.original.toLowerCase();
    }
    email.addEventListener('input', update);
    update();
})();
</script>
<?php
// Refilled values are only needed for the reload, so drop them now.
cc_forget_old();
require_once __DIR__ . '/../layout/footer.php';

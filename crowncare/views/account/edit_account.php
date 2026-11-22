<?php

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';
require_once __DIR__ . '/../../classes/DeliveryClass.php';

require_customer();

$customer = (new CustomerController())->getCustomer(get_customer_id());
if (!$customer) {
    cc_force_sign_out('We could not find your account. Please sign in again.');
}

$areas = (new DeliveryClass())->getActiveAreas();
$currentArea = cc_old('city', $customer['customer_city']);

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
                        <label for="city">Area in Accra</label>
                        <select id="city" name="city" required>
                            <option value="">Choose your area</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?php echo e($area['area_name']); ?>" <?php echo $currentArea === $area['area_name'] ? 'selected' : ''; ?>><?php echo e($area['area_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="cc-field-error" data-error-for="city"></span>
                    </div>
                </div>

                <div class="cc-field">
                    <span class="cc-label">Country</span>
                    <div class="cc-fixed-value">Ghana</div>
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
cc_forget_old();
require_once __DIR__ . '/../layout/footer.php';

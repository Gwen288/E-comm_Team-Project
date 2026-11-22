<?php

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';

require_customer();

$ordersInProgress = (new CustomerController())->countOrdersInProgress(get_customer_id());

$settingsPage = 'close';
$pageTitle = 'Close account';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-account">
    <?php require __DIR__ . '/../layout/account_settings_nav.php'; ?>

        <section class="cc-panel cc-danger-panel">
            <h2>Close your account</h2>

            <ul class="cc-danger-list">
                <li>You will be signed out and will not be able to sign in again with this account.</li>
                <li>Your name, email address, phone number, saved cart and hair quiz results are removed.</li>
                <li>Upcoming consultations are cancelled.</li>
                <li>Records of past orders and payments are kept without your personal details, as the law requires for sales records.</li>
            </ul>

            <?php if ($ordersInProgress > 0): ?>
                <p class="cc-form-alert">
                    You have <?php echo $ordersInProgress === 1 ? 'an order' : $ordersInProgress . ' orders'; ?> on the way.
                    You can close your account once <?php echo $ordersInProgress === 1 ? 'it has' : 'they have'; ?> been delivered.
                </p>
                <p><a class="cc-link" href="<?php echo BASE_URL; ?>views/account/orders.php">View my orders</a></p>
            <?php else: ?>
                <form
                    class="cc-form"
                    action="<?php echo BASE_URL; ?>actions/delete_account_action.php"
                    method="POST"
                    data-ajax
                    data-confirm="Close your account?"
                    data-confirm-text="This cannot be undone."
                    data-confirm-button="Close my account"
                    novalidate
                >
                    <?php echo cc_csrf_field(); ?>
                    <div class="cc-form-alert" role="alert" hidden></div>

                    <div class="cc-field">
                        <label for="current_password">Enter your password to confirm</label>
                        <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                        <span class="cc-field-error" data-error-for="current_password"></span>
                    </div>

                    <button type="submit" class="cc-button cc-button--danger" data-busy="Closing...">Close my account</button>
                </form>
            <?php endif; ?>
        </section>

    </div>
</div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

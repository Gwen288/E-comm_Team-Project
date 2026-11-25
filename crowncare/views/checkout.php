<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

/*
 * Checkout for signed-in customers and guests. Guests give their email,
 * name and phone so the order can be followed up; they can also sign in
 * first to keep the order in their account.
 */

block_admin_purchase();

$isSignedIn = is_logged_in();
$customerId = $isSignedIn ? get_customer_id() : 0;
$customer = null;

$cartController = new CartController();
$cartItems = $cartController->getCartItems($customerId);

if (empty($cartItems)) {
    $_SESSION['error'] = 'Your cart is empty. Add a product before checking out.';
    redirect(BASE_URL . 'views/cart.php');
}

if ($isSignedIn) {
    $customer = (new CustomerController())->getCustomer($customerId);
    if (!$customer) {
        cc_force_sign_out('We could not find your account. Please sign in again.');
    }
}

if ($cartController->stockProblems($customerId)) {
    $_SESSION['error'] = 'Some items in your cart are no longer available in that quantity. Please update your cart.';
    redirect(BASE_URL . 'views/cart.php');
}

// Signed-in customers' names are split into first and last name for the form.
$accountName = $customer ? trim($customer['customer_name']) : '';
$nameParts = $accountName !== '' ? explode(' ', $accountName, 2) : ['', ''];
$defaultFirst = $nameParts[0] ?? '';
$defaultLast = $nameParts[1] ?? '';

$subtotal = 0.0;

foreach ($cartItems as $item) {
    $subtotal += (int) $item['qty'] * (float) $item['product_price'];
}

// Delivery is arranged with a third-party delivery service and is not charged here.
$total = $subtotal;

$paystackReady = paystack_is_configured();

$pageTitle = 'Checkout';
require_once __DIR__ . '/layout/header.php';
?>

<main class="shop-page checkout-page">

    <div class="cc-page-nav">
        <a class="cc-back" href="<?php echo BASE_URL; ?>views/cart.php">Back to cart</a>
        <div class="cc-page-nav-links">
            <a href="<?php echo BASE_URL; ?>index.php">Back to home</a>
        </div>
    </div>

    <div class="checkout-heading">
        <div>
            <h1>Checkout</h1>
            <p>Confirm your details and pay securely with Paystack.</p>
        </div>
    </div>

    <?php if (!$isSignedIn): ?>
        <p class="checkout-account-note">
            Have an account?
            <a href="<?php echo BASE_URL; ?>views/login.php?return=checkout">Log in</a>
            to keep this order with your other orders. You can also check out as a guest below.
        </p>
    <?php endif; ?>

    <?php if (!$paystackReady): ?>
        <div class="checkout-warning">
            Paystack test keys are not configured yet. Add your test keys to
            <code>crowncare/core/paystack_config.php</code> to enable payment.
        </div>
    <?php endif; ?>

    <div class="checkout-grid">

        <section class="checkout-form-panel surface-panel">
            <h2>Your details</h2>

            <form
                class="checkout-form"
                action="<?php echo BASE_URL; ?>actions/process_payment_action.php"
                method="POST"
            >
                <?php echo cc_csrf_field(); ?>
                <div class="checkout-field">
                    <label for="contact_email">Email address</label>
                    <?php if ($isSignedIn): ?>
                        <input
                            type="email"
                            id="contact_email"
                            value="<?php echo e($customer['customer_email']); ?>"
                            readonly
                        >
                    <?php else: ?>
                        <input
                            type="email"
                            id="contact_email"
                            name="contact_email"
                            value="<?php echo e(cc_old('contact_email')); ?>"
                            autocomplete="email"
                            maxlength="100"
                            required
                        >
                    <?php endif; ?>
                    <small>We send your receipt and order updates to this address.</small>
                </div>

                <div class="checkout-row">
                    <div class="checkout-field">
                        <label for="first_name">First name</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="<?php echo e(cc_old('first_name', $defaultFirst)); ?>"
                            autocomplete="given-name"
                            maxlength="50"
                            required
                        >
                    </div>

                    <div class="checkout-field">
                        <label for="last_name">Last name</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="<?php echo e(cc_old('last_name', $defaultLast)); ?>"
                            autocomplete="family-name"
                            maxlength="50"
                            required
                        >
                    </div>
                </div>

                <div class="checkout-field">
                    <label for="delivery_phone">Phone number</label>
                    <input
                        type="tel"
                        id="delivery_phone"
                        name="delivery_phone"
                        value="<?php echo e(cc_old('delivery_phone', $customer['customer_contact'] ?? '')); ?>"
                        autocomplete="tel"
                        placeholder="024 123 4567"
                        required
                    >
                    <small>We use this number if there is a problem with your order, and the rider calls it on arrival.</small>
                </div>

                <h3 class="checkout-subhead">Delivery</h3>

                <div class="checkout-field">
                    <label for="delivery_address">Delivery address</label>
                    <textarea
                        id="delivery_address"
                        name="delivery_address"
                        placeholder="Area, house number, street and a nearby landmark"
                        autocomplete="street-address"
                        maxlength="255"
                        required
                    ><?php echo e(cc_old('delivery_address')); ?></textarea>
                    <small>Tell us where to deliver your order.</small>
                </div>

                <div class="checkout-field">
                    <label for="delivery_notes">Delivery notes (optional)</label>
                    <textarea
                        id="delivery_notes"
                        name="delivery_notes"
                        placeholder="Any instructions for the rider"
                        maxlength="255"
                    ><?php echo e(cc_old('delivery_notes')); ?></textarea>
                </div>

                <button
                    type="submit"
                    class="checkout-pay-btn"
                    <?php echo $paystackReady ? '' : 'disabled'; ?>
                >
                    Pay <?php echo e(cc_money($total)); ?> with Paystack
                </button>

                <p class="checkout-secure-note">
                    Payments are processed by Paystack. CrownCare does not store
                    your card details.
                </p>
            </form>
        </section>

        <aside class="checkout-summary surface-panel">
            <h2>Order summary</h2>

            <ul class="checkout-items">
                <?php foreach ($cartItems as $item): ?>
                    <?php
                    $lineImage = cc_cart_image($item, (string) ($item['size_name'] ?? ''));
                    $hasImage = $lineImage !== null;
                    ?>
                    <li class="checkout-line">
                        <?php if ($hasImage): ?>
                            <img
                                class="checkout-line-img"
                                src="<?php echo htmlspecialchars($lineImage); ?>"
                                alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                            >
                        <?php else: ?>
                            <span class="checkout-line-img checkout-line-img--empty" aria-hidden="true"></span>
                        <?php endif; ?>

                        <div class="checkout-line-copy">
                            <strong><?php echo htmlspecialchars($item['product_title']); ?></strong>
                            <span>
                                <?php if (!empty($item['size_name'])): ?>Size: <?php echo htmlspecialchars(cc_size_label($item['size_name'])); ?>, <?php endif; ?>
                                Qty <?php echo (int) $item['qty']; ?>
                            </span>
                        </div>

                        <strong class="checkout-line-price">
                            GH₵<?php
                            echo number_format(
                                (int) $item['qty'] * (float) $item['product_price'],
                                2
                            );
                            ?>
                        </strong>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="checkout-summary-row">
                <span>Subtotal</span>
                <span>GH₵<?php echo number_format($subtotal, 2); ?></span>
            </div>

            <div class="checkout-summary-row checkout-total">
                <span>Total</span>
                <span><?php echo e(cc_money($total)); ?></span>
            </div>

            <p class="checkout-delivery-note">
                Delivery is handled by our delivery partner, who will contact you
                to arrange it. The delivery charge is not paid here.
            </p>
        </aside>

    </div>

</main>

<?php
cc_forget_old();
require_once __DIR__ . '/layout/footer.php';

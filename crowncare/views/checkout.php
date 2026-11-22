<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../classes/DeliveryClass.php';

if (!is_logged_in()) {
    $_SESSION['info'] = 'Please sign in or create an account to check out. Your cart is saved.';
    $_SESSION['redirect_after_login'] = BASE_URL . 'views/checkout.php';
    redirect(BASE_URL . 'views/login.php');
}

block_admin_purchase();

$customerId = get_customer_id();

$cartController = new CartController();
$customerController = new CustomerController();

$cartItems = $cartController->getCartItems($customerId);
$customer = $customerController->getCustomer($customerId);

if (empty($cartItems)) {
    $_SESSION['error'] = 'Your cart is empty. Add a product before checking out.';
    redirect(BASE_URL . 'views/cart.php');
}

if (!$customer) {
    cc_force_sign_out('We could not find your account. Please sign in again.');
}

if ($cartController->stockProblems($customerId)) {
    $_SESSION['error'] = 'Some items in your cart are no longer available in that quantity. Please update your cart.';
    redirect(BASE_URL . 'views/cart.php');
}

$subtotal = 0.0;

foreach ($cartItems as $item) {
    $subtotal += (int) $item['qty'] * (float) $item['product_price'];
}

$delivery = new DeliveryClass();
$areas = $delivery->getActiveAreas();
$selectedAreaName = cc_old('delivery_area', $customer['customer_city']);
$selectedArea = $delivery->getActiveAreaByName($selectedAreaName);
$freeDelivery = $subtotal >= FREE_DELIVERY_THRESHOLD;

$deliveryFee = DeliveryClass::feeFor($selectedArea, $subtotal);
$total = $subtotal + $deliveryFee;

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
            <p>Confirm your delivery details and pay securely with Paystack.</p>
        </div>
    </div>

    <?php if (!$paystackReady): ?>
        <div class="checkout-warning">
            Paystack test keys are not configured yet. Add your test keys to
            <code>crowncare/core/paystack_config.php</code> to enable payment.
        </div>
    <?php endif; ?>

    <div class="checkout-grid">

        <section class="checkout-form-panel surface-panel">
            <h2>Delivery details</h2>

            <form
                class="checkout-form"
                action="<?php echo BASE_URL; ?>actions/process_payment_action.php"
                method="POST"
                data-free-delivery="<?php echo $freeDelivery ? '1' : '0'; ?>"
                data-subtotal="<?php echo e(number_format($subtotal, 2, '.', '')); ?>"
            >
                <?php echo cc_csrf_field(); ?>
                <div class="checkout-field">
                    <label for="delivery_name">Full name</label>
                    <input
                        type="text"
                        id="delivery_name"
                        name="delivery_name"
                        value="<?php echo e(cc_old('delivery_name', $customer['customer_name'])); ?>"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="checkout-field">
                    <label for="delivery_email">Email address</label>
                    <input
                        type="email"
                        id="delivery_email"
                        value="<?php echo htmlspecialchars($customer['customer_email']); ?>"
                        readonly
                    >
                    <small>Paystack will send the payment receipt to this address.</small>
                </div>

                <div class="checkout-row">
                    <div class="checkout-field">
                        <label for="delivery_phone">Phone number</label>
                        <input
                            type="tel"
                            id="delivery_phone"
                            name="delivery_phone"
                            value="<?php echo e(cc_old('delivery_phone', $customer['customer_contact'])); ?>"
                            autocomplete="tel"
                            placeholder="024 123 4567"
                            required
                        >
                        <small>The rider calls this number on arrival.</small>
                    </div>

                    <div class="checkout-field">
                        <label for="delivery_area">Area in Accra</label>
                        <select id="delivery_area" name="delivery_area" required data-delivery-area>
                            <option value="">Choose your area</option>
                            <?php foreach ($areas as $area): ?>
                                <option
                                    value="<?php echo e($area['area_name']); ?>"
                                    data-fee="<?php echo e(number_format((float) $area['delivery_fee'], 2, '.', '')); ?>"
                                    <?php echo $selectedAreaName === $area['area_name'] ? 'selected' : ''; ?>
                                ><?php echo e($area['area_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small>We deliver in Greater Accra only.</small>
                    </div>
                </div>

                <div class="checkout-field">
                    <label for="delivery_address">Delivery address</label>
                    <textarea
                        id="delivery_address"
                        name="delivery_address"
                        placeholder="House number, street and a nearby landmark"
                        autocomplete="street-address"
                        maxlength="255"
                        required
                    ><?php echo e(cc_old('delivery_address')); ?></textarea>
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
                    Pay <span data-pay-total><?php echo e(cc_money($total)); ?></span> with Paystack
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
                                <?php if (!empty($item['size_name'])): ?>Size: <?php echo htmlspecialchars(cc_size_label($item['size_name'])); ?> &middot; <?php endif; ?>
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

            <div class="checkout-summary-row">
                <span>Delivery</span>
                <span data-delivery-fee>
                    <?php
                    if ($freeDelivery) {
                        echo 'Free';
                    } elseif ($selectedArea) {
                        echo e(cc_money($deliveryFee));
                    } else {
                        echo 'Choose your area';
                    }
                    ?>
                </span>
            </div>

            <div class="checkout-summary-row checkout-total">
                <span>Total</span>
                <span data-order-total><?php echo e(cc_money($total)); ?></span>
            </div>
        </aside>

    </div>

</main>

<script>
// Delivery fee and total follow the chosen area.
(function () {
    var form = document.querySelector('.checkout-form');
    var area = document.querySelector('[data-delivery-area]');
    if (!form || !area) return;

    var subtotal = parseFloat(form.dataset.subtotal) || 0;
    var free = form.dataset.freeDelivery === '1';
    var money = function (n) {
        return 'GH\u20B5' + n.toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    function update() {
        var option = area.options[area.selectedIndex];
        var fee = free || !option || !option.dataset.fee ? 0 : parseFloat(option.dataset.fee);
        document.querySelector('[data-delivery-fee]').textContent =
            free ? 'Free' : (option && option.dataset.fee ? money(fee) : 'Choose your area');
        document.querySelector('[data-order-total]').textContent = money(subtotal + fee);
        document.querySelector('[data-pay-total]').textContent = money(subtotal + fee);
    }

    area.addEventListener('change', update);
})();
</script>

<?php
cc_forget_old();
require_once __DIR__ . '/layout/footer.php';

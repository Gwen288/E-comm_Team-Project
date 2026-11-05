<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if (!is_logged_in()) {
    $_SESSION['error'] = 'Please sign in to continue to checkout.';
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
    $_SESSION['error'] = 'We could not find your account. Please log in again.';
    redirect(BASE_URL . 'logout.php');
}

$subtotal = 0.0;

foreach ($cartItems as $item) {
    $subtotal += (int) $item['qty'] * (float) $item['product_price'];
}

$deliveryFee = $subtotal >= FREE_DELIVERY_THRESHOLD
    ? 0.0
    : (float) DELIVERY_FEE;

$total = $subtotal + $deliveryFee;

$paystackReady = paystack_is_configured();

require_once __DIR__ . '/layout/header.php';
?>

<main class="shop-page checkout-page">

    <div class="checkout-heading">
        <div>
            <h1>Checkout</h1>
            <p>Confirm your delivery details and pay securely with Paystack.</p>
        </div>

        <a class="cart-continue-top" href="<?php echo BASE_URL; ?>views/cart.php">
            Back to Cart
        </a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-message">
            <?php
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-message">
            <?php
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

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
            >
                <div class="checkout-field">
                    <label for="delivery_name">Full name</label>
                    <input
                        type="text"
                        id="delivery_name"
                        name="delivery_name"
                        value="<?php echo htmlspecialchars($customer['customer_name']); ?>"
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
                            value="<?php echo htmlspecialchars($customer['customer_contact']); ?>"
                            autocomplete="tel"
                            required
                        >
                    </div>

                    <div class="checkout-field">
                        <label for="delivery_city">City / town</label>
                        <input
                            type="text"
                            id="delivery_city"
                            name="delivery_city"
                            value="<?php echo htmlspecialchars($customer['customer_city']); ?>"
                            autocomplete="address-level2"
                            required
                        >
                    </div>
                </div>

                <div class="checkout-field">
                    <label for="delivery_address">Delivery address</label>
                    <textarea
                        id="delivery_address"
                        name="delivery_address"
                        placeholder="House number, street, area, landmark"
                        autocomplete="street-address"
                        required
                    ></textarea>
                </div>

                <div class="checkout-field">
                    <label for="delivery_notes">Delivery notes (optional)</label>
                    <textarea
                        id="delivery_notes"
                        name="delivery_notes"
                        placeholder="Any instructions for the courier"
                    ></textarea>
                </div>

                <button
                    type="submit"
                    class="checkout-pay-btn"
                    <?php echo $paystackReady ? '' : 'disabled'; ?>
                >
                    Pay GH₵<?php echo number_format($total, 2); ?> with Paystack
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
                    $image = trim($item['product_image'] ?? '');
                    $imagePath = __DIR__ . '/../images/products/' . basename($image);
                    $hasImage = $image !== '' && is_file($imagePath);
                    ?>
                    <li class="checkout-line">
                        <?php if ($hasImage): ?>
                            <img
                                class="checkout-line-img"
                                src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($image)); ?>"
                                alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                            >
                        <?php else: ?>
                            <span class="checkout-line-img checkout-line-img--empty" aria-hidden="true"></span>
                        <?php endif; ?>

                        <div class="checkout-line-copy">
                            <strong><?php echo htmlspecialchars($item['product_title']); ?></strong>
                            <span>Qty <?php echo (int) $item['qty']; ?></span>
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
                <span>
                    <?php
                    echo $deliveryFee > 0
                        ? 'GH₵' . number_format($deliveryFee, 2)
                        : 'Free';
                    ?>
                </span>
            </div>

            <div class="checkout-summary-row checkout-total">
                <span>Total</span>
                <span>GH₵<?php echo number_format($total, 2); ?></span>
            </div>
        </aside>

    </div>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

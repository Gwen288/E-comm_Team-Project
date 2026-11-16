<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

/*
 * Cart page, laid out like the CMS (Wix) cart:
 * "My cart" lines (photo of the chosen size, name, size, quantity counter,
 * line total, remove) and an "Order summary" with subtotal, delivery and total.
 */

$cartController = new CartController();

block_admin_purchase();

$cartItems = $cartController->getCartItems(get_customer_id());

$subtotal = 0.0;
foreach ($cartItems as $item) {
    $subtotal += (int) $item['qty'] * (float) $item['product_price'];
}

// Same delivery rule as checkout.
$deliveryFee = ($subtotal >= FREE_DELIVERY_THRESHOLD || !$cartItems) ? 0.0 : (float) DELIVERY_FEE;
$total = $subtotal + $deliveryFee;

require_once __DIR__ . '/layout/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/cms-shop.css?v=20261111">

<main class="cc-page cc-cart-page">

    <?php if (!empty($_SESSION['cart_message'])): ?>
        <div class="cc-shell">
            <p class="cc-notice" role="status"><?php echo cc_e($_SESSION['cart_message']); unset($_SESSION['cart_message']); ?></p>
        </div>
    <?php endif; ?>

    <div class="cc-cart">

        <!-- MY CART -->
        <section aria-labelledby="cc-cart-title">
            <h1 class="cc-cart-title" id="cc-cart-title">My cart</h1>

            <?php if (!$cartItems): ?>
                <div class="cc-cart-empty">
                    <p>Your cart is empty.</p>
                    <a class="cc-btn" href="<?php echo BASE_URL; ?>views/shop.php">Continue Browsing</a>
                </div>
            <?php endif; ?>

            <?php foreach ($cartItems as $item): ?>
                <?php
                $sizeId = (int) ($item['size_id'] ?? 0);
                $sizeName = $item['size_name'] ?? '';
                $line = (int) $item['p_id'] . ':' . $sizeId;
                $qty = (int) $item['qty'];
                $image = cc_cart_image($item, (string) $sizeName);
                $url = BASE_URL . 'views/single_product.php?pro_id=' . (int) $item['p_id'] . ($sizeId ? '&size=' . $sizeId : '');
                ?>
                <div class="cc-cart-item">
                    <a class="cc-cart-thumb" href="<?php echo $url; ?>" tabindex="-1" aria-hidden="true">
                        <?php if ($image): ?>
                            <img src="<?php echo cc_e($image); ?>" alt="">
                        <?php endif; ?>
                    </a>

                    <div>
                        <a class="cc-cart-name" href="<?php echo $url; ?>"><?php echo cc_e($item['product_title']); ?></a>
                        <?php if ($sizeName): ?>
                            <p class="cc-cart-option">Size: <?php echo cc_e(cc_size_label($sizeName)); ?></p>
                        <?php endif; ?>
                        <?php if ($qty > 1): ?>
                            <p class="cc-cart-unit"><?php echo cc_money($item['product_price']); ?> each</p>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="<?php echo BASE_URL; ?>actions/update_qty_action.php" data-qty-form>
                        <input type="hidden" name="line" value="<?php echo cc_e($line); ?>">
                        <div class="cc-counter cc-counter--small">
                            <button type="button" data-step="-1" aria-label="Decrease quantity" <?php echo $qty <= 1 ? 'disabled' : ''; ?>>&minus;</button>
                            <input type="number" name="qty" value="<?php echo $qty; ?>" min="1" max="99" inputmode="numeric" aria-label="Quantity of <?php echo cc_e($item['product_title']); ?>">
                            <button type="button" data-step="1" aria-label="Increase quantity">+</button>
                        </div>
                    </form>

                    <p class="cc-cart-total"><?php echo cc_money($qty * (float) $item['product_price']); ?></p>

                    <form class="cc-remove-form" method="POST" action="<?php echo BASE_URL; ?>actions/remove_from_cart_action.php">
                        <input type="hidden" name="line" value="<?php echo cc_e($line); ?>">
                        <button type="submit" class="cc-cart-remove" aria-label="Remove <?php echo cc_e($item['product_title']); ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14M10 7V5h4v2M7 7l1 12h8l1-12M10 11v5M14 11v5" fill="none" stroke="currentColor" stroke-width="1.3"/></svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- ORDER SUMMARY -->
        <?php if ($cartItems): ?>
            <aside class="cc-summary" aria-labelledby="cc-summary-title">
                <h2 class="cc-summary-title" id="cc-summary-title">Order summary</h2>

                <div class="cc-summary-row">
                    <span>Subtotal</span>
                    <span><?php echo cc_money($subtotal); ?></span>
                </div>

                <div class="cc-summary-row">
                    <span>Delivery</span>
                    <span><?php echo $deliveryFee > 0 ? cc_money($deliveryFee) : 'FREE'; ?></span>
                </div>
                <p class="cc-summary-note">
                    Ghana<?php echo $deliveryFee > 0 ? '. Free on orders from ' . cc_money(FREE_DELIVERY_THRESHOLD) : ''; ?>
                </p>

                <div class="cc-summary-total">
                    <span>Total</span>
                    <span><?php echo cc_money($total); ?></span>
                </div>

                <a class="cc-btn cc-btn--tall" href="<?php echo BASE_URL; ?>views/checkout.php">Checkout</a>

                <p class="cc-secure">
                    <svg viewBox="0 0 11 14" aria-hidden="true"><path d="M2 6V4a3.5 3.5 0 017 0v2h1v8H1V6zm1.5 0h4V4a2 2 0 00-4 0z" fill="currentColor"/></svg>
                    Secure Checkout
                </p>
            </aside>
        <?php endif; ?>

    </div>
</main>

<script>
// Quantity counters save straight away, as on the CMS cart.
document.querySelectorAll('[data-qty-form]').forEach(function (form) {
    var input = form.querySelector('input[name="qty"]');

    form.addEventListener('click', function (e) {
        var step = e.target.closest('[data-step]');
        if (!step) return;
        var next = Math.max(1, Math.min(99, (parseInt(input.value, 10) || 1) + parseInt(step.dataset.step, 10)));
        if (String(next) !== input.value) {
            input.value = next;
            form.submit();
        }
    });

    input.addEventListener('change', function () {
        input.value = Math.max(1, Math.min(99, parseInt(input.value, 10) || 1));
        form.submit();
    });
});
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

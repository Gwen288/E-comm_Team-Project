
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$cartController = new CartController();

block_admin_purchase();

$cartCustomerId = get_customer_id();
$cartItems = $cartController->getCartItems($cartCustomerId);

$itemCount = 0;

foreach ($cartItems as $item) {
    $itemCount += (int) $item['qty'];
}

require_once __DIR__ . '/layout/header.php';
?>

<main class="shop-page cart-page">

    <div class="cart-heading">
        <div>
            <p class="eyebrow">Your bag</p>
            <h1>Shopping cart</h1>

            <?php if (!empty($cartItems)): ?>
                <p>
                    <?php echo (int) $itemCount; ?>
                    item<?php echo $itemCount === 1 ? '' : 's'; ?>
                    ready for checkout
                </p>
            <?php else: ?>
                <p>Your bag is waiting for something lovely.</p>
            <?php endif; ?>
        </div>

        <a class="cart-continue-top" href="<?php echo BASE_URL; ?>views/shop.php">
            Continue shopping
        </a>
    </div>

    <?php if (!empty($_SESSION['cart_message'])): ?>
        <div class="cart-message">
            <?php
            echo htmlspecialchars($_SESSION['cart_message']);
            unset($_SESSION['cart_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>

        <section class="cart-empty">
            <h2>Your cart is empty</h2>
            <p>Browse the shop and add products you love.</p>

            <a class="cart-primary-btn" href="<?php echo BASE_URL; ?>views/shop.php">
                Browse products
            </a>
        </section>

    <?php else: ?>

        <div class="cart-layout">

            <section class="cart-items surface-panel" aria-label="Cart items">

                <form
                    id="update-qty-form"
                    action="<?php echo BASE_URL; ?>actions/update_qty_action.php"
                    method="POST"
                >

                    <?php foreach ($cartItems as $item): ?>
                        <?php
                        $productId = (int) $item['p_id'];
                        $image = trim($item['product_image'] ?? '');
                        $imagePath = __DIR__ . '/../images/products/' . basename($image);
                        $hasImage = $image !== '' && is_file($imagePath);

                        $lineTotal = (int) $item['qty'] * (float) $item['product_price'];
                        ?>

                        <article class="cart-item">

                            <a
                                class="cart-item-media"
                                href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo $productId; ?>"
                            >
                                <?php if ($hasImage): ?>
                                    <img
                                        src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($image)); ?>"
                                        alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                    >
                                <?php else: ?>
                                    <div class="cart-image-placeholder">Photo soon</div>
                                <?php endif; ?>
                            </a>

                            <div class="cart-item-body">

                                <div class="cart-item-top">
                                    <div>
                                        <h2>
                                            <a href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo $productId; ?>">
                                                <?php echo htmlspecialchars($item['product_title']); ?>
                                            </a>
                                        </h2>

                                        <p class="cart-item-unit">
                                            GH₵<?php echo number_format((float) $item['product_price'], 2); ?> each
                                        </p>
                                    </div>

                                    <p class="cart-item-line">
                                        GH₵<?php echo number_format($lineTotal, 2); ?>
                                    </p>
                                </div>

                                <div class="cart-item-controls">

                                    <label class="cart-qty-label">
                                        <span>Qty</span>

                                        <input
                                            class="cart-quantity"
                                            type="number"
                                            name="qty[]"
                                            value="<?php echo (int) $item['qty']; ?>"
                                            min="1"
                                            required
                                            aria-label="Quantity for <?php echo htmlspecialchars($item['product_title']); ?>"
                                        >
                                    </label>

                                    <input
                                        type="hidden"
                                        name="update[]"
                                        value="<?php echo $productId; ?>"
                                    >

                                    <button
                                        type="submit"
                                        form="remove-cart-form"
                                        class="cart-item-remove"
                                        name="remove[]"
                                        value="<?php echo $productId; ?>"
                                    >
                                        Remove
                                    </button>

                                </div>
                            </div>
                        </article>

                    <?php endforeach; ?>

                    <div class="cart-update-row">
                        <button type="submit" class="cart-secondary-btn">
                            Update quantities
                        </button>
                    </div>

                </form>

                <form
                    id="remove-cart-form"
                    action="<?php echo BASE_URL; ?>actions/remove_from_cart_action.php"
                    method="POST"
                    hidden
                ></form>

            </section>

            <aside class="cart-summary-panel surface-panel">

                <?php
                // Calculate the subtotal directly inside the order summary.
                $summaryTotal = 0;

                foreach ($cartItems as $summaryItem) {
                    $summaryQuantity = (int) $summaryItem['qty'];
                    $summaryPrice = (float) $summaryItem['product_price'];

                    $summaryTotal += $summaryQuantity * $summaryPrice;
                }
                ?>

                <h2>Order summary</h2>

                <div class="cart-subtotal-row">
                    <span>Subtotal</span>
                    <strong>GH₵<?php echo number_format($summaryTotal, 2); ?></strong>
                </div>

                <p class="cart-summary-note">
                    Delivery is calculated at checkout. Orders from GH₵300 ship free.
                </p>

                <a
                    class="cart-primary-btn cart-checkout-btn"
                    href="<?php echo BASE_URL; ?>views/checkout.php"
                >
                    Proceed to checkout
                </a>

            </aside>

        </div>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$cartController = new CartController();
$cartIp = get_ip();

$cartItems = $cartController->getCartItems($cartIp);
$cartTotal = $cartController->getCartTotal($cartIp);

require_once __DIR__ . '/layout/header.php';
?>

<main class="shop-page cart-page">

    <div class="cart-heading">
        <div>
            <h1>Shopping Cart</h1>
            <p>Review your items and update quantities before checkout.</p>
        </div>

        <a class="cart-continue-top" href="<?php echo BASE_URL; ?>index.php">
            Continue Shopping
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
            <p>You haven't added any products to your cart yet.</p>

            <a class="cart-primary-btn" href="<?php echo BASE_URL; ?>index.php">
                Browse Products
            </a>
        </section>

    <?php else: ?>

        <section class="cart-panel">

            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Remove</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="remove[]"
                                        value="<?php echo (int) $item['p_id']; ?>"
                                        form="remove-cart-form"
                                        aria-label="Remove <?php echo htmlspecialchars($item['product_title']); ?>"
                                    >
                                </td>

                                <td>
                                    <div class="cart-product">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img
                                                src="<?php
                                                    echo BASE_URL . 'images/products/'
                                                        . rawurlencode(
                                                            basename($item['product_image'])
                                                        );
                                                ?>"
                                                alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                                class="cart-product-image"
                                            >
                                        <?php else: ?>
                                            <div class="cart-image-placeholder">
                                                No image
                                            </div>
                                        <?php endif; ?>

                                        <span class="cart-product-name">
                                            <?php echo htmlspecialchars($item['product_title']); ?>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <input
                                        class="cart-quantity"
                                        type="number"
                                        name="qty[]"
                                        value="<?php echo (int) $item['qty']; ?>"
                                        min="1"
                                        required
                                        form="update-qty-form"
                                        aria-label="Quantity for <?php echo htmlspecialchars($item['product_title']); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="update[]"
                                        value="<?php echo (int) $item['p_id']; ?>"
                                        form="update-qty-form"
                                    >
                                </td>

                                <td class="cart-price">
                                    $<?php echo number_format((float) $item['product_price'], 2); ?>
                                </td>

                                <td class="cart-price cart-line-total">
                                    $<?php
                                        echo number_format(
                                            (int) $item['qty'] * (float) $item['product_price'],
                                            2
                                        );
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-actions">

                <form
                    id="update-qty-form"
                    class="cart-form"
                    action="<?php echo BASE_URL; ?>actions/update_qty_action.php"
                    method="POST"
                >
                    <button
                        type="submit"
                        class="cart-secondary-btn"
                    >
                        Update Quantity
                    </button>
                </form>

                <form
                    id="remove-cart-form"
                    action="<?php echo BASE_URL; ?>actions/remove_from_cart_action.php"
                    method="POST"
                >
                    <button
                        type="submit"
                        class="cart-remove-btn"
                        name="remove_items"
                        value="1"
                    >
                        Remove Selected
                    </button>
                </form>

            </div>

            <div class="cart-summary-panel">
                <div class="cart-subtotal-row">
                    <span>Subtotal</span>
                    <strong>$<?php echo number_format($cartTotal, 2); ?></strong>
                </div>

                <p class="cart-summary-note">
                    Shipping and any applicable taxes are not included.
                </p>

                <a
                    class="cart-primary-btn cart-checkout-btn"
                    href="<?php echo BASE_URL; ?>views/checkout.php"
                >
                    Proceed to Checkout
                </a>
            </div>

        </section>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

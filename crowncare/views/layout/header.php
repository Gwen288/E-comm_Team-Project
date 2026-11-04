
<?php
// core.php should already be loaded before this header.
require_once __DIR__ . '/../../controllers/CartController.php';

$cartController = new CartController();

$cartCustomerId = is_logged_in() ? get_customer_id() : 0;

$cartCount = 0;
$cartTotal = 0;

if ($cartCustomerId > 0) {
    $cartCount = $cartController->getCartCount($cartCustomerId);
    $cartTotal = $cartController->getCartTotal($cartCustomerId);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shoppn</title>

    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>css/style.css"
    >
</head>

<body>

<header class="site-header">

    <div class="header-shell">

        <!-- UPPER HEADER SECTION -->
        <div class="header-main-row">

            <!-- Brand -->
            <a
                href="<?php echo BASE_URL; ?>index.php"
                class="brand-name"
            >
                CrownCare
            </a>

            <!-- Account and Cart Actions -->
            <div class="header-actions">

                <?php if (is_logged_in()): ?>

                    <span class="header-link">
                        Welcome,
                        <?php echo htmlspecialchars($_SESSION['customer_name']); ?>
                    </span>

                    <a
                        href="<?php echo BASE_URL; ?>views/account/my_account.php"
                        class="header-link"
                    >
                        My Account
                    </a>

                    <a
                        href="<?php echo BASE_URL; ?>logout.php"
                        class="header-link"
                    >
                        Logout
                    </a>

                <?php else: ?>

                    <a
                        href="<?php echo BASE_URL; ?>views/register.php"
                        class="header-link"
                    >
                        Register
                    </a>

                    <a
                        href="<?php echo BASE_URL; ?>views/login.php"
                        class="header-link"
                    >
                        Login
                    </a>

                <?php endif; ?>

                <!-- Shopping Cart -->
                <a
                    href="<?php echo BASE_URL; ?>views/cart.php"
                    class="header-cart"
                >
                    Cart

                    <span class="cart-count">
                        <?php echo (int) $cartCount; ?>
                    </span>
                </a>

            </div>

        </div>

        <!-- LOWER HEADER SECTION: MAIN NAVIGATION -->
        <div class="header-nav">

            <div class="header-nav-row">

                <a href="<?php echo BASE_URL; ?>index.php">
                    Home
                </a>

                <?php if (is_logged_in() && is_admin()): ?>

                    <a href="<?php echo BASE_URL; ?>views/admin/brand.php">
                        Brands
                    </a>

                    <a href="<?php echo BASE_URL; ?>views/admin/category.php">
                        Categories
                    </a>

                    <a href="<?php echo BASE_URL; ?>views/admin/product.php">
                        Products
                    </a>

                <?php endif; ?>

            </div>

        </div>

        <!-- CART TOTAL -->
        <div class="header-cart-total">
            Shopping Cart Total:
            <strong>
                GH₵<?php echo number_format($cartTotal, 2); ?>
            </strong>
        </div>

    </div>

</header>
</body>
</html>

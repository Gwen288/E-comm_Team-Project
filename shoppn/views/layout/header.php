<?php
// core.php should already be loaded before this header
require_once __DIR__ . '/../../controllers/CartController.php';

$cartController = new CartController();
$cartIp = get_ip();

$cartCount = $cartController->getCartCount($cartIp);
$cartTotal = $cartController->getCartTotal($cartIp);
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

<header>
    <nav>
        <a href="<?php echo BASE_URL; ?>index.php">
            Shoppn
        </a>

        <a href="<?php echo BASE_URL; ?>index.php">
            Home
        </a>

        <?php if (is_logged_in()): ?>

            <span>
                Welcome,
                <?php echo htmlspecialchars($_SESSION['customer_name']); ?>
            </span>

            <a href="<?php echo BASE_URL; ?>views/account/my_account.php">
                My Account
            </a>

            <?php if (is_admin()): ?>
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

            <a href="<?php echo BASE_URL; ?>logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="<?php echo BASE_URL; ?>views/register.php">
                Register
            </a>

            <a href="<?php echo BASE_URL; ?>views/login.php">
                Login
            </a>

        <?php endif; ?>
    </nav>

    <div class="cart-summary">
        <span>
            Shopping Cart —
            Total Items: <?php echo (int) $cartCount; ?> |
            Total Price: $<?php echo number_format($cartTotal, 2); ?>
        </span>

        <a href="<?php echo BASE_URL; ?>views/cart.php">
            Go to Cart
        </a>
    </div>
</header>

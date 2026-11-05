<?php
// core.php should already be loaded before this header
require_once __DIR__ . '/../../controllers/CartController.php';

$cartController = new CartController();
$cartCustomerId = is_logged_in() ? get_customer_id() : 0;

/* Guest carts live in the session — always ask the controller. */
$cartCount = (!(is_logged_in() && is_admin()))
    ? $cartController->getCartCount($cartCustomerId)
    : 0;

$headerSearchQuery = trim($_GET['user_query'] ?? '');
$isAdminViewer = is_logged_in() && is_admin();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrownCare | Natural Hair Care</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Nunito+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>css/style.css?v=20261105-stickyfoot"
    >
</head>

<body>

<header class="site-header">
    <div class="header-shell header-main-row">
        <a href="<?php echo BASE_URL; ?>index.php" class="brand-name">
            CrownCare
        </a>

        <form
            class="header-search"
            action="<?php echo BASE_URL; ?>views/shop.php"
            method="GET"
            role="search"
        >
            <label class="visually-hidden" for="header-search-input">Search products</label>
            <input
                id="header-search-input"
                type="search"
                name="user_query"
                placeholder="Search oils, shampoos, leave-ins..."
                value="<?php echo htmlspecialchars($headerSearchQuery, ENT_QUOTES, 'UTF-8'); ?>"
            >
            <button type="submit" class="header-search-icon" aria-label="Search">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
                    <circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="2"></circle>
                    <path d="M16.5 16.5L21 21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                </svg>
            </button>
        </form>

        <div class="header-actions">
            <?php if ($isAdminViewer): ?>
                <a class="header-link" href="<?php echo BASE_URL; ?>views/admin/index.php">
                    Dashboard
                </a>
                <a class="header-link" href="<?php echo BASE_URL; ?>logout.php">Sign out</a>
            <?php elseif (is_logged_in()): ?>
                <a
                    class="header-icon-link"
                    href="<?php echo BASE_URL; ?>views/account/my_account.php"
                    aria-label="My account"
                    title="My account"
                >
                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="8" r="3.25" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                        <path d="M5.5 19.2c1.4-3.1 3.6-4.6 6.5-4.6s5.1 1.5 6.5 4.6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                </a>
                <a
                    class="header-icon-link header-cart-icon"
                    href="<?php echo BASE_URL; ?>views/cart.php"
                    aria-label="Cart, <?php echo (int) $cartCount; ?> items"
                    title="Cart"
                >
                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
                        <path d="M7 8V7a5 5 0 0 1 10 0v1" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                        <path d="M6.2 8.5h11.6l-.7 10.2a1.8 1.8 0 0 1-1.8 1.6H8.7a1.8 1.8 0 0 1-1.8-1.6L6.2 8.5z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                    </svg>
                    <?php if ((int) $cartCount > 0): ?>
                        <span class="cart-count"><?php echo (int) $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a
                    class="header-icon-link"
                    href="<?php echo BASE_URL; ?>views/login.php"
                    aria-label="Sign in"
                    title="Sign in"
                >
                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="8" r="3.25" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                        <path d="M5.5 19.2c1.4-3.1 3.6-4.6 6.5-4.6s5.1 1.5 6.5 4.6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                </a>
                <a
                    class="header-icon-link header-cart-icon"
                    href="<?php echo BASE_URL; ?>views/cart.php"
                    aria-label="Cart, <?php echo (int) $cartCount; ?> items"
                    title="Cart"
                >
                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
                        <path d="M7 8V7a5 5 0 0 1 10 0v1" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                        <path d="M6.2 8.5h11.6l-.7 10.2a1.8 1.8 0 0 1-1.8 1.6H8.7a1.8 1.8 0 0 1-1.8-1.6L6.2 8.5z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                    </svg>
                    <?php if ((int) $cartCount > 0): ?>
                        <span class="cart-count"><?php echo (int) $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

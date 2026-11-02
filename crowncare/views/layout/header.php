<?php
// core.php should already be loaded before this header
require_once __DIR__ . '/../../controllers/CartController.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

$cartController = new CartController();
$headerProductController = new ProductController();
$cartCustomerId = get_customer_id();

$cartCount = $cartCustomerId > 0
    ? $cartController->getCartCount($cartCustomerId)
    : 0;

$headerCategories = $headerProductController->getAllCategories();
$headerSearchQuery = trim($_GET['user_query'] ?? '');
$headerCategoryId = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;
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
        href="<?php echo BASE_URL; ?>css/style.css?v=20261102-cart"
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
            action="<?php echo BASE_URL; ?>index.php"
            method="GET"
            role="search"
        >
            <label class="visually-hidden" for="header-search-input">Search products</label>
            <input
                id="header-search-input"
                type="search"
                name="user_query"
                placeholder="Search for oils, shampoos, leave-ins..."
                value="<?php echo htmlspecialchars($headerSearchQuery, ENT_QUOTES, 'UTF-8'); ?>"
            >
            <button type="submit" aria-label="Search">
                <span aria-hidden="true">⌕</span>
            </button>
        </form>

        <div class="header-actions">
            <?php if (is_logged_in()): ?>
                <a class="header-link" href="<?php echo BASE_URL; ?>views/account/my_account.php">
                    Hi, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>
                </a>
                <a class="header-link" href="<?php echo BASE_URL; ?>logout.php">Sign out</a>
            <?php else: ?>
                <a class="header-link" href="<?php echo BASE_URL; ?>views/login.php">Sign in</a>
            <?php endif; ?>

            <?php if (is_admin()): ?>
                <a class="header-link" href="<?php echo BASE_URL; ?>views/admin/product.php">Admin</a>
            <?php endif; ?>

            <a class="header-cart" href="<?php echo BASE_URL; ?>views/cart.php" aria-label="Cart, <?php echo (int) $cartCount; ?> items">
                <span class="cart-count"><?php echo (int) $cartCount; ?></span>
                Cart
            </a>
        </div>
    </div>

    <nav class="header-nav" aria-label="Categories">
        <div class="header-shell header-nav-row">
            <a
                class="<?php echo $headerCategoryId === 0 ? 'is-active' : ''; ?>"
                href="<?php echo BASE_URL; ?>index.php"
            >
                All products
            </a>

            <?php foreach ($headerCategories as $category): ?>
                <a
                    class="<?php echo $headerCategoryId === (int) $category['cat_id'] ? 'is-active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>index.php?cat=<?php echo (int) $category['cat_id']; ?>"
                >
                    <?php echo htmlspecialchars($category['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
</header>

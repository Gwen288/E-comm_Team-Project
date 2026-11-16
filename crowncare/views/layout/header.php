
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

$currentPage = basename($_SERVER['PHP_SELF']);
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
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Instrument+Sans:wght@400;500;600&family=Nunito+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>css/style.css?v=20261105-stickyfoot"
    >
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/cms-site.css?v=20261116">
</head>

<body>

<?php
/* Tabs in the order of the CMS (Wix) menu. Journal has no page yet, so it does not link anywhere. */
$navItems = [
    ['label' => 'Home', 'href' => BASE_URL . 'index.php', 'pages' => ['index.php', 'home.php']],
    ['label' => 'Shop', 'href' => BASE_URL . 'views/shop.php', 'pages' => ['shop.php', 'single_product.php', 'all_products.php', 'search_results.php']],
    ['label' => 'Hair Guide', 'href' => BASE_URL . 'views/hair_guide.php', 'pages' => ['hair_guide.php']],
    ['label' => 'Journal', 'href' => '#', 'pages' => []],
    ['label' => 'Hair Quiz', 'href' => BASE_URL . 'views/hair_quiz.php', 'pages' => ['hair_quiz.php', 'recommendations.php']],
];
?>
<header class="site-header cc-header">
    <div class="cc-header-row">

        <a href="<?php echo BASE_URL; ?>index.php" class="cc-logo">CrownCare</a>

        <?php if (!$isAdminViewer): ?>
            <nav class="cc-nav" aria-label="Main">
                <?php foreach ($navItems as $item): ?>
                    <?php $isActive = in_array($currentPage, $item['pages'], true); ?>
                    <a
                        href="<?php echo $item['href']; ?>"
                        class="<?php echo $isActive ? 'is-active' : ''; ?>"
                        <?php echo $isActive ? 'aria-current="page"' : ''; ?>
                        <?php echo $item['href'] === '#' ? 'onclick="return false;"' : ''; ?>
                    ><?php echo $item['label']; ?></a>
                <?php endforeach; ?>
            </nav>
        <?php else: ?>
            <div class="cc-nav" aria-hidden="true"></div>
        <?php endif; ?>

        <form
            class="cc-search"
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
            <button type="submit" aria-label="Search">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
                    <circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="2"></circle>
                    <path d="M16.5 16.5L21 21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                </svg>
            </button>
        </form>

        <div class="cc-actions">
            <?php if ($isAdminViewer): ?>
                <a class="cc-text-link" href="<?php echo BASE_URL; ?>views/admin/index.php">Dashboard</a>
                <a class="cc-text-link" href="<?php echo BASE_URL; ?>logout.php">Sign out</a>
            <?php else: ?>
                <a
                    class="cc-icon-link"
                    href="<?php echo BASE_URL; ?><?php echo is_logged_in() ? 'views/account/my_account.php' : 'views/login.php'; ?>"
                    aria-label="<?php echo is_logged_in() ? 'My account' : 'Log in'; ?>"
                    title="<?php echo is_logged_in() ? 'My account' : 'Log in'; ?>"
                >
                    <svg viewBox="0 0 40 40" width="40" height="40" aria-hidden="true" focusable="false">
                        <circle cx="20" cy="20" r="19" fill="currentColor"></circle>
                        <circle cx="20" cy="15.5" r="6" fill="#FDF6E8"></circle>
                        <path d="M8.5 31.5c2.3-5.2 6.3-7.8 11.5-7.8s9.2 2.6 11.5 7.8A17 17 0 0 1 8.5 31.5z" fill="#FDF6E8"></path>
                    </svg>
                </a>

                <a
                    class="cc-icon-link"
                    href="<?php echo BASE_URL; ?>views/cart.php"
                    aria-label="Cart, <?php echo (int) $cartCount; ?> items"
                    title="Cart"
                >
                    <svg viewBox="0 0 36 44" width="36" height="44" aria-hidden="true" focusable="false">
                        <path d="M11 15v-4.5a7 7 0 0 1 14 0V15" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"></path>
                        <path d="M3.5 14h29l-1.6 27.5H5.1z" fill="currentColor"></path>
                    </svg>
                    <span class="cc-cart-count"><?php echo (int) $cartCount; ?></span>
                </a>
            <?php endif; ?>
        </div>

    </div>
</header>
<script>
// Lets the home page hero fit the screen exactly below the header.
(function () {
    var header = document.querySelector('.cc-header');
    function setHeight() {
        document.documentElement.style.setProperty('--cc-header-h', header.offsetHeight + 'px');
    }
    setHeight();
    window.addEventListener('resize', setHeight);
})();
</script>

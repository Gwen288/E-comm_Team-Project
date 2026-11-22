
<?php
// core.php should already be loaded before this header.
// Pages may set $pageTitle and $pageDescription before including it.

// Consultants only use the consultant area.
keep_consultants_in_their_area();

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
    <title><?php echo e(isset($pageTitle) && $pageTitle !== '' ? $pageTitle . ' | CrownCare' : 'CrownCare | Natural Hair Care'); ?></title>

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
    <link
    rel="stylesheet"
    href="<?php echo BASE_URL; ?>css/crowncare_additions.css?v=20261109"
>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/cms-site.css?v=20261116-consult">
<?php require __DIR__ . '/head_assets.php'; ?>
</head>

<body>
<a class="cc-skip-link" href="#main-content">Skip to main content</a>
<?php require __DIR__ . '/flash.php'; ?>

<?php
/*
 * Main menu. Single links and groups; a group opens a dropdown on hover,
 * on keyboard focus, or when its button is selected (touch screens).
 */
$navItems = [
    ['label' => 'Home', 'href' => BASE_URL . 'index.php', 'pages' => ['index.php', 'home.php']],
    ['label' => 'Shop', 'href' => BASE_URL . 'views/shop.php', 'pages' => ['shop.php', 'single_product.php']],
    ['label' => 'Hair Quiz', 'href' => BASE_URL . 'views/hair_quiz.php', 'pages' => ['hair_quiz.php', 'recommendations.php']],
    ['label' => 'Learn', 'id' => 'cc-menu-learn', 'children' => [
        ['label' => 'Hair guide', 'href' => BASE_URL . 'views/hair_guide.php', 'pages' => ['hair_guide.php']],
        ['label' => 'Journal', 'href' => BASE_URL . 'views/journal.php', 'pages' => ['journal.php']],
    ]],
    ['label' => 'Book a consultation', 'href' => BASE_URL . 'views/book_consultation.php', 'pages' => ['book_consultation.php', 'booking_confirmation.php']],
    ['label' => 'Company', 'id' => 'cc-menu-company', 'children' => [
        ['label' => 'About CrownCare', 'href' => BASE_URL . 'views/pages/about.php', 'pages' => ['about.php']],
        ['label' => 'Contact us', 'href' => BASE_URL . 'views/pages/contact.php', 'pages' => ['contact.php']],
        ['label' => 'FAQ', 'href' => BASE_URL . 'views/pages/faq.php', 'pages' => ['faq.php']],
        ['label' => 'Delivery and returns', 'href' => BASE_URL . 'views/pages/shipping.php', 'pages' => ['shipping.php']],
        ['label' => 'Privacy policy', 'href' => BASE_URL . 'views/pages/privacy.php', 'pages' => ['privacy.php']],
        ['label' => 'Terms of use', 'href' => BASE_URL . 'views/pages/terms.php', 'pages' => ['terms.php']],
    ]],
];

$isCustomerViewer = is_logged_in() && !$isAdminViewer;
?>
<header class="site-header cc-header">
    <div class="cc-header-row">

        <a href="<?php echo BASE_URL; ?>index.php" class="cc-logo">CrownCare</a>

        <?php if (!$isAdminViewer): ?>
            <nav class="cc-nav" id="cc-main-nav" aria-label="Main">
                <?php foreach ($navItems as $item): ?>
                    <?php if (empty($item['children'])): ?>
                        <?php $isActive = in_array($currentPage, $item['pages'], true); ?>
                        <a
                            href="<?php echo $item['href']; ?>"
                            class="cc-nav-link<?php echo $isActive ? ' is-active' : ''; ?>"
                            <?php echo $isActive ? 'aria-current="page"' : ''; ?>
                        ><?php echo e($item['label']); ?></a>
                    <?php else: ?>
                        <?php
                        $groupActive = false;
                        foreach ($item['children'] as $child) {
                            if (in_array($currentPage, $child['pages'], true)) {
                                $groupActive = true;
                            }
                        }
                        ?>
                        <div class="cc-nav-group<?php echo $groupActive ? ' is-active' : ''; ?>" data-cc-dropdown>
                            <button
                                type="button"
                                class="cc-nav-trigger"
                                aria-expanded="false"
                                aria-controls="<?php echo e($item['id']); ?>"
                            ><?php echo e($item['label']); ?><span class="cc-nav-caret" aria-hidden="true"></span></button>
                            <div class="cc-nav-menu" id="<?php echo e($item['id']); ?>">
                                <?php foreach ($item['children'] as $child): ?>
                                    <?php $isActive = in_array($currentPage, $child['pages'], true); ?>
                                    <a
                                        href="<?php echo $child['href']; ?>"
                                        class="<?php echo $isActive ? 'is-active' : ''; ?>"
                                        <?php echo $isActive ? 'aria-current="page"' : ''; ?>
                                    ><?php echo e($child['label']); ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
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
                placeholder="Search products"
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
                <?php $signoutClass = 'cc-text-link'; require __DIR__ . '/signout_form.php'; ?>
            <?php else: ?>
                <a
                    class="cc-icon-link cc-account-link"
                    href="<?php echo BASE_URL; ?><?php echo is_logged_in() ? 'views/account/my_account.php' : 'views/login.php'; ?>"
                    title="<?php echo is_logged_in() ? 'My account' : 'Log in or create an account'; ?>"
                    aria-label="<?php echo is_logged_in() ? 'My account' : 'Log in'; ?>"
                >
                    <svg class="cc-account-icon" viewBox="0 0 40 40" width="26" height="26" aria-hidden="true" focusable="false">
                        <circle cx="20" cy="20" r="19" fill="currentColor"></circle>
                        <circle cx="20" cy="15.5" r="6" fill="#FDF6E8"></circle>
                        <path d="M8.5 31.5c2.3-5.2 6.3-7.8 11.5-7.8s9.2 2.6 11.5 7.8A17 17 0 0 1 8.5 31.5z" fill="#FDF6E8"></path>
                    </svg>
                    <span class="cc-account-label"><?php echo is_logged_in() ? 'My Account' : 'Log In'; ?></span>
                </a>

                <a
                    class="cc-icon-link cc-account-link"
                    href="<?php echo BASE_URL; ?>views/cart.php"
                    aria-label="Cart, <?php echo (int) $cartCount; ?> items"
                    title="Cart"
                >
                    <span class="cc-cart-icon">
                        <svg viewBox="0 0 36 44" width="22" height="27" aria-hidden="true" focusable="false">
                            <path d="M11 15v-4.5a7 7 0 0 1 14 0V15" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"></path>
                            <path d="M3.5 14h29l-1.6 27.5H5.1z" fill="currentColor"></path>
                        </svg>
                        <span class="cc-cart-count"><?php echo (int) $cartCount; ?></span>
                    </span>
                    <span class="cc-account-label">Cart</span>
                </a>

                <?php if ($isCustomerViewer): ?>
                    <?php $signoutClass = 'cc-text-link cc-header-signout'; require __DIR__ . '/signout_form.php'; ?>
                <?php endif; ?>

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

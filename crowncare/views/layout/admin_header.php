<?php
// Admin layout for every page under views/admin/. Only admins get in;
// everyone else is sent back to sign in with a message.
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error'] = 'You do not have permission to access the admin area.';
    redirect(BASE_URL . 'views/login.php');
}

// The current file (and ?edit_id=) decides which sidebar group and link are active.
$adminPage = basename($_SERVER['PHP_SELF'] ?? '');
$adminQuery = $_GET ?? [];

$productsOpen = in_array($adminPage, ['product.php', 'product_form.php'], true);
$categoriesOpen = in_array($adminPage, ['category.php', 'category_form.php'], true);
$salesOpen = in_array($adminPage, ['orders.php', 'order_view.php', 'customers.php'], true);
$dashboardOpen = in_array($adminPage, ['index.php', 'analytics.php'], true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(($pageTitle ?? 'Dashboard') . ' | CrownCare Admin'); ?></title>
    <meta name="robots" content="noindex">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Instrument+Sans:wght@400;500;600&family=Nunito+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>css/style.css?v=20261118-admin-filter-stable-actions"
    >
    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>css/cms-site.css?v=20261118-admin-layout"
    >
<?php require __DIR__ . '/head_assets.php'; ?>
</head>

<body class="admin-body">
<?php require __DIR__ . '/flash.php'; ?>

<div class="admin-shell">

    <aside class="admin-sidebar" id="admin-sidebar">
        <button
            class="admin-sidebar-close"
            type="button"
            aria-label="Close navigation"
            data-admin-sidebar-close
        >
            &times;
        </button>

        <a class="admin-brand" href="<?php echo BASE_URL; ?>views/admin/index.php">
            CrownCare
            <span>Admin</span>
        </a>

        <nav class="admin-nav" aria-label="Admin" data-admin-accordion>
            <div class="admin-nav-group <?php echo $dashboardOpen ? 'is-open' : ''; ?>">
                <button
                    type="button"
                    class="admin-nav-parent <?php echo $adminPage === 'index.php' ? 'is-active' : ''; ?>"
                    aria-expanded="<?php echo $dashboardOpen ? 'true' : 'false'; ?>"
                >
                    Dashboard
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <a
                        class="<?php echo $adminPage === 'index.php' ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/index.php"
                    >
                        Overview
                    </a>
                    <a
                        class="<?php echo $adminPage === 'analytics.php' ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/analytics.php"
                    >
                        Analytics
                    </a>
                </div>
            </div>

            <div class="admin-nav-group <?php echo $productsOpen ? 'is-open' : ''; ?>">
                <button
                    type="button"
                    class="admin-nav-parent"
                    aria-expanded="<?php echo $productsOpen ? 'true' : 'false'; ?>"
                >
                    Products
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <a
                        class="<?php echo $adminPage === 'product.php' ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/product.php"
                    >
                        All products
                    </a>
                    <a
                        class="<?php echo ($adminPage === 'product_form.php' && empty($adminQuery['edit_id'])) ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/product_form.php"
                    >
                        Add product
                    </a>
                </div>
            </div>

            <div class="admin-nav-group <?php echo $categoriesOpen ? 'is-open' : ''; ?>">
                <button
                    type="button"
                    class="admin-nav-parent"
                    aria-expanded="<?php echo $categoriesOpen ? 'true' : 'false'; ?>"
                >
                    Categories
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <a
                        class="<?php echo $adminPage === 'category.php' ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/category.php"
                    >
                        All categories
                    </a>
                    <a
                        class="<?php echo ($adminPage === 'category_form.php' && empty($adminQuery['edit_id'])) ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/category_form.php"
                    >
                        Add category
                    </a>
                </div>
            </div>

            <div class="admin-nav-group <?php echo $salesOpen ? 'is-open' : ''; ?>">
                <button
                    type="button"
                    class="admin-nav-parent"
                    aria-expanded="<?php echo $salesOpen ? 'true' : 'false'; ?>"
                >
                    Sales
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <a
                        class="<?php echo in_array($adminPage, ['orders.php', 'order_view.php'], true) ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/orders.php"
                    >
                        Orders
                    </a>
                    <a
                        class="<?php echo $adminPage === 'customers.php' ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/customers.php"
                    >
                        Customers
                    </a>
                </div>
            </div>
        </nav>
    </aside>

    <div class="admin-sidebar-backdrop" aria-hidden="true" data-admin-sidebar-backdrop></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button
                class="admin-sidebar-toggle"
                type="button"
                aria-label="Open navigation"
                aria-controls="admin-sidebar"
                aria-expanded="false"
                data-admin-sidebar-toggle
            >
                <span class="admin-sidebar-toggle-bars" aria-hidden="true"></span>
            </button>

            <span class="admin-topbar-user">
                Welcome, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Admin'); ?>
            </span>
            <nav class="admin-topbar-actions" aria-label="Admin quick links">
                <a class="admin-topbar-link" href="<?php echo BASE_URL; ?>index.php">View storefront</a>
                <?php $signoutClass = 'admin-topbar-link'; require __DIR__ . '/signout_form.php'; ?>
            </nav>
        </header>
        <div class="admin-content">

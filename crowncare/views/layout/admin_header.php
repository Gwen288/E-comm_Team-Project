<?php
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error'] = 'You do not have permission to access the admin area.';
    redirect(BASE_URL . 'views/login.php');
}

$adminPage = basename($_SERVER['PHP_SELF'] ?? '');
$adminQuery = $_GET ?? [];

$productsOpen = in_array($adminPage, ['product.php', 'product_form.php'], true);
$categoriesOpen = in_array($adminPage, ['category.php', 'category_form.php'], true);
$brandsOpen = in_array($adminPage, ['brand.php', 'brand_form.php'], true);
$salesOpen = in_array($adminPage, ['orders.php', 'order_view.php', 'customers.php'], true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrownCare Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Nunito+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?php echo BASE_URL; ?>css/style.css?v=20261105-admin-accordion"
    >
</head>

<body class="admin-body">

<div class="admin-shell">

    <aside class="admin-sidebar">
        <a class="admin-brand" href="<?php echo BASE_URL; ?>views/admin/index.php">
            CrownCare
            <span>Admin</span>
        </a>

        <nav class="admin-nav" aria-label="Admin" data-admin-accordion>
            <a
                class="<?php echo $adminPage === 'index.php' ? 'is-active' : ''; ?>"
                href="<?php echo BASE_URL; ?>views/admin/index.php"
            >
                Dashboard
            </a>

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
                        class="<?php echo $adminPage === 'product_form.php' ? 'is-active' : ''; ?>"
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

            <div class="admin-nav-group <?php echo $brandsOpen ? 'is-open' : ''; ?>">
                <button
                    type="button"
                    class="admin-nav-parent"
                    aria-expanded="<?php echo $brandsOpen ? 'true' : 'false'; ?>"
                >
                    Brands
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <a
                        class="<?php echo $adminPage === 'brand.php' ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/brand.php"
                    >
                        All brands
                    </a>
                    <a
                        class="<?php echo ($adminPage === 'brand_form.php' && empty($adminQuery['edit_id'])) ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>views/admin/brand_form.php"
                    >
                        Add brand
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

            <div class="admin-nav-group">
                <button
                    type="button"
                    class="admin-nav-parent"
                    aria-expanded="false"
                >
                    Store
                    <span class="admin-nav-chevron" aria-hidden="true"></span>
                </button>
                <div class="admin-nav-sub">
                    <a href="<?php echo BASE_URL; ?>index.php">View storefront</a>
                    <a href="<?php echo BASE_URL; ?>logout.php">Sign out</a>
                </div>
            </div>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <strong>CrownCare</strong>
                <span>Manage store</span>
            </div>
            <div class="admin-topbar-user">
                Welcome, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Admin'); ?>
            </div>
        </header>

        <div class="admin-content">

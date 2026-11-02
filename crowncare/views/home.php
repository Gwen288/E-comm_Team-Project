<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

/* Read the search and filter values. */
$searchQuery = trim($_GET['user_query'] ?? '');
$categoryId = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;
$brandId = isset($_GET['brand']) ? (int) $_GET['brand'] : 0;

/* Load the appropriate starting product list. */
if ($searchQuery !== '') {
    $products = $controller->searchProducts($searchQuery);
    $pageTitle = 'Search results';
} elseif ($categoryId > 0 || $brandId > 0) {
    $products = $controller->getAllProducts();
    $pageTitle = 'Shop';
} else {
    $products = $controller->getFeaturedProducts(12);
    $pageTitle = 'Featured finds';
}

/* Apply category and brand filters together when selected. */
if ($categoryId > 0) {
    $products = array_filter($products, function ($product) use ($categoryId) {
        return isset($product['product_cat'])
            && (int) $product['product_cat'] === $categoryId;
    });
}

if ($brandId > 0) {
    $products = array_filter($products, function ($product) use ($brandId) {
        return isset($product['product_brand'])
            && (int) $product['product_brand'] === $brandId;
    });
}

$products = array_values($products);

$categories = $controller->getAllCategories();
$brands = $controller->getAllBrands();

if ($searchQuery !== '') {
    $pageTitle = 'Search results';
} elseif ($categoryId > 0 && $brandId > 0) {
    $pageTitle = 'Filtered products';
} elseif ($categoryId > 0) {
    $pageTitle = 'Shop by category';
} elseif ($brandId > 0) {
    $pageTitle = 'Shop by brand';
}
?>

<?php require_once __DIR__ . '/layout/header.php'; ?>

<main class="shop-page">

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message">
            <?php
            echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?php
            echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <section class="hero-band">
        <div class="hero-copy">
            <p class="eyebrow">CrownCare</p>
            <h1>Hair care that feels made for you</h1>
            <p>
                Soft shea-inspired formulas and everyday essentials for
                textured hair — browse calmly, buy with confidence.
            </p>
        </div>
    </section>

    <?php if (!empty($categories)): ?>
        <section class="explore-section">
            <div class="section-heading">
                <h2>Shop our categories</h2>
            </div>

            <div class="explore-row">
                <?php foreach ($categories as $category): ?>
                    <a
                        class="explore-card"
                        href="<?php echo BASE_URL; ?>index.php?cat=<?php echo (int) $category['cat_id']; ?>"
                    >
                        <span class="explore-swatch" aria-hidden="true"></span>
                        <strong><?php echo htmlspecialchars($category['cat_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($brands)): ?>
        <section class="brand-strip">
            <span class="brand-strip-label">Brands</span>
            <div class="brand-chips">
                <a
                    class="chip <?php echo $brandId === 0 ? 'is-active' : ''; ?>"
                    href="<?php echo BASE_URL; ?>index.php<?php echo $categoryId ? ('?cat=' . $categoryId) : ''; ?>"
                >
                    All
                </a>
                <?php foreach ($brands as $brand): ?>
                    <a
                        class="chip <?php echo $brandId === (int) $brand['brand_id'] ? 'is-active' : ''; ?>"
                        href="<?php echo BASE_URL; ?>index.php?brand=<?php echo (int) $brand['brand_id']; ?><?php echo $categoryId ? ('&cat=' . $categoryId) : ''; ?>"
                    >
                        <?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="products-section">
        <div class="products-heading">
            <h2><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
            <span><?php echo count($products); ?> items</span>
        </div>

        <?php if (!empty($products)): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <a
                            class="product-image-link"
                            href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>"
                        >
                            <?php
                            $image = trim($product['product_image'] ?? '');
                            $imagePath = __DIR__ . '/../images/products/' . basename($image);
                            ?>

                            <?php if ($image !== '' && is_file($imagePath)): ?>
                                <img
                                    src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($image)); ?>"
                                    alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="product-image"
                                >
                            <?php else: ?>
                                <div class="product-no-image">Photo coming soon</div>
                            <?php endif; ?>
                        </a>

                        <div class="product-card-content">
                            <h3>
                                <a href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>">
                                    <?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h3>

                            <p class="product-price">
                                GH₵ <?php echo number_format((float) $product['product_price'], 2); ?>
                            </p>

                            <a
                                class="add-cart-btn"
                                href="<?php echo BASE_URL; ?>actions/add_to_cart_action.php?add_cart=<?php echo (int) $product['product_id']; ?>"
                            >
                                Add to cart
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <h3>No products found</h3>
                <p>Try another search or browse all products.</p>
                <a href="<?php echo BASE_URL; ?>index.php">View all products</a>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

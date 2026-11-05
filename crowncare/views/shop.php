<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$searchQuery = trim($_GET['user_query'] ?? '');
$categoryId = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;
$brandId = isset($_GET['brand']) ? (int) $_GET['brand'] : 0;

if ($searchQuery !== '') {
    $products = $controller->searchProducts($searchQuery);
} else {
    $products = $controller->getAllProducts();
}

if ($categoryId > 0) {
    $products = array_values(array_filter($products, function ($product) use ($categoryId) {
        return isset($product['product_cat'])
            && (int) $product['product_cat'] === $categoryId;
    }));
}

if ($brandId > 0) {
    $products = array_values(array_filter($products, function ($product) use ($brandId) {
        return isset($product['product_brand'])
            && (int) $product['product_brand'] === $brandId;
    }));
}

$categories = $controller->getAllCategories();
$shopBase = BASE_URL . 'views/shop.php';
?>

<?php require_once __DIR__ . '/layout/header.php'; ?>

<main class="shop-page shop-catalog">

    <?php if (!empty($_SESSION['cart_message'])): ?>
        <div class="cart-message">
            <?php
            echo htmlspecialchars($_SESSION['cart_message'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['cart_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if ($searchQuery !== ''): ?>
        <p class="shop-search-note">
            Results for “<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>”
            · <?php echo count($products); ?> items
        </p>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>
        <nav class="quiet-cats" aria-label="Categories">
            <a
                class="<?php echo $categoryId === 0 && $brandId === 0 && $searchQuery === '' ? 'is-active' : ''; ?>"
                href="<?php echo $shopBase; ?>"
            >
                All
            </a>
            <?php foreach ($categories as $category): ?>
                <a
                    class="<?php echo $categoryId === (int) $category['cat_id'] ? 'is-active' : ''; ?>"
                    href="<?php echo $shopBase; ?>?cat=<?php echo (int) $category['cat_id']; ?>"
                >
                    <?php echo htmlspecialchars($category['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

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
                            GH₵<?php echo number_format((float) $product['product_price'], 2); ?>
                        </p>

                        <?php if (!(is_logged_in() && is_admin())): ?>
                            <a
                                class="product-add-link"
                                href="<?php echo BASE_URL; ?>actions/add_to_cart_action.php?add_cart=<?php echo (int) $product['product_id']; ?>"
                            >
                                Add to cart
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-products surface-panel">
            <h3>No products found</h3>
            <p>Try another search or browse everything.</p>
            <a href="<?php echo $shopBase; ?>">View all products</a>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

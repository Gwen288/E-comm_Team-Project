
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$bounds = $controller->getPriceBounds();

$floor = (float) $bounds['min_price'];
$ceiling = (float) $bounds['max_price'];

if ($ceiling <= $floor) {
    $ceiling = $floor + 100;
}

$min = isset($_GET['min_price'])
    ? max($floor, min($ceiling, (float) $_GET['min_price']))
    : $floor;

$max = isset($_GET['max_price'])
    ? max($floor, min($ceiling, (float) $_GET['max_price']))
    : $ceiling;

if ($min > $max) {
    $swap = $min;
    $min = $max;
    $max = $swap;
}

$filters = [
    'query' => trim($_GET['user_query'] ?? ''),
    'category' => (int) ($_GET['cat'] ?? 0),
    'main_category' => (int) ($_GET['main'] ?? 0),
    'brand' => (int) ($_GET['brand'] ?? 0),
    'size' => (int) ($_GET['size'] ?? 0),
    'min_price' => $min,
    'max_price' => $max,
    'sort' => $_GET['sort'] ?? 'recommendation'
];

$products = $controller->getShopProducts($filters);

$categories = $controller->getAllCategories();
$mainCategories = $controller->getMainCategories();
$sizes = $controller->getAvailableShopSizes();
$slides = $controller->getFeaturedProducts(5);

$shopUrl = BASE_URL . 'views/shop.php';

function shop_query_url($changes = [])
{
    $params = $_GET;

    foreach ($changes as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }

    return BASE_URL . 'views/shop.php' .
        (count($params) ? '?' . http_build_query($params) : '');
}
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

    <!-- FEATURED PRODUCT SLIDESHOW -->
    <section class="shop-featured">

        <div class="shop-featured-heading">
            <div>
                <p class="eyebrow">A little care for your crown</p>
                <h1>Find your hair-care essentials</h1>
            </div>

            <a class="landing-text-link" href="#catalogue">
                Explore the catalogue
            </a>
        </div>

        <?php if ($slides): ?>

            <div class="shop-slideshow" data-slideshow>

                <?php foreach ($slides as $index => $item): ?>

                    <?php
                    $img = trim($item['product_image'] ?? '');
                    $imgPath = __DIR__ . '/../images/products/' . basename($img);
                    ?>

                    <article
                        class="shop-slide <?php echo $index === 0 ? 'is-active' : ''; ?>"
                        data-slide
                        <?php echo $index === 0 ? '' : 'hidden'; ?>
                    >

                        <a
                            class="shop-slide-image"
                            href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $item['product_id']; ?>"
                        >

                            <?php if ($img !== '' && is_file($imgPath)): ?>
                                <img
                                    src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($img)); ?>"
                                    alt="<?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                            <?php else: ?>
                                <span>Product image coming soon</span>
                            <?php endif; ?>

                        </a>

                        <div class="shop-slide-copy">
                            <span class="eyebrow">Featured find</span>

                            <h2>
                                <?php echo htmlspecialchars($item['product_title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h2>

                            <p>
                                GH₵<?php echo number_format((float) $item['product_price'], 2); ?>
                            </p>

                            <a
                                class="hero-btn-primary"
                                href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $item['product_id']; ?>"
                            >
                                View product
                            </a>
                        </div>

                    </article>

                <?php endforeach; ?>

                <div class="shop-slide-controls">
                    <button type="button" data-slide-prev aria-label="Previous featured product">‹</button>
                    <span data-slide-count>1 / <?php echo count($slides); ?></span>
                    <button type="button" data-slide-next aria-label="Next featured product">›</button>
                </div>

            </div>

        <?php else: ?>

            <div class="shop-slide-empty">
                New products will appear here as they are added.
            </div>

        <?php endif; ?>

    </section>


    <!-- CATALOGUE -->
    <section class="shop-catalogue" id="catalogue">

        <aside class="shop-filters">

            <div class="filter-heading">
                <h2>Filters</h2>
                <a href="<?php echo $shopUrl; ?>">Clear all</a>
            </div>

            <!-- MAIN CATEGORIES -->
            <div class="filter-group">
                <h3>Browse by</h3>

                <a
                    class="filter-option <?php echo !$filters['main_category'] ? 'selected' : ''; ?>"
                    href="<?php echo shop_query_url(['main' => null, 'cat' => null]); ?>"
                >
                    All products
                </a>

                <?php foreach ($mainCategories as $cat): ?>
                    <a
                        class="filter-option <?php echo $filters['main_category'] === (int) $cat['cat_id'] ? 'selected' : ''; ?>"
                        href="<?php echo shop_query_url(['main' => (int) $cat['cat_id'], 'cat' => null]); ?>"
                    >
                        <?php echo htmlspecialchars($cat['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>

            </div>


            <!-- ALL CATEGORIES AND SUBCATEGORIES -->
            <div class="filter-group">
                <h3>Filter by category</h3>

                <?php foreach ($categories as $cat): ?>

                    <a
                        class="filter-option category-option <?php echo $filters['category'] === (int) $cat['cat_id'] ? 'selected' : ''; ?>"
                        href="<?php echo shop_query_url(['cat' => (int) $cat['cat_id'], 'main' => null]); ?>"
                    >
                        <?php echo !empty($cat['parent_category_id']) ? '↳ ' : ''; ?>
                        <?php echo htmlspecialchars($cat['cat_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>

                <?php endforeach; ?>

            </div>


            <!-- DUAL-HANDLE PRICE SLIDER -->
            <details
                class="filter-group price-filter"
                <?php echo isset($_GET['min_price']) || isset($_GET['max_price']) ? 'open' : ''; ?>
            >

                <summary>
                    <span>Price</span>
                    <span class="filter-plus">+</span>
                </summary>

                <form
                    method="GET"
                    action="<?php echo $shopUrl; ?>"
                    class="price-range-form"
                >

                    <?php foreach ($_GET as $key => $value): ?>

                        <?php
                        if (
                            in_array($key, ['min_price', 'max_price'], true) ||
                            is_array($value)
                        ) {
                            continue;
                        }
                        ?>

                        <input
                            type="hidden"
                            name="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"
                            value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>"
                        >

                    <?php endforeach; ?>

                    <div
                        class="dual-range"
                        data-dual-range
                        data-floor="<?php echo $floor; ?>"
                        data-ceiling="<?php echo $ceiling; ?>"
                    >

                        <div class="range-track">
                            <div class="range-selected" data-range-selected></div>
                        </div>

                        <input
                            type="range"
                            name="min_price"
                            min="<?php echo $floor; ?>"
                            max="<?php echo $ceiling; ?>"
                            step="1"
                            value="<?php echo $min; ?>"
                            data-min-range
                            aria-label="Minimum price"
                        >

                        <input
                            type="range"
                            name="max_price"
                            min="<?php echo $floor; ?>"
                            max="<?php echo $ceiling; ?>"
                            step="1"
                            value="<?php echo $max; ?>"
                            data-max-range
                            aria-label="Maximum price"
                        >

                    </div>

                    <div class="range-values">
                        <span>GH₵<strong data-min-label><?php echo number_format($min, 0); ?></strong></span>
                        <span>GH₵<strong data-max-label><?php echo number_format($max, 0); ?></strong></span>
                    </div>

                    <button class="filter-apply" type="submit">
                        Apply price
                    </button>

                </form>

            </details>


            <!-- AVAILABLE PRODUCT SIZES -->
            <div class="filter-group">
                <h3>Size</h3>

                <?php if ($sizes): ?>

                    <?php foreach ($sizes as $size): ?>

                        <a
                            class="filter-option <?php echo $filters['size'] === (int) $size['size_id'] ? 'selected' : ''; ?>"
                            href="<?php echo shop_query_url([
                                'size' => $filters['size'] === (int) $size['size_id']
                                    ? null
                                    : (int) $size['size_id']
                            ]); ?>"
                        >
                            <?php echo htmlspecialchars($size['size_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p class="filter-muted">
                        Product sizes will appear here when assigned.
                    </p>

                <?php endif; ?>

            </div>

        </aside>


        <!-- PRODUCTS -->
        <div class="shop-results">

            <div class="shop-results-top">

                <div>
                    <p class="eyebrow">The CrownCare collection</p>
                    <h2>Shop all products</h2>
                    <p>
                        <?php echo count($products); ?>
                        product<?php echo count($products) !== 1 ? 's' : ''; ?> found
                    </p>
                </div>

                <form
                    method="GET"
                    action="<?php echo $shopUrl; ?>"
                    class="sort-form"
                >

                    <?php foreach ($_GET as $key => $value): ?>

                        <?php if ($key === 'sort' || is_array($value)) continue; ?>

                        <input
                            type="hidden"
                            name="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"
                            value="<?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>"
                        >

                    <?php endforeach; ?>

                    <label for="shop-sort">Sort by</label>

                    <select
                        id="shop-sort"
                        name="sort"
                        onchange="this.form.submit()"
                    >
                        <option value="recommendation" <?php echo $filters['sort'] === 'recommendation' ? 'selected' : ''; ?>>Recommendation</option>
                        <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_high" <?php echo $filters['sort'] === 'price_high' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        <option value="price_low" <?php echo $filters['sort'] === 'price_low' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="name_az" <?php echo $filters['sort'] === 'name_az' ? 'selected' : ''; ?>>Name A-Z</option>
                        <option value="name_za" <?php echo $filters['sort'] === 'name_za' ? 'selected' : ''; ?>>Name Z-A</option>
                    </select>

                </form>

            </div>


            <?php if ($filters['query'] !== ''): ?>
                <p class="shop-search-note">
                    Search results for “<?php echo htmlspecialchars($filters['query'], ENT_QUOTES, 'UTF-8'); ?>”
                </p>
            <?php endif; ?>


            <?php if ($products): ?>

                <div class="product-grid">

                    <?php foreach ($products as $product): ?>

                        <?php
                        $image = trim($product['product_image'] ?? '');
                        $imagePath = __DIR__ . '/../images/products/' . basename($image);
                        ?>

                        <article class="product-card">

                            <a
                                class="product-image-link"
                                href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>"
                            >

                                <?php if ($image !== '' && is_file($imagePath)): ?>

                                    <img
                                        class="product-image"
                                        src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($image)); ?>"
                                        alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >

                                <?php else: ?>

                                    <div class="product-no-image">
                                        Photo coming soon
                                    </div>

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
                    <p>Try changing or clearing one of your filters.</p>
                    <a href="<?php echo $shopUrl; ?>">View all products</a>
                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

<script src="<?php echo BASE_URL; ?>js/shop.js"></script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

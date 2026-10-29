
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
    $pageTitle = 'Search Results';
} elseif ($categoryId > 0 || $brandId > 0) {
    $products = $controller->getAllProducts();
    $pageTitle = 'Filtered Products';
} else {
    $products = $controller->getFeaturedProducts(12);
    $pageTitle = 'Featured Products';
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

/* Reset array indexes after filtering. */
$products = array_values($products);

/* Load options for the search form. */
$categories = $controller->getAllCategories();
$brands = $controller->getAllBrands();

if ($searchQuery !== '') {
    $pageTitle = 'Search Results';
} elseif ($categoryId > 0 && $brandId > 0) {
    $pageTitle = 'Products by Category and Brand';
} elseif ($categoryId > 0) {
    $pageTitle = 'Products in This Category';
} elseif ($brandId > 0) {
    $pageTitle = 'Products by Brand';
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

    <div class="shop-layout">

        <!-- LEFT: Keep the existing sidebar unchanged. -->
        <?php require __DIR__ . '/layout/sidebar.php'; ?>

        <!-- RIGHT: Search and products. -->
        <section class="shop-main">

            <div class="shop-heading">
                <h1>Welcome to Shoppn</h1>
                <p>Find the products you need.</p>
            </div>

            <!-- Search and filters -->
            <div class="home-search-section">
                <form
                    class="home-search-form"
                    action="<?php echo BASE_URL; ?>index.php"
                    method="GET"
                >
                    <input
                        type="search"
                        name="user_query"
                        placeholder="Search products by name or keyword..."
                        value="<?php
                        echo htmlspecialchars(
                            $searchQuery,
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>"
                    >

                    <select name="cat" aria-label="Filter by category">
                        <option value="0">All Categories</option>

                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?php echo (int) $category['cat_id']; ?>"
                                <?php
                                echo $categoryId === (int) $category['cat_id']
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                <?php
                                echo htmlspecialchars(
                                    $category['cat_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="brand" aria-label="Filter by brand">
                        <option value="0">All Brands</option>

                        <?php foreach ($brands as $brand): ?>
                            <option
                                value="<?php echo (int) $brand['brand_id']; ?>"
                                <?php
                                echo $brandId === (int) $brand['brand_id']
                                    ? 'selected'
                                    : '';
                                ?>
                            >
                                <?php
                                echo htmlspecialchars(
                                    $brand['brand_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Search / Filter</button>

                    <a  class="filter-reset"  href="<?php echo BASE_URL; ?>index.php">
                             Clear
                    </a>
                </form>
            </div>

            <!-- Product heading -->
            <div class="products-heading">
                <h2>
                    <?php
                    echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8');
                    ?>
                </h2>

                <span>
                    <?php echo count($products); ?> product(s)
                </span>
            </div>

            <!-- Existing product grid and cards -->
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

                                $imagePath = __DIR__ . '/../images/products/'
                                    . basename($image);

                                if ($image !== '' && is_file($imagePath)):
                                ?>
                                    <img
                                        src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($image)); ?>"
                                        alt="<?php
                                        echo htmlspecialchars(
                                            $product['product_title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>"
                                        class="product-image"
                                    >
                                <?php else: ?>
                                    <div class="product-no-image">
                                        No image available
                                    </div>
                                <?php endif; ?>
                            </a>

                            <div class="product-card-content">

                                <h3>
                                    <a
                                        href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>"
                                    >
                                        <?php
                                        echo htmlspecialchars(
                                            $product['product_title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </a>
                                </h3>

                                <p class="product-price">
                                    GH₵ <?php
                                    echo number_format(
                                        (float) $product['product_price'],
                                        2
                                    );
                                    ?>
                                </p>

                                <div class="product-card-actions">

                                    <a
                                        class="view-product-btn"
                                        href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>"
                                    >
                                        View Details
                                    </a>

                                    <a href="<?php echo BASE_URL; ?>actions/add_to_cart_action.php?add_cart=<?php echo (int) $product['product_id']; ?>">
                                        Add to Cart
                                    
                                    </a>

                                </div>
                            </div>
                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="no-products">
                    <h3>No products found</h3>
                    <p>
                        Try another search term or change your filters.
                    </p>
                    <a href="<?php echo BASE_URL; ?>index.php">
                        View all products
                    </a>
                </div>

            <?php endif; ?>

        </section>
    </div>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

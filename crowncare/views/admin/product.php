<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

/* Catalogue list for admins: filter, sort, edit or delete products. */

$controller = new ProductController();
$products = $controller->getAllProducts();
$allProducts = $products;
$categories = $controller->getAllCategories();

$search = trim((string) ($_GET['search'] ?? ''));
$categoryFilter = (int) ($_GET['category'] ?? 0);
$priceFilter = (string) ($_GET['price'] ?? 'all');
$sort = (string) ($_GET['sort'] ?? 'recent');
// Unknown filter values from the address bar fall back to "all" / "recent".
$allowedPrices = ['all', 'under-50', '50-100', '100-plus'];
$allowedSorts = ['recent', 'name-asc', 'price-asc', 'price-desc'];

if (!in_array($priceFilter, $allowedPrices, true)) {
    $priceFilter = 'all';
}
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'recent';
}

// Filtering runs here for the first page load, then again in JS as the admin types.
$products = array_values(array_filter($products, function ($product) use ($search, $categoryFilter, $priceFilter) {
    $price = (float) $product['product_price'];
    $matchesSearch = $search === ''
        || stripos($product['product_title'], $search) !== false
        || stripos($product['cat_name'] ?? '', $search) !== false
        || stripos($product['product_keywords'] ?? '', $search) !== false;
    $matchesCategory = $categoryFilter === 0 || (int) $product['product_cat'] === $categoryFilter;
    $matchesPrice = $priceFilter === 'all'
        || ($priceFilter === 'under-50' && $price < 50)
        || ($priceFilter === '50-100' && $price >= 50 && $price <= 100)
        || ($priceFilter === '100-plus' && $price > 100);

    return $matchesSearch && $matchesCategory && $matchesPrice;
}));
// Every row is printed; these ids decide which ones start visible.
$matchedProductIds = array_fill_keys(array_map('intval', array_column($products, 'product_id')), true);

// "recent" just means newest id first.
usort($products, function ($a, $b) use ($sort) {
    if ($sort === 'name-asc') {
        return strcasecmp($a['product_title'], $b['product_title']);
    }
    if ($sort === 'price-asc') {
        return (float) $a['product_price'] <=> (float) $b['product_price'];
    }
    if ($sort === 'price-desc') {
        return (float) $b['product_price'] <=> (float) $a['product_price'];
    }

    return (int) $b['product_id'] <=> (int) $a['product_id'];
});

$pageTitle = 'Products';
require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header admin-catalog-page-header">
    <div>
        <h1>All products</h1>
        <p>Products currently stored in the catalogue.</p>
    </div>
    <a class="admin-btn admin-btn-primary" href="<?php echo BASE_URL; ?>views/admin/product_form.php">
        Add product
    </a>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="admin-message admin-success">
        <?php
        echo htmlspecialchars($_SESSION['success']);
        unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="admin-message admin-error">
        <?php
        echo htmlspecialchars($_SESSION['error']);
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<div class="admin-catalog-toolbar">
    <div class="admin-catalog-toolbar-inner">
    <form class="admin-catalog-filters admin-product-filters" method="GET" action="<?php echo BASE_URL; ?>views/admin/product.php" data-admin-live-filters="products">
        <label>
            <span>Search products</span>
            <input
                type="search"
                name="search"
                value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="Search products by name or keyword..."
            >
        </label>
        <label>
            <span>Category</span>
            <select name="category">
                <option value="0">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?php echo (int) $category['cat_id']; ?>"
                        <?php echo $categoryFilter === (int) $category['cat_id'] ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($category['cat_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <span>Price</span>
            <select name="price">
                <option value="all" <?php echo $priceFilter === 'all' ? 'selected' : ''; ?>>Any price</option>
                <option value="under-50" <?php echo $priceFilter === 'under-50' ? 'selected' : ''; ?>>Under GH₵50</option>
                <option value="50-100" <?php echo $priceFilter === '50-100' ? 'selected' : ''; ?>>GH₵50–100</option>
                <option value="100-plus" <?php echo $priceFilter === '100-plus' ? 'selected' : ''; ?>>Over GH₵100</option>
            </select>
        </label>
        <label>
            <span>Sort by</span>
            <select name="sort">
                <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>>Recently added</option>
                <option value="name-asc" <?php echo $sort === 'name-asc' ? 'selected' : ''; ?>>Name A–Z</option>
                <option value="price-asc" <?php echo $sort === 'price-asc' ? 'selected' : ''; ?>>Price low to high</option>
                <option value="price-desc" <?php echo $sort === 'price-desc' ? 'selected' : ''; ?>>Price high to low</option>
            </select>
        </label>
        <div class="admin-catalog-filter-actions">
            <button class="admin-btn admin-btn-primary" type="submit">Apply</button>
            <a class="admin-btn admin-btn-secondary" href="<?php echo BASE_URL; ?>views/admin/product.php" data-clear-admin-filters>Clear</a>
        </div>
    </form>
    </div>
</div>
<p class="admin-filter-count" data-admin-filter-count aria-live="polite"><?php echo count($products); ?> product<?php echo count($products) === 1 ? '' : 's'; ?> found</p>

<section class="admin-card">
    <?php if (empty($allProducts)): ?>
        <p class="admin-empty">No products have been added yet.</p>
        <a class="admin-btn admin-btn-primary" href="<?php echo BASE_URL; ?>views/admin/product_form.php">
            Add product
        </a>
    <?php else: ?>
        <p class="admin-empty" data-admin-filter-empty <?php echo empty($products) ? '' : 'hidden'; ?>>
            No products match these filters. Try changing or clearing the filters.
        </p>
        <div class="admin-table-container">
            <table class="admin-table" data-admin-filter-table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allProducts as $product): ?>
                        <?php
                        $productId = (int) $product['product_id'];
                        $productTitle = (string) $product['product_title'];
                        $productCategory = (string) ($product['cat_name'] ?? 'Unknown');
                        $productKeywords = (string) ($product['product_keywords'] ?? '');
                        $productPrice = (float) $product['product_price'];
                        ?>
                        <tr
                            data-admin-filter-row
                            data-search="<?php echo htmlspecialchars($productTitle . ' ' . $productCategory . ' ' . $productKeywords, ENT_QUOTES, 'UTF-8'); ?>"
                            data-category="<?php echo (int) $product['product_cat']; ?>"
                            data-price="<?php echo htmlspecialchars((string) $productPrice, ENT_QUOTES, 'UTF-8'); ?>"
                            data-id="<?php echo $productId; ?>"
                            <?php echo isset($matchedProductIds[$productId]) ? '' : 'hidden'; ?>
                        >
                            <td>
                                <?php
                                // The saved name alone is not enough; the file must exist.
                                $image = trim($product['product_image'] ?? '');
                                $imagePath = __DIR__ . '/../../images/products/' . basename($image);
                                ?>
                                <?php if ($image !== '' && is_file($imagePath)): ?>
                                    <img
                                        class="admin-product-thumbnail"
                                        src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($image)); ?>"
                                        alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                    >
                                <?php else: ?>
                                    <span class="admin-thumb-empty">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['product_title']); ?></td>
                            <td><?php echo htmlspecialchars($product['cat_name'] ?? 'Unknown'); ?></td>
                            <td>GH₵<?php echo number_format((float) $product['product_price'], 2); ?></td>
                            <td>
                                <div class="admin-actions admin-actions-links">
                                    <a href="<?php echo BASE_URL; ?>views/admin/product_form.php?edit_id=<?php echo (int) $product['product_id']; ?>">
                                        Edit
                                    </a>
                                    <form
                                        action="<?php echo BASE_URL; ?>actions/delete_product_action.php"
                                        method="POST"
                                        data-confirm="Delete this product?" data-confirm-text="This cannot be undone." data-confirm-button="Delete product"
                                    >
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>">
                                        <button type="submit" class="admin-link-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script>
// Same filters and sort as PHP, redone in JS so the table reacts without a reload.
(function () {
    var form = document.querySelector('[data-admin-live-filters="products"]');
    if (!form) return;

    var rows = Array.from(document.querySelectorAll('[data-admin-filter-row]'));
    var count = document.querySelector('[data-admin-filter-count]');
    var empty = document.querySelector('[data-admin-filter-empty]');
    var table = document.querySelector('[data-admin-filter-table]');
    var body = table ? table.querySelector('tbody') : null;
    if (!rows.length || !count || !empty || !table || !body) return;
    var search = form.elements.search;
    var category = form.elements.category;
    var price = form.elements.price;
    var sort = form.elements.sort;

    function updateResults() {
        var term = search.value.trim().toLocaleLowerCase();
        var shown = 0;

        rows.forEach(function (row) {
            var rowPrice = Number(row.dataset.price);
            var matches = (!term || row.dataset.search.toLocaleLowerCase().includes(term))
                && (category.value === '0' || row.dataset.category === category.value)
                && (price.value === 'all'
                    || (price.value === 'under-50' && rowPrice < 50)
                    || (price.value === '50-100' && rowPrice >= 50 && rowPrice <= 100)
                    || (price.value === '100-plus' && rowPrice > 100));
            row.hidden = !matches;
            if (matches) shown++;
        });

        rows.sort(function (a, b) {
            var left = a.querySelector('td:nth-child(2)').textContent.trim();
            var right = b.querySelector('td:nth-child(2)').textContent.trim();
            var leftPrice = Number(a.dataset.price);
            var rightPrice = Number(b.dataset.price);
            if (sort.value === 'name-asc') return left.localeCompare(right);
            if (sort.value === 'price-asc') return leftPrice - rightPrice;
            if (sort.value === 'price-desc') return rightPrice - leftPrice;
            return Number(b.dataset.id) - Number(a.dataset.id);
        });
        rows.forEach(function (row) { body.appendChild(row); });

        count.textContent = shown + ' product' + (shown === 1 ? '' : 's') + ' found';
        empty.hidden = shown !== 0;
        table.hidden = shown === 0;
    }

    var searchTimer;
    search.addEventListener('input', function () {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(updateResults, 120);
    });
    [category, price, sort].forEach(function (control) {
        control.addEventListener('change', updateResults);
    });
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        updateResults();
    });
    form.addEventListener('reset', function () {
        window.setTimeout(updateResults, 0);
    });
    form.querySelector('[data-clear-admin-filters]').addEventListener('click', function (event) {
        event.preventDefault();
        search.value = '';
        category.value = '0';
        price.value = 'all';
        sort.value = 'recent';
        updateResults();
    });
    updateResults();
})();
</script>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

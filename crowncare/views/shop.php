<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../classes/StockClass.php';

/*
 * Shop page, laid out like the CMS (Wix) shop:
 * breadcrumbs, category banner and title, a "Browse by" / "Filter by" sidebar,
 * a product count with "Sort by", and a three-column grid.
 *
 * Query string: cat (category id), type[] (product type checkboxes), size[] (size
 * checkboxes), min_price, max_price, sort, user_query (header search).
 * The older ?main= links still work.
 */

$controller = new ProductController();
$stockTotals = (new StockClass())->getAllProductTotals();

$categories = $controller->getAllCategories();
$categoriesById = [];
foreach ($categories as $cat) {
    $categoriesById[(int) $cat['cat_id']] = $cat;
}

$childrenOf = [];
foreach ($categories as $cat) {
    if (!empty($cat['parent_category_id'])) {
        $childrenOf[(int) $cat['parent_category_id']][] = $cat;
    }
}

// Main categories in the order they were added (Shampoos, Cleansers, Conditioners, ...).
$mainCategories = array_values(array_filter($categories, function ($c) {
    return empty($c['parent_category_id']);
}));
usort($mainCategories, function ($a, $b) {
    return (int) $a['cat_id'] <=> (int) $b['cat_id'];
});

$catId = (int) ($_GET['cat'] ?? ($_GET['main'] ?? 0));
$current = $categoriesById[$catId] ?? null;
if (!$current) {
    $catId = 0;
}
$parent = $current && !empty($current['parent_category_id'])
    ? ($categoriesById[(int) $current['parent_category_id']] ?? null)
    : null;

$query = trim($_GET['user_query'] ?? '');

$bounds = $controller->getPriceBounds();
$floor = floor((float) $bounds['min_price']);
$ceiling = ceil((float) $bounds['max_price']);
if ($ceiling <= $floor) {
    $ceiling = $floor + 100;
}

$min = isset($_GET['min_price']) ? max($floor, min($ceiling, (float) $_GET['min_price'])) : $floor;
$max = isset($_GET['max_price']) ? max($floor, min($ceiling, (float) $_GET['max_price'])) : $ceiling;
if ($min > $max) {
    [$min, $max] = [$max, $min];
}

$types = array_values(array_filter(array_map('intval', (array) ($_GET['type'] ?? []))));
$sizeIds = array_values(array_filter(array_map('intval', (array) ($_GET['size'] ?? []))));

$sortOptions = [
    'recommendation' => 'Recommended',
    'newest' => 'Newest',
    'price_low' => 'Price (low to high)',
    'price_high' => 'Price (high to low)',
    'name_az' => 'Name A-Z',
    'name_za' => 'Name Z-A',
];
$sort = array_key_exists($_GET['sort'] ?? '', $sortOptions) ? $_GET['sort'] : 'recommendation';

$products = $controller->getShopProducts([
    'query' => $query,
    'main_category' => $catId,
    'categories' => $types,
    'sizes' => $sizeIds,
    'min_price' => $min,
    'max_price' => $max,
    'sort' => $sort,
]);

// Sizes that are offered, in a natural order (30 ml, 50 ml, 100 ml ... then grams).
$sizes = $controller->getAvailableShopSizes();
usort($sizes, function ($a, $b) {
    preg_match('/^(\d+)\s*([a-z]+)$/i', $a['size_name'], $ma);
    preg_match('/^(\d+)\s*([a-z]+)$/i', $b['size_name'], $mb);
    return [strtolower($ma[2] ?? $a['size_name']) === 'g', (int) ($ma[1] ?? 0)]
        <=> [strtolower($mb[2] ?? $b['size_name']) === 'g', (int) ($mb[1] ?? 0)];
});

// "Product type" choices: the subcategories of the open category, or every category.
if ($current && !empty($childrenOf[$catId])) {
    $typeChoices = $childrenOf[$catId];
} elseif (!$current) {
    $typeChoices = $categories;
} else {
    $typeChoices = [];
}

$pageTitle = $query !== '' ? 'Search results' : ($current['cat_name'] ?? 'All Products');
$bannerUrl = $query !== '' ? null
    : ($current
        ? cc_category_banner($current['cat_name'], $parent['cat_name'] ?? null)
        : cc_category_banner('All Products'));

$shopUrl = BASE_URL . 'views/shop.php';

function cc_cat_url($catId)
{
    return BASE_URL . 'views/shop.php' . ($catId ? '?cat=' . (int) $catId : '');
}

$filtersActive = $types || $sizeIds || $min > $floor || $max < $ceiling;

$pageTitle = 'Shop';
$pageDescription = 'Shampoos, conditioners, hair creams, oils and styling products for natural and African hair, delivered across Accra.';
require_once __DIR__ . '/layout/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/cms-shop.css?v=20261111">

<main class="cc-page cc-shop">
    <div class="cc-shell">


        <!-- BREADCRUMBS -->
        <nav class="cc-breadcrumbs" aria-label="Breadcrumb">
            <a href="<?php echo BASE_URL; ?>index.php">Home</a>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            <?php if ($parent): ?>
                <a href="<?php echo cc_cat_url($parent['cat_id']); ?>"><?php echo cc_e($parent['cat_name']); ?></a>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
            <?php endif; ?>
            <span aria-current="page"><?php echo cc_e($pageTitle); ?></span>
        </nav>

        <!-- BANNER AND TITLE -->
        <?php if ($bannerUrl): ?>
            <div class="cc-hero-banner<?php echo $current ? '' : ' cc-hero-banner--wide'; ?>">
                <img src="<?php echo cc_e($bannerUrl); ?>" alt="">
            </div>
        <?php endif; ?>

        <h1 class="cc-category-title">
            <?php echo $query !== '' ? 'Results for &ldquo;' . cc_e($query) . '&rdquo;' : cc_e($pageTitle); ?>
        </h1>

        <div class="cc-shop-layout">

            <!-- SIDEBAR -->
            <aside class="cc-sidebar">
              <details class="cc-mobile-panel" open data-mobile-panel>
                <summary class="cc-mobile-toggle">Browse and filter</summary>

                <nav class="cc-browse" aria-label="Browse by category">
                    <h2 class="cc-side-title">Browse by</h2>
                    <ul>
                        <?php if (!$current): ?>
                            <li><a class="is-active" href="<?php echo cc_cat_url(0); ?>">All Products</a></li>
                            <?php foreach ($mainCategories as $cat): ?>
                                <li><a href="<?php echo cc_cat_url($cat['cat_id']); ?>"><?php echo cc_e($cat['cat_name']); ?></a></li>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <?php
                            // Inside a category: a link back up, then the category's own subcategories,
                            // or its siblings when it is a subcategory.
                            $backTo = $parent ?: $current;
                            $backUrl = $parent ? cc_cat_url($parent['cat_id']) : cc_cat_url(0);
                            $list = $parent ? ($childrenOf[(int) $parent['cat_id']] ?? []) : ($childrenOf[$catId] ?? []);
                            ?>
                            <?php if ($list): ?>
                                <li>
                                    <a class="cc-browse-back" href="<?php echo $backUrl; ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
                                        <?php echo cc_e($backTo['cat_name']); ?>
                                    </a>
                                </li>
                                <?php foreach ($list as $cat): ?>
                                    <li class="cc-browse-child">
                                        <a class="<?php echo (int) $cat['cat_id'] === $catId ? 'is-active' : ''; ?>" href="<?php echo cc_cat_url($cat['cat_id']); ?>"><?php echo cc_e($cat['cat_name']); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><a href="<?php echo cc_cat_url(0); ?>">All Products</a></li>
                                <?php foreach ($mainCategories as $cat): ?>
                                    <li><a class="<?php echo (int) $cat['cat_id'] === $catId ? 'is-active' : ''; ?>" href="<?php echo cc_cat_url($cat['cat_id']); ?>"><?php echo cc_e($cat['cat_name']); ?></a></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </nav>

                <form id="cc-filters" method="GET" action="<?php echo $shopUrl; ?>" data-filters>
                    <?php if ($catId): ?><input type="hidden" name="cat" value="<?php echo $catId; ?>"><?php endif; ?>
                    <?php if ($query !== ''): ?><input type="hidden" name="user_query" value="<?php echo cc_e($query); ?>"><?php endif; ?>

                    <h2 class="cc-side-title">Filter by</h2>

                    <?php if ($typeChoices): ?>
                        <details class="cc-filter" open>
                            <summary>Product type</summary>
                            <div class="cc-filter-body">
                                <?php foreach ($typeChoices as $cat): ?>
                                    <label class="cc-check">
                                        <input type="checkbox" name="type[]" value="<?php echo (int) $cat['cat_id']; ?>" <?php echo in_array((int) $cat['cat_id'], $types, true) ? 'checked' : ''; ?>>
                                        <?php echo cc_e($cat['cat_name']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endif; ?>

                    <details class="cc-filter" <?php echo ($min > $floor || $max < $ceiling) ? 'open' : ''; ?>>
                        <summary>Price</summary>
                        <div class="cc-filter-body">
                            <div class="cc-price-range" data-range data-floor="<?php echo $floor; ?>" data-ceiling="<?php echo $ceiling; ?>">
                                <span class="cc-price-fill" data-range-fill></span>
                                <input type="range" name="min_price" min="<?php echo $floor; ?>" max="<?php echo $ceiling; ?>" step="1" value="<?php echo $min; ?>" aria-label="Minimum price" data-range-min>
                                <input type="range" name="max_price" min="<?php echo $floor; ?>" max="<?php echo $ceiling; ?>" step="1" value="<?php echo $max; ?>" aria-label="Maximum price" data-range-max>
                            </div>
                            <div class="cc-price-labels">
                                <span data-min-label>GHS <?php echo (int) $min; ?></span>
                                <span data-max-label>GHS <?php echo (int) $max; ?></span>
                            </div>
                        </div>
                    </details>

                    <?php if ($sizes): ?>
                        <details class="cc-filter" <?php echo $sizeIds ? 'open' : ''; ?>>
                            <summary>Size</summary>
                            <div class="cc-filter-body">
                                <?php foreach ($sizes as $size): ?>
                                    <label class="cc-check">
                                        <input type="checkbox" name="size[]" value="<?php echo (int) $size['size_id']; ?>" <?php echo in_array((int) $size['size_id'], $sizeIds, true) ? 'checked' : ''; ?>>
                                        <?php echo cc_e(cc_size_label($size['size_name'])); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endif; ?>

                    <?php if ($filtersActive): ?>
                        <a class="cc-clear-filters" href="<?php echo $catId ? cc_cat_url($catId) : $shopUrl . ($query !== '' ? '?user_query=' . rawurlencode($query) : ''); ?>">Clear filters</a>
                    <?php endif; ?>

                    <noscript><button type="submit" class="cc-btn" style="margin-top:16px">Apply</button></noscript>
                </form>
              </details>
            </aside>

            <!-- PRODUCTS -->
            <section aria-label="Products">
                <div class="cc-results-top">
                    <p class="cc-count"><?php echo count($products); ?> product<?php echo count($products) === 1 ? '' : 's'; ?></p>

                    <label class="cc-sort">
                        Sort by:
                        <select name="sort" form="cc-filters" data-auto-submit>
                            <?php foreach ($sortOptions as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $sort === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <?php if ($products): ?>
                    <div class="cc-grid">
                        <?php foreach ($products as $product): ?>
                            <?php
                            $photos = cc_product_gallery($product);
                            $url = BASE_URL . 'views/single_product.php?pro_id=' . (int) $product['product_id'];
                            $soldOut = ($stockTotals[(int) $product['product_id']] ?? 0) <= 0;
                            ?>
                            <a class="cc-card<?php echo $soldOut ? ' is-sold-out' : ''; ?>" href="<?php echo $url; ?>">
                                <div class="cc-card-media">
                                    <?php if ($soldOut): ?>
                                        <span class="cc-sold-out-tag">Sold out</span>
                                    <?php endif; ?>
                                    <?php if ($photos): ?>
                                        <img src="<?php echo cc_e($photos[0]['src']); ?>" alt="<?php echo cc_e($product['product_title']); ?>" loading="lazy">
                                        <?php if (isset($photos[1])): ?>
                                            <img class="cc-card-hover" src="<?php echo cc_e($photos[1]['src']); ?>" alt="" loading="lazy">
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="cc-card-noimage">Photo coming soon</div>
                                    <?php endif; ?>
                                </div>
                                <div class="cc-card-details">
                                    <h3 class="cc-card-name"><?php echo cc_e($product['product_title']); ?></h3>
                                    <p class="cc-card-price"><?php echo cc_money($product['product_price']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="cc-empty">
                        <p>No products match these filters.</p>
                        <?php if ($filtersActive): ?>
                            <a href="<?php echo $catId ? cc_cat_url($catId) : $shopUrl; ?>">Clear filters</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>

        </div>
    </div>
</main>

<script>
(function () {
    // On phones the sidebar folds into a "Browse and filter" button, opened when filters are in use.
    var panel = document.querySelector('[data-mobile-panel]');
    if (panel && window.matchMedia('(max-width: 860px)').matches) {
        panel.open = <?php echo $filtersActive ? 'true' : 'false'; ?>;
    }

    var form = document.querySelector('[data-filters]');
    if (!form) return;

    function submit() {
        form.submit();
    }

    // Checkboxes and the sort menu apply straight away, as on the CMS.
    form.querySelectorAll('input[type="checkbox"]').forEach(function (box) {
        box.addEventListener('change', submit);
    });
    document.querySelectorAll('[data-auto-submit]').forEach(function (el) {
        el.addEventListener('change', submit);
    });

    // Two-handle price slider; the page updates when a handle is let go.
    var range = form.querySelector('[data-range]');
    if (range) {
        var minInput = range.querySelector('[data-range-min]');
        var maxInput = range.querySelector('[data-range-max]');
        var fill = range.querySelector('[data-range-fill]');
        var floor = Number(range.dataset.floor);
        var ceiling = Number(range.dataset.ceiling);
        var minLabel = form.querySelector('[data-min-label]');
        var maxLabel = form.querySelector('[data-max-label]');

        function paint(changed) {
            var lo = Number(minInput.value);
            var hi = Number(maxInput.value);
            if (lo > hi) {
                if (changed === minInput) { minInput.value = hi; lo = hi; }
                else { maxInput.value = lo; hi = lo; }
            }
            var span = ceiling - floor || 1;
            fill.style.left = ((lo - floor) / span * 100) + '%';
            fill.style.right = (100 - (hi - floor) / span * 100) + '%';
            minLabel.textContent = 'GHS ' + lo;
            maxLabel.textContent = 'GHS ' + hi;
        }

        [minInput, maxInput].forEach(function (input) {
            input.addEventListener('input', function () { paint(input); });
            input.addEventListener('change', submit);
        });
        paint(null);
    }
})();
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

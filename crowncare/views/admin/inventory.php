<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../classes/StockClass.php';

require_admin();

$rows = (new StockClass())->getInventory();
$lowLevel = (int) CC_LOW_STOCK;

$productIds = [];
$unitsInStock = 0;
$lowCount = 0;
$outCount = 0;

foreach ($rows as &$row) {
    $stock = (int) $row['stock_qty'];
    $productIds[(int) $row['product_id']] = true;
    $unitsInStock += $stock;

    if ($stock <= 0) {
        $row['state'] = 'out';
        $outCount++;
    } elseif ($stock <= $lowLevel) {
        $row['state'] = 'low';
        $lowCount++;
    } else {
        $row['state'] = 'ok';
    }
}
unset($row);

/** Badge for a stock level. */
function cc_inventory_badge($stock, $lowLevel)
{
    if ($stock <= 0) {
        return ['cc-status cc-status--bad', 'Out of stock'];
    }
    if ($stock <= $lowLevel) {
        return ['cc-status cc-status--wait', 'Low stock'];
    }
    return ['cc-status cc-status--good', 'In stock'];
}

$pageTitle = 'Inventory';
require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header admin-catalog-page-header">
    <div>
        <h1>Inventory</h1>
        <p>
            Stock for every product and size. Stock goes down when an order is paid
            and goes back up if a paid order is cancelled. To add a product that is
            not in the shop yet, use Add product.
        </p>
    </div>
    <a class="admin-btn admin-btn-secondary" href="<?php echo BASE_URL; ?>views/admin/product_form.php">
        Add product
    </a>
</div>

<div class="admin-stat-grid cc-inventory-stats">
    <div class="admin-stat-card">
        <span>Products</span>
        <strong><?php echo count($productIds); ?></strong>
    </div>
    <div class="admin-stat-card">
        <span>Units in stock</span>
        <strong data-total-units><?php echo number_format($unitsInStock); ?></strong>
    </div>
    <div class="admin-stat-card">
        <span>Low stock (<?php echo $lowLevel; ?> or fewer)</span>
        <strong data-count="low"><?php echo $lowCount; ?></strong>
    </div>
    <div class="admin-stat-card">
        <span>Out of stock</span>
        <strong data-count="out"><?php echo $outCount; ?></strong>
    </div>
</div>

<div class="admin-catalog-toolbar">
    <div class="admin-catalog-toolbar-inner">
        <div class="admin-catalog-filters cc-inventory-filters">
            <label>
                <span>Search</span>
                <input type="search" id="inventory-search" placeholder="Product or size" autocomplete="off">
            </label>
            <label>
                <span>Stock level</span>
                <select id="inventory-state">
                    <option value="all">All</option>
                    <option value="ok">In stock</option>
                    <option value="low">Low stock</option>
                    <option value="out">Out of stock</option>
                </select>
            </label>
        </div>
    </div>
</div>
<p class="admin-filter-count" id="inventory-count" aria-live="polite"><?php echo count($rows); ?> item<?php echo count($rows) === 1 ? '' : 's'; ?></p>

<section class="admin-card">
    <?php if (!$rows): ?>
        <p class="admin-empty">No products have been added yet.</p>
    <?php else: ?>
        <p class="admin-empty" id="inventory-empty" hidden>No items match. Try another search or stock level.</p>

        <div class="admin-table-container">
            <table class="admin-table cc-inventory-table">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Size</th>
                        <th scope="col" class="is-number">Sold</th>
                        <th scope="col" class="is-number">In stock</th>
                        <th scope="col">Status</th>
                        <th scope="col">Add stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $productId = (int) $row['product_id'];
                        $sizeId = (int) $row['size_id'];
                        $stock = (int) $row['stock_qty'];
                        $sizeLabel = $row['size_name'] !== null ? cc_size_label($row['size_name']) : 'No sizes';
                        [$badgeClass, $badgeText] = cc_inventory_badge($stock, $lowLevel);
                        $image = trim((string) ($row['product_image'] ?? ''));
                        $imageUrl = cc_product_image_url($image);
                        ?>
                        <tr
                            id="stock-<?php echo $productId; ?>-<?php echo $sizeId; ?>"
                            data-inventory-row
                            data-state="<?php echo e($row['state']); ?>"
                            data-search="<?php echo e(strtolower($row['product_title'] . ' ' . ($row['cat_name'] ?? '') . ' ' . $sizeLabel)); ?>"
                        >
                            <td>
                                <div class="cc-inventory-product">
                                    <?php if ($imageUrl): ?>
                                        <img class="admin-product-thumbnail" src="<?php echo e($imageUrl); ?>" alt="">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo e($row['product_title']); ?></strong>
                                        <small><?php echo e($row['cat_name'] ?? ''); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo e($sizeLabel); ?></td>
                            <td class="is-number"><?php echo (int) $row['sold']; ?></td>
                            <td class="is-number"><strong data-stock><?php echo $stock; ?></strong></td>
                            <td><span class="<?php echo $badgeClass; ?>" data-badge><?php echo $badgeText; ?></span></td>
                            <td>
                                <form class="cc-stock-form" action="<?php echo BASE_URL; ?>actions/add_stock_action.php" method="POST" data-add-stock>
                                    <?php echo cc_csrf_field(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                    <input type="hidden" name="size_id" value="<?php echo $sizeId; ?>">
                                    <label class="visually-hidden" for="qty-<?php echo $productId; ?>-<?php echo $sizeId; ?>">
                                        Units to add to <?php echo e($row['product_title'] . ', ' . $sizeLabel); ?>
                                    </label>
                                    <input
                                        type="number"
                                        id="qty-<?php echo $productId; ?>-<?php echo $sizeId; ?>"
                                        name="qty"
                                        min="1"
                                        max="10000"
                                        step="1"
                                        inputmode="numeric"
                                        placeholder="Qty"
                                        required
                                    >
                                    <button type="submit" class="admin-btn admin-btn-primary">Add</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var lowLevel = <?php echo $lowLevel; ?>;
    var rows = Array.prototype.slice.call(document.querySelectorAll('[data-inventory-row]'));
    var search = document.getElementById('inventory-search');
    var stateFilter = document.getElementById('inventory-state');
    var countText = document.getElementById('inventory-count');
    var emptyText = document.getElementById('inventory-empty');

    function stateFor(stock) {
        if (stock <= 0) return ['out', 'cc-status cc-status--bad', 'Out of stock'];
        if (stock <= lowLevel) return ['low', 'cc-status cc-status--wait', 'Low stock'];
        return ['ok', 'cc-status cc-status--good', 'In stock'];
    }

    function applyFilters() {
        var term = search ? search.value.trim().toLowerCase() : '';
        var state = stateFilter ? stateFilter.value : 'all';
        var shown = 0;

        rows.forEach(function (row) {
            var match = (term === '' || row.dataset.search.indexOf(term) !== -1)
                && (state === 'all' || row.dataset.state === state);
            row.hidden = !match;
            if (match) shown++;
        });

        if (countText) countText.textContent = shown + (shown === 1 ? ' item' : ' items');
        if (emptyText) emptyText.hidden = shown > 0;
    }

    function updateSummary() {
        var units = 0, low = 0, out = 0;
        rows.forEach(function (row) {
            var stock = parseInt(row.querySelector('[data-stock]').textContent, 10) || 0;
            units += stock;
            if (row.dataset.state === 'low') low++;
            if (row.dataset.state === 'out') out++;
        });
        document.querySelector('[data-total-units]').textContent = units.toLocaleString('en-GH');
        document.querySelector('[data-count="low"]').textContent = low;
        document.querySelector('[data-count="out"]').textContent = out;
    }

    if (search) search.addEventListener('input', applyFilters);
    if (stateFilter) stateFilter.addEventListener('change', applyFilters);

    // Add stock without reloading the page.
    document.querySelectorAll('[data-add-stock]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.ccFetch || !window.FormData) return;
            event.preventDefault();

            var input = form.querySelector('input[name="qty"]');
            var button = form.querySelector('button');
            var qty = parseInt(input.value, 10);

            if (!qty || qty < 1 || qty > 10000) {
                window.ccNotify('Enter a whole number from 1 to 10,000.', 'error');
                input.focus();
                return;
            }

            button.disabled = true;

            window.ccFetch(form.action, { method: 'POST', body: new FormData(form) })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (!data.ok) {
                        window.ccNotify(data.message || 'The stock could not be updated.', 'error');
                        return;
                    }

                    var row = form.closest('[data-inventory-row]');
                    var info = stateFor(data.stock);
                    row.querySelector('[data-stock]').textContent = data.stock;
                    var badge = row.querySelector('[data-badge]');
                    badge.className = info[1];
                    badge.textContent = info[2];
                    row.dataset.state = info[0];
                    input.value = '';

                    updateSummary();
                    applyFilters();
                    window.ccNotify(data.message, 'success');
                })
                .catch(function () {
                    window.ccNotify('The stock could not be updated. Please try again.', 'error');
                })
                .then(function () {
                    button.disabled = false;
                });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

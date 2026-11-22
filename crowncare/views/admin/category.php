
<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

require_admin();

$controller = new ProductController();
$categories = $controller->getAllCategories();
$allCategories = $categories;

$search = trim((string) ($_GET['search'] ?? ''));
$typeFilter = (string) ($_GET['type'] ?? 'all');
$parentFilter = (int) ($_GET['parent'] ?? 0);

if (!in_array($typeFilter, ['all', 'main', 'subcategory'], true)) {
    $typeFilter = 'all';
}

$mainCategories = array_values(array_filter($categories, function ($category) {
    return empty($category['parent_category_id']);
}));
$categories = array_values(array_filter($categories, function ($category) use ($search, $typeFilter, $parentFilter) {
    $isSubcategory = !empty($category['parent_category_id']);
    $matchesSearch = $search === ''
        || stripos($category['cat_name'], $search) !== false
        || stripos($category['parent_category_name'] ?? '', $search) !== false;
    $matchesType = $typeFilter === 'all'
        || ($typeFilter === 'main' && !$isSubcategory)
        || ($typeFilter === 'subcategory' && $isSubcategory);
    $matchesParent = $parentFilter === 0 || (int) ($category['parent_category_id'] ?? 0) === $parentFilter;

    return $matchesSearch && $matchesType && $matchesParent;
}));
$matchedCategoryIds = array_fill_keys(array_map('intval', array_column($categories, 'cat_id')), true);

$pageTitle = 'Categories';
require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header admin-catalog-page-header">
    <div>
        <h1>All categories</h1>
        <p>Manage main categories and their subcategories.</p>
    </div>

    <a
        class="admin-btn admin-btn-primary"
        href="<?php echo BASE_URL; ?>views/admin/category_form.php"
    >
        Add category
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="admin-message admin-success">
        <?php
        echo htmlspecialchars($_SESSION['success']);
        unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="admin-message admin-error">
        <?php
        echo htmlspecialchars($_SESSION['error']);
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<div class="admin-catalog-toolbar">
    <div class="admin-catalog-toolbar-inner">
    <form class="admin-catalog-filters admin-category-filters" method="GET" action="<?php echo BASE_URL; ?>views/admin/category.php" data-admin-live-filters="categories">
        <label>
            <span>Search categories</span>
            <input
                type="search"
                name="search"
                value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                placeholder="Search categories by name..."
            >
        </label>
        <label>
            <span>Category type</span>
            <select name="type">
                <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>All types</option>
                <option value="main" <?php echo $typeFilter === 'main' ? 'selected' : ''; ?>>Main categories</option>
                <option value="subcategory" <?php echo $typeFilter === 'subcategory' ? 'selected' : ''; ?>>Subcategories</option>
            </select>
        </label>
        <label>
            <span>Parent category</span>
            <select name="parent">
                <option value="0">All parent categories</option>
                <?php foreach ($mainCategories as $mainCategory): ?>
                    <option
                        value="<?php echo (int) $mainCategory['cat_id']; ?>"
                        <?php echo $parentFilter === (int) $mainCategory['cat_id'] ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($mainCategory['cat_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="admin-catalog-filter-actions">
            <button class="admin-btn admin-btn-primary" type="submit">Apply</button>
            <a class="admin-btn admin-btn-secondary" href="<?php echo BASE_URL; ?>views/admin/category.php" data-clear-admin-filters>Clear</a>
        </div>
    </form>
    </div>
</div>
<p class="admin-filter-count" data-admin-filter-count aria-live="polite"><?php echo count($categories); ?> categor<?php echo count($categories) === 1 ? 'y' : 'ies'; ?> found</p>

<section class="admin-card">
    <?php if (count($allCategories) > 0): ?>
        <p class="admin-empty" data-admin-filter-empty <?php echo empty($categories) ? '' : 'hidden'; ?>>
            No categories match these filters. Try changing or clearing the filters.
        </p>

        <div class="admin-table-container">
            <table class="admin-table" data-admin-filter-table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Category Type</th>
                        <th>Parent Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($allCategories as $category): ?>

                        <?php
                        $isSubcategory = !empty($category['parent_category_id']);
                        $categoryId = (int) $category['cat_id'];
                        $categoryName = (string) $category['cat_name'];
                        $parentName = (string) ($category['parent_category_name'] ?? '');
                        ?>

                        <tr
                            data-admin-filter-row
                            data-search="<?php echo htmlspecialchars($categoryName . ' ' . $parentName, ENT_QUOTES, 'UTF-8'); ?>"
                            data-type="<?php echo $isSubcategory ? 'subcategory' : 'main'; ?>"
                            data-parent="<?php echo (int) ($category['parent_category_id'] ?? 0); ?>"
                            <?php echo isset($matchedCategoryIds[$categoryId]) ? '' : 'hidden'; ?>
                        >
                            <td>
                                <?php echo (int) $category['cat_id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($category['cat_name']); ?>
                            </td>

                            <td>
                                <?php echo $isSubcategory ? 'Subcategory' : 'Main Category'; ?>
                            </td>

                            <td>
                                <?php
                                echo $isSubcategory
                                    ? htmlspecialchars($category['parent_category_name'] ?? 'Unknown')
                                    : '—';
                                ?>
                            </td>

                            <td>
                                <div class="admin-actions admin-actions-links">

                                    <a href="<?php echo BASE_URL; ?>views/admin/category_form.php?edit_id=<?php echo (int) $category['cat_id']; ?>">
                                        Edit
                                    </a>

                                    <form
                                        action="<?php echo BASE_URL; ?>actions/delete_category_action.php"
                                        method="POST"
                                        data-confirm="Delete this category?" data-confirm-text="This cannot be undone." data-confirm-button="Delete category"
                                    >
                                        <input
                                            type="hidden"
                                            name="cat_id"
                                            value="<?php echo (int) $category['cat_id']; ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="admin-link-delete"
                                        >
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>

    <?php else: ?>
        <div class="admin-empty">
            <p>No categories have been added yet.</p>
        </div>
    <?php endif; ?>

</section>

<script>
(function () {
    var form = document.querySelector('[data-admin-live-filters="categories"]');
    if (!form) return;

    var rows = Array.from(document.querySelectorAll('[data-admin-filter-row]'));
    var count = document.querySelector('[data-admin-filter-count]');
    var empty = document.querySelector('[data-admin-filter-empty]');
    var table = document.querySelector('[data-admin-filter-table]');
    if (!rows.length || !count || !empty || !table) return;
    var search = form.elements.search;
    var type = form.elements.type;
    var parent = form.elements.parent;

    function updateResults() {
        var term = search.value.trim().toLocaleLowerCase();
        var shown = 0;

        rows.forEach(function (row) {
            var matches = (!term || row.dataset.search.toLocaleLowerCase().includes(term))
                && (type.value === 'all' || row.dataset.type === type.value)
                && (parent.value === '0' || row.dataset.parent === parent.value);
            row.hidden = !matches;
            if (matches) shown++;
        });

        count.textContent = shown + ' categor' + (shown === 1 ? 'y' : 'ies') + ' found';
        empty.hidden = shown !== 0;
        table.hidden = shown === 0;
    }

    var searchTimer;
    search.addEventListener('input', function () {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(updateResults, 120);
    });
    [type, parent].forEach(function (control) {
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
        type.value = 'all';
        parent.value = '0';
        updateResults();
    });
    updateResults();
})();
</script>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>

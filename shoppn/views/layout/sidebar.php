
<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

if (!isset($controller)) {
    $controller = new ProductController();
}

$categories = $controller->getAllCategories();
$brands = $controller->getAllBrands();
?>

<aside class="shop-sidebar">
    <h2>Categories</h2>

    <?php if (empty($categories)): ?>
        <p>No categories available.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?cat=<?php
                        echo (int) $category['cat_id'];
                    ?>">
                        <?php echo htmlspecialchars(
                            $category['cat_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Brands</h2>

    <?php if (empty($brands)): ?>
        <p>No brands available.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($brands as $brand): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?brand=<?php
                        echo (int) $brand['brand_id'];
                    ?>">
                        <?php echo htmlspecialchars(
                            $brand['brand_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</aside>

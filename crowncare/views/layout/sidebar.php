
<?php
/*
 * Category filter for the shop sidebar. No view includes it right now —
 * shop.php builds its own category links — but the markup is kept here.
 */
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

// Reuse the controller if the including page already made one.
if (!isset($controller)) {
    $controller = new ProductController();
}

// Categories come from the database; each link filters shop.php by ?cat=.
$categories = $controller->getAllCategories();
?>

<aside class="shop-sidebar">
    <h2>Categories</h2>

    <?php if (empty($categories)): ?>
        <p>No categories available.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>views/shop.php?cat=<?php
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

</aside>

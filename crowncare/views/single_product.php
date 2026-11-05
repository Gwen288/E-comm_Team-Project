
<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$productId = filter_input(
    INPUT_GET,
    'pro_id',
    FILTER_VALIDATE_INT
);

$product = false;

if ($productId !== false && $productId !== null && $productId > 0) {
    $product = $controller->getProductById($productId);
}

require_once __DIR__ . '/layout/header.php';
?>

<main class="shop-page single-product">

    <?php if (!empty($_SESSION['cart_message'])): ?>
        <div class="cart-message">
            <?php
            echo htmlspecialchars($_SESSION['cart_message'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['cart_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if ($product === false): ?>

        <div class="no-products">
            <h1>Product Not Found</h1>
            <p>This product does not exist or is no longer available.</p>

            <a href="<?php echo BASE_URL; ?>views/shop.php">
                Back to Shop
            </a>
        </div>

    <?php else: ?>

        <a class="back-link" href="<?php echo BASE_URL; ?>views/shop.php">
            &larr; Back to Shop
        </a>

        <div class="single-product-content">

            <div class="single-product-image">
                <?php
                $image = trim($product['product_image'] ?? '');

                $imagePath = __DIR__ . '/../images/products/'
                    . basename($image);
                ?>

                <?php if ($image !== '' && is_file($imagePath)): ?>

                    <img
                        src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($image)); ?>"
                        alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                    >

                <?php else: ?>

                    <div class="product-no-image">
                        Image coming soon
                    </div>

                <?php endif; ?>
            </div>

            <div class="single-product-info surface-panel">

                <h1>
                    <?php
                    echo htmlspecialchars(
                        $product['product_title'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </h1>

                <p class="product-price">
                    GH₵ <?php
                    echo number_format(
                        (float) $product['product_price'],
                        2
                    );
                    ?>
                </p>

                <p class="product-meta-line">
                    <span>Brand</span>
                    <?php
                    echo htmlspecialchars(
                        $product['brand_name'] ?? 'Unknown',
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </p>

                <p class="product-meta-line">
                    <span>Category</span>
                    <?php
                    echo htmlspecialchars(
                        $product['cat_name'] ?? 'Unknown',
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </p>

                <?php if (!(is_logged_in() && is_admin())): ?>
                    <a
                        class="add-cart-btn"
                        href="<?php echo BASE_URL; ?>actions/add_to_cart_action.php?add_cart=<?php echo (int) $product['product_id']; ?>"
                    >
                        Add to cart
                    </a>
                <?php endif; ?>

                <div class="product-details-block">
                    <h2>Product details</h2>

                    <div class="product-details-body">
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $product['product_desc'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            )
                        );
                        ?>
                    </div>

                    <?php if (!empty($product['product_keywords'])): ?>
                        <p class="product-details-meta">
                            <span>Keywords</span>
                            <?php
                            echo htmlspecialchars(
                                $product['product_keywords'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

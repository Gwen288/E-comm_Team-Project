
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

    <?php if ($product === false): ?>

        <div class="no-products">
            <h1>Product Not Found</h1>
            <p>This product does not exist or is no longer available.</p>

            <a href="<?php echo BASE_URL; ?>index.php">
                Back to Shop
            </a>
        </div>

    <?php else: ?>

        <a href="<?php echo BASE_URL; ?>index.php">
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
                        No image available
                    </div>

                <?php endif; ?>
            </div>

            <div class="single-product-info">

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

                <p>
                    <strong>Brand:</strong>
                    <?php
                    echo htmlspecialchars(
                        $product['brand_name'] ?? 'Unknown',
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </p>

                <p>
                    <strong>Category:</strong>
                    <?php
                    echo htmlspecialchars(
                        $product['cat_name'] ?? 'Unknown',
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </p>

                <h2>Description</h2>

                <p>
                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $product['product_desc'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        )
                    );
                    ?>
                </p>

                <?php if (!empty($product['product_keywords'])): ?>
                    <p>
                        <strong>Keywords:</strong>
                        <?php
                        echo htmlspecialchars(
                            $product['product_keywords'],
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </p>
                <?php endif; ?>

                <a
                    href="<?php echo BASE_URL; ?>index.php?add_cart=<?php echo (int) $product['product_id']; ?>"
                >
                    Add to Cart
                </a>

            </div>

        </div>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

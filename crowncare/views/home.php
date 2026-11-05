<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();
$featured = $controller->getFeaturedProducts(4);
$heroImage = BASE_URL . 'images/hero/crowncare-hero.jpg';
$shopUrl = BASE_URL . 'views/shop.php';
?>

<?php require_once __DIR__ . '/layout/header.php'; ?>

<main class="landing-page">

    <?php if (isset($_SESSION['success'])): ?>
        <div class="landing-shell">
            <div class="success-message">
                <?php
                echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8');
                unset($_SESSION['success']);
                ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="landing-shell">
            <div class="error-message">
                <?php
                echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8');
                unset($_SESSION['error']);
                ?>
            </div>
        </div>
    <?php endif; ?>

    <section
        class="hero-bleed"
        style="--hero-image: url('<?php echo htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8'); ?>');"
    >
        <div class="hero-bleed-overlay">
            <div class="hero-bleed-copy">
                <p class="hero-kicker">Natural hair care</p>
                <h1>Explore care made for your crown</h1>
               
                <div class="hero-actions">
                    <a class="hero-btn-primary" href="<?php echo $shopUrl; ?>">Go to shop</a>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-shell landing-about">
        <div class="landing-about-copy">
            <p class="eyebrow">Our promise</p>
            <h2>Care that respects your texture</h2>
            <p>
                CrownCare brings shea, cocoa, and everyday hair essentials together in one calm shop —
                so you can find what works without the noise.
            </p>
            <a class="landing-text-link" href="<?php echo $shopUrl; ?>">Browse the full shop</a>
        </div>
    </section>

    <section class="landing-shell products-section" id="featured">
        <div class="products-heading">
            <h2>Featured products</h2>
            <a class="landing-text-link" href="<?php echo $shopUrl; ?>">Shop all</a>
        </div>

        <?php if (!empty($featured)): ?>
            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <article class="product-card">
                        <a
                            class="product-image-link"
                            href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>"
                        >
                            <?php
                            $image = trim($product['product_image'] ?? '');
                            $imagePath = __DIR__ . '/../images/products/' . basename($image);
                            ?>

                            <?php if ($image !== '' && is_file($imagePath)): ?>
                                <img
                                    src="<?php echo BASE_URL; ?>images/products/<?php echo rawurlencode(basename($image)); ?>"
                                    alt="<?php echo htmlspecialchars($product['product_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="product-image"
                                >
                            <?php else: ?>
                                <div class="product-no-image">Photo coming soon</div>
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
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <h3>Products coming soon</h3>
                <p>Check back shortly, or open the shop when items are ready.</p>
                <a href="<?php echo $shopUrl; ?>">Go to shop</a>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

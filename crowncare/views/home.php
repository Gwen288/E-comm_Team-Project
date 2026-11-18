
<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();
$featured = $controller->getFeaturedProducts(4);

$shopUrl = BASE_URL . 'views/shop.php';
$hairGuideUrl = BASE_URL . 'views/hair_guide.php';
$hairQuizUrl = BASE_URL . 'views/hair_quiz.php';
$consultationUrl = BASE_URL . 'views/book_consultation.php';

// Use the photos from the CMS site when they have been downloaded,
// otherwise fall back to the original images.
function home_image($cmsFile, $fallbackFile)
{
    $file = is_file(__DIR__ . '/../' . $cmsFile) ? $cmsFile : $fallbackFile;
    return BASE_URL . $file;
}

$heroImage = home_image('images/home/hero.jpg', 'images/hero/crowncare-hero.jpg');
$hairGuideImage = home_image('images/home/know-your-hair.webp', 'images/home/hair-guide.jpg');
$hairQuizImage = home_image('images/home/find-your-match.webp', 'images/home/hair-quiz.jpg');
$consultationImage = is_file(__DIR__ . '/../images/home/consultation.jpg')
    ? BASE_URL . 'images/home/consultation.jpg'
    : '';
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

    <!-- HERO (as on the CMS site). It is sized to fit the screen below the header. -->
    <div class="cc-hero-wrap">
        <section
            class="cc-hero"
            style="background-image: url('<?php echo htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8'); ?>');"
        >
            <div class="cc-hero-copy">
                <h1 class="cc-hero-title">Restore Your Natural Crown</h1>
                <p class="cc-hero-text">Experience the power of Ghanaian botanical science. Discover artisanal hair-care solutions designed for your unique heritage and lifestyle.</p>
                <div class="cc-hero-actions">
                    <a class="cc-pill" href="<?php echo $shopUrl; ?>">Shop the Collection</a>
                    <a class="cc-pill cc-pill--outline" href="<?php echo $hairQuizUrl; ?>">Take the Quiz</a>
                </div>
            </div>
        </section>
    </div>

    <!-- OUR PROMISE -->
    <section class="landing-shell landing-about">
        <div class="landing-about-copy">
            <p class="eyebrow">Our promise</p>
            <h2>Care that respects your texture</h2>
            <p>
                CrownCare brings shea, cocoa, and everyday hair essentials together
                in one calm shop — so you can find what works without the noise.
            </p>
            <a class="landing-text-link" href="<?php echo $shopUrl; ?>">
                Browse the full shop
            </a>
        </div>
    </section>

    <!-- KNOW YOUR HAIR / HAIR GUIDE -->
    <section class="landing-shell hair-feature-section">
        <div class="hair-feature-image hair-guide-image">
        <img
    src="<?php echo $hairGuideImage; ?>"
    alt="Hair Guide"
    width="600"
    height="440"
>
            <div class="hair-image-label">Learn. Understand. Care.</div>
        </div>

        <div class="hair-feature-content">
            <p class="eyebrow">01 — Know your hair</p>

            <h2>Every crown has its own story.</h2>

            <p>
                Healthy hair care starts with understanding your hair.
                Explore natural hair types, porosity, moisture, protective
                styling and the everyday habits that support healthy hair.
            </p>

            <p>
                Get to know your texture and discover how to care for it
                with more confidence.
            </p>

            <a class="hair-feature-button" href="<?php echo $hairGuideUrl; ?>">
                Explore the Hair Guide
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    <!-- HAIR QUIZ -->
    <section class="landing-shell hair-feature-section hair-feature-reverse">
        <div class="hair-feature-image hair-quiz-image">
        <img
    src="<?php echo $hairQuizImage; ?>"
    alt="Hair Quiz"
    width="600"
    height="440"
>
            <div class="hair-image-label">Your hair. Your routine.</div>
        </div>

        <div class="hair-feature-content">
            <p class="eyebrow">02 — Discover your routine</p>

            <h2>Not sure what your hair needs?</h2>

            <p>
                Answer a few simple questions about your hair, its condition
                and your hair-care habits. The CrownCare Hair Quiz helps you
                explore products and routine suggestions that may suit your needs.
            </p>

            <p>
                A simple starting point for making more informed hair-care choices.
            </p>

            <a class="hair-feature-button" href="<?php echo $hairQuizUrl; ?>">
                Take the Hair Quiz
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    <!-- HAIR CONSULTATION -->
    <section class="landing-shell hair-feature-section">
        <div class="hair-feature-image consultation-image">
            <?php if ($consultationImage !== ''): ?>
                <img
                    src="<?php echo $consultationImage; ?>"
                    alt="CrownCare hair specialist"
                    width="600"
                    height="440"
                >
            <?php else: ?>
                <div class="consultation-photo-placeholder" role="img" aria-label="CrownCare hair specialist">
                    <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"></path>
                    </svg>
                </div>
            <?php endif; ?>
            <div class="hair-image-label">Advice. Routine. Results.</div>
        </div>

        <div class="hair-feature-content">
            <p class="eyebrow">Book a consultation</p>

            <h2>Talk to a hair specialist.</h2>

            <p>
                Book a one-to-one consultation with a CrownCare hair specialist.
                We look at your hair and scalp, talk through your goals and build
                a routine that suits your hair, your schedule and your budget.
            </p>

            <p>
                Choose an in-person visit or a video call, pick a time that works
                for you, and come with your questions.
            </p>

            <a class="hair-feature-button" href="<?php echo $consultationUrl; ?>">
                Book a consultation
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    <!-- FEATURED PRODUCTS -->
    <section class="landing-shell products-section" id="featured">
        <div class="products-heading">
            <h2>Featured products</h2>
            <a class="landing-text-link" href="<?php echo $shopUrl; ?>">
                Shop all
            </a>
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

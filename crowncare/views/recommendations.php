<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/RecommendationClass.php';

// No quiz answers in the session means this page was opened directly.
if (!isset($_SESSION['hair_quiz'])) {
    redirect(BASE_URL . 'views/hair_quiz.php');
}

$quiz = $_SESSION['hair_quiz'];

/*
 * Results page for shoppers who finished the quiz. Recommendations use the same
 * logic as the CMS quiz: one in-stock product per routine step, fitted to the
 * budget. See classes/RecommendationClass.php.
 */
// Each step carries its product and a "from" price for the cheapest matching size.
$result = (new RecommendationClass())->recommend($quiz);
$routine = $result['routine'];
$routineTotal = $result['total'];
$unsureType = ($quiz['hair_type'] ?? '') === 'Not sure';

$pageTitle = 'Your recommended products';
require_once __DIR__ . '/layout/header.php';
?>

<main class="cc-flow cc-reco">
    <div class="cc-reco-shell">
        <div class="cc-page-nav">
            <a class="cc-back" href="<?php echo BASE_URL; ?>views/hair_quiz.php">Change my answers</a>
            <div class="cc-page-nav-links">
                <a href="<?php echo BASE_URL; ?>views/shop.php">Shop all products</a>
            </div>
        </div>

        <h1>Your recommended products</h1>
        <p class="cc-reco-lead">Based on your answers, these CrownCare products make up your routine.</p>

        <?php if (!$routine): ?>
            <section class="cc-panel">
                <p class="cc-empty">We could not find products in stock for these answers right now.</p>
                <p><a class="cc-button cc-button--small" href="<?php echo BASE_URL; ?>views/shop.php">Browse the shop</a></p>
            </section>
        <?php else: ?>
            <div class="cc-reco-grid">
                <?php foreach ($routine as $item): ?>
                    <?php
                    $product = $item['product'];
                    $url = BASE_URL . 'views/single_product.php?pro_id=' . (int) $product['product_id'];
                    $image = cc_cart_image($product, '');
                    ?>
                    <article class="cc-reco-card">
                        <a class="cc-reco-photo" href="<?php echo $url; ?>" tabindex="-1" aria-hidden="true">
                            <?php if ($image): ?>
                                <img src="<?php echo e($image); ?>" alt="" loading="lazy">
                            <?php endif; ?>
                        </a>
                        <div class="cc-reco-body">
                            <span class="cc-reco-step"><?php echo e($item['step']); ?></span>
                            <h2><a href="<?php echo $url; ?>"><?php echo e($product['product_title']); ?></a></h2>
                            <p class="cc-reco-price">From <?php echo e(cc_money($item['from'])); ?></p>
                            <a class="cc-button cc-button--small cc-button--outline" href="<?php echo $url; ?>">View product</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="cc-reco-total">
                <div>
                    <span>Routine total</span>
                    <strong>From <?php echo e(cc_money($routineTotal)); ?></strong>
                </div>
                <a class="cc-button" href="<?php echo BASE_URL; ?>views/shop.php">Go to the shop</a>
            </div>

            <?php if ($unsureType): ?>
                <p class="cc-reco-note">
                    Not sure of your hair type? Read the
                    <a class="cc-link" href="<?php echo BASE_URL; ?>views/hair_guide.php">Hair Guide</a>
                    or book a
                    <a class="cc-link" href="<?php echo BASE_URL; ?>views/book_consultation.php">Hair and Scalp Assessment</a>.
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

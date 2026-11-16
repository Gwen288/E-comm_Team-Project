<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/RecommendationClass.php';

if (!isset($_SESSION['hair_quiz'])) {
    redirect(BASE_URL . 'views/hair_quiz.php');
}

$quiz = $_SESSION['hair_quiz'];

$budgetDescriptions = [
    'essentials' => 'Under GH₵250',
    '250-500' => 'GH₵250–500',
    '500-750' => 'GH₵500–750',
    '750-plus' => 'GH₵750+'
];

$budgetDescription = $budgetDescriptions[$quiz['budget']] ?? '';

/*
 * Recommendations use the same logic as the CMS quiz:
 * one product per routine step, fitted to the budget, plus a kit when it matches.
 * See classes/RecommendationClass.php.
 */
$recommender = new RecommendationClass();
$result = $recommender->recommend($quiz);

$routine = $result['routine'];
$routineTotal = $result['total'];
$droppedProducts = $result['dropped'];
$kit = $result['kit'];

require_once __DIR__ . '/layout/header.php';
?>

<main class="recommendations-page">

    <section class="recommendations-intro">

        <span class="hair-quiz-kicker">YOUR CROWNCARE MATCH</span>

        <h1>Recommendations made around your hair.</h1>

        <p>
            Based on your answers, here are products to explore
            for your hair type, concerns and current goal.
        </p>

    </section>

    <section class="recommendation-summary">

        <div>
            <span>HAIR TYPE</span>
            <strong><?php echo htmlspecialchars($quiz['hair_type']); ?></strong>
        </div>

        <div>
            <span>MAIN GOAL</span>
            <strong><?php echo htmlspecialchars($quiz['hair_goal']); ?></strong>
        </div>

        <div>
            <span>BUDGET</span>
            <strong><?php echo htmlspecialchars($budgetDescription); ?></strong>
        </div>

    </section>

    <div class="recommendation-heading">
        <div>
            <span class="hair-quiz-kicker">SELECTED FOR YOUR ROUTINE</span>
            <h2>Your product matches</h2>
        </div>

        <a href="<?php echo BASE_URL; ?>views/hair_quiz.php">
            Retake quiz
        </a>
    </div>

    <?php if (!empty($routine)): ?>

        <div class="product-grid">

            <?php foreach ($routine as $pick): ?>
                <?php $product = $pick['product']; ?>

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
                                alt="<?php echo htmlspecialchars($product['product_title']); ?>"
                                class="product-image"
                            >

                        <?php else: ?>

                            <div class="product-no-image">Photo coming soon</div>

                        <?php endif; ?>

                    </a>

                    <div class="product-card-content">

                        <span class="recommendation-category">
                            <?php echo htmlspecialchars($pick['step']); ?>
                        </span>

                        <h3>
                            <a href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['product_title']); ?>
                            </a>
                        </h3>

                        <p class="recommendation-why">
                            <?php echo htmlspecialchars($pick['why']); ?>
                        </p>

                        <p class="product-price">
                            From GH₵<?php echo number_format((float) $pick['from'], 2); ?>
                        </p>

                        <a
                            class="recommendation-view-link"
                            href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>"
                        >
                            View product →
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

        <div class="recommendation-total">
            <span>Your recommended products start from (smallest sizes)</span>
            <strong>GH₵<?php echo number_format((float) $routineTotal, 2); ?></strong>
        </div>

        <?php if ($droppedProducts): ?>
            <p class="recommendation-note">
                To stay within your budget, we did not recommend:
                <?php echo htmlspecialchars(implode(', ', $droppedProducts)); ?>.
                You can add <?php echo count($droppedProducts) > 1 ? 'them' : 'it'; ?> later.
            </p>
        <?php endif; ?>

        <?php if ($kit): ?>
            <?php $kitPhotos = cc_product_gallery($kit['product']); ?>
            <a
                class="recommendation-kit"
                href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $kit['product']['product_id']; ?>"
            >
                <?php if ($kitPhotos): ?>
                    <img src="<?php echo htmlspecialchars($kitPhotos[0]['src']); ?>" alt="<?php echo htmlspecialchars($kit['product']['product_title']); ?>">
                <?php endif; ?>
                <div>
                    <span class="recommendation-category">Also recommended for you · Save with a kit</span>
                    <h3><?php echo htmlspecialchars($kit['product']['product_title']); ?></h3>
                    <p class="recommendation-why">
                        <?php echo htmlspecialchars($kit['contains']); ?>.
                        Saves GH₵<?php echo (int) $kit['saves']; ?> compared with buying separately.
                    </p>
                    <span class="recommendation-view-link">GH₵<?php echo number_format((float) $kit['from'], 2); ?> · View kit →</span>
                </div>
            </a>
        <?php endif; ?>

        <p class="recommendation-note">
            These recommendations are based on general hair-care guidance and are not medical advice.
            If you have ongoing scalp problems or hair loss, please see a dermatologist.
        </p>

    <?php else: ?>

        <div class="recommendation-empty">

            <h2>No products found within this budget.</h2>

            <p>
                Try a higher budget or browse the full CrownCare catalogue.
            </p>

            <a href="<?php echo BASE_URL; ?>views/shop.php">
                Browse all products
            </a>

        </div>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
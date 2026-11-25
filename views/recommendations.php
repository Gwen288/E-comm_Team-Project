<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/RecommendationClass.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../classes/StockClass.php';

/*
 * Recommended products from the hair quiz.
 *
 * Fresh answers (in the session) use the same logic as the CMS quiz: one in-stock
 * product per routine step, fitted to the budget (classes/RecommendationClass.php).
 * Signed-in customers have their latest set saved, so they can come back to it
 * later from My account without taking the quiz again.
 */
$recommender = new RecommendationClass();
$customerId = is_customer() ? get_customer_id() : 0;
$savedAt = null;

if (isset($_SESSION['hair_quiz'])) {
    $quiz = $_SESSION['hair_quiz'];
    $result = $recommender->recommend($quiz);
    if ($customerId && $result['routine'] && empty($_SESSION['hair_quiz_saved'])) {
        if ($recommender->save($customerId, $quiz, $result)) {
            $_SESSION['hair_quiz_saved'] = true;
        }
    }
    if ($customerId && !empty($_SESSION['hair_quiz_saved'])) {
        $savedAt = date('Y-m-d H:i:s');
    }
} elseif ($customerId && ($saved = $recommender->getSaved($customerId))) {
    $quiz = $saved['answers'];
    $result = $recommender->routineFromSaved($saved);
    $savedAt = $saved['saved_at'];
} else {
    redirect(BASE_URL . 'views/hair_quiz.php');
}

$routine = $result['routine'];
$routineTotal = $result['total'];
$unsureType = ($quiz['hair_type'] ?? '') === 'Not sure';
$canBuy = !(is_logged_in() && (is_admin() || is_consultant()));

// Sizes and stock for the add to cart controls on each card.
$productController = new ProductController();
$stockClass = new StockClass();
foreach ($routine as $i => $item) {
    $pid = (int) $item['product']['product_id'];
    $sizes = $productController->getProductSizes($pid) ?: [];
    $sizeStock = $sizes ? $stockClass->getSizeStock($pid) : [];
    $productStock = $sizes ? 0 : (int) $stockClass->getAvailable($pid, 0);
    $routine[$i]['sizes'] = $sizes;
    $routine[$i]['size_stock'] = $sizeStock;
    $routine[$i]['sold_out'] = $sizes ? array_sum($sizeStock) <= 0 : $productStock <= 0;
}

$pageTitle = 'Your recommended products';
require_once __DIR__ . '/layout/header.php';
?>

<main class="cc-flow cc-reco">
    <div class="cc-reco-shell">
        <div class="cc-page-nav">
            <a class="cc-back" href="<?php echo BASE_URL; ?>views/hair_quiz.php"><?php echo isset($_SESSION['hair_quiz']) ? 'Change my answers' : 'Retake the quiz'; ?></a>
            <div class="cc-page-nav-links">
                <a href="<?php echo BASE_URL; ?>views/shop.php">Shop all products</a>
            </div>
        </div>

        <h1>Your recommended products</h1>
        <p class="cc-reco-lead">Based on your answers, these CrownCare products make up your routine.</p>

        <?php if ($savedAt): ?>
            <p class="cc-reco-saved">Saved to your account on <?php echo e(date('j F Y', strtotime($savedAt))); ?>. You can find them again under <a class="cc-link" href="<?php echo BASE_URL; ?>views/account/my_account.php">My account</a>.</p>
        <?php elseif (!is_logged_in()): ?>
            <p class="cc-reco-saved"><a class="cc-link" href="<?php echo BASE_URL; ?>views/login.php?return=recommendations">Sign in</a> to save these products to your account and come back to them later.</p>
        <?php endif; ?>

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
                    $pid = (int) $product['product_id'];
                    $url = BASE_URL . 'views/single_product.php?pro_id=' . $pid . '&from=recommendations';
                    $image = cc_cart_image($product, '');
                    ?>
                    <article class="cc-reco-card">
                        <a class="cc-reco-photo<?php echo $item['sold_out'] ? ' is-sold-out' : ''; ?>" href="<?php echo $url; ?>" tabindex="-1" aria-hidden="true">
                            <?php if ($image): ?>
                                <img src="<?php echo e($image); ?>" alt="" loading="lazy">
                            <?php endif; ?>
                            <?php if ($item['sold_out']): ?><span class="cc-sold-out-tag">Sold out</span><?php endif; ?>
                        </a>
                        <div class="cc-reco-body">
                            <span class="cc-reco-step"><?php echo e($item['step']); ?></span>
                            <h2><a href="<?php echo $url; ?>"><?php echo e($product['product_title']); ?></a></h2>
                            <p class="cc-reco-price">From <?php echo e(cc_money($item['from'])); ?></p>

                            <?php if ($canBuy && !$item['sold_out']): ?>
                                <form class="cc-reco-cart" method="POST" action="<?php echo BASE_URL; ?>actions/add_to_cart_action.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(cc_csrf_token()); ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <?php if ($item['sizes']): ?>
                                        <label class="visually-hidden" for="reco-size-<?php echo $pid; ?>">Size for <?php echo e($product['product_title']); ?></label>
                                        <select id="reco-size-<?php echo $pid; ?>" name="size_id" required>
                                            <option value="">Choose a size</option>
                                            <?php foreach ($item['sizes'] as $size): ?>
                                                <?php $left = (int) ($item['size_stock'][(int) $size['size_id']] ?? 0); ?>
                                                <option value="<?php echo (int) $size['size_id']; ?>" <?php echo $left <= 0 ? 'disabled' : ''; ?>>
                                                    <?php echo e(cc_size_label($size['size_name'])); ?>, <?php echo e(cc_money($size['price'])); ?><?php echo $left <= 0 ? ' (sold out)' : ''; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="size_id" value="0">
                                    <?php endif; ?>
                                    <button type="submit" class="cc-button cc-button--small">Add to cart</button>
                                </form>
                            <?php endif; ?>

                            <a class="cc-link cc-reco-view" href="<?php echo $url; ?>">View product</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="cc-reco-total">
                <div>
                    <span>Routine total</span>
                    <strong>From <?php echo e(cc_money($routineTotal)); ?></strong>
                </div>
                <a class="cc-button" href="<?php echo BASE_URL; ?>views/cart.php">Go to my cart</a>
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

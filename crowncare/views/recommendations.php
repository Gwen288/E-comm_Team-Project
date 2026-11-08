<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/db_class.php';

if (!isset($_SESSION['hair_quiz'])) {
    redirect(BASE_URL . 'views/hair_quiz.php');
}

class HairRecommendationDatabase extends Database
{
    public function getConnection()
    {
        return $this->conn;
    }
}

$database = new HairRecommendationDatabase();
$conn = $database->getConnection();

$quiz = $_SESSION['hair_quiz'];

$budgetLimits = [
    'essentials' => 250,
    '250-500' => 500,
    '500-750' => 750,
    '750-plus' => null
];

$maximumBudget = $budgetLimits[$quiz['budget']] ?? 250;

$budgetDescriptions = [
    'essentials' => 'Under GH₵250',
    '250-500' => 'GH₵250–500',
    '500-750' => 'GH₵500–750',
    '750-plus' => 'GH₵750+'
];

$budgetDescription = $budgetDescriptions[$quiz['budget']] ?? '';

$matchingAttributes = [];

if ($quiz['hair_type'] !== 'Not sure') {
    $matchingAttributes[] = $quiz['hair_type'];
}

foreach ($quiz['concerns'] as $concern) {
    $matchingAttributes[] = $concern;
}

$matchingAttributes[] = $quiz['hair_goal'];

foreach ($quiz['preferences'] as $preference) {
    $matchingAttributes[] = $preference;
}

$matchingAttributes = array_values(array_unique($matchingAttributes));

$products = [];

$sql = "
    SELECT
        p.product_id,
        p.product_title,
        p.product_price,
        p.product_desc,
        p.product_image,
        c.cat_name,
        b.brand_name,
        COUNT(DISTINCT ha.attribute_id) AS match_score
    FROM products p
    INNER JOIN categories c
        ON c.cat_id = p.product_cat
    INNER JOIN brands b
        ON b.brand_id = p.product_brand
    LEFT JOIN product_hair_attributes pha
        ON pha.product_id = p.product_id
    LEFT JOIN hair_attributes ha
        ON ha.attribute_id = pha.attribute_id
       AND ha.attribute_name IN (" .
            implode(',', array_fill(0, count($matchingAttributes), '?')) .
        ")
    WHERE 1 = 1
";

if ($maximumBudget !== null) {
    $sql .= " AND p.product_price <= ?";
}

$sql .= "
    GROUP BY
        p.product_id,
        p.product_title,
        p.product_price,
        p.product_desc,
        p.product_image,
        c.cat_name,
        b.brand_name
    ORDER BY match_score DESC, p.product_price ASC
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $types = str_repeat('s', count($matchingAttributes));
    $parameters = $matchingAttributes;

    if ($maximumBudget !== null) {
        $types .= 'd';
        $parameters[] = $maximumBudget;
    }

    if (!empty($parameters)) {
        $stmt->bind_param($types, ...$parameters);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    $stmt->close();
}

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

    <?php if (!empty($products)): ?>

        <div class="product-grid">

            <?php foreach ($products as $product): ?>

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
                            <?php echo htmlspecialchars($product['cat_name']); ?>
                        </span>

                        <h3>
                            <a href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['product_title']); ?>
                            </a>
                        </h3>

                        <p class="product-price">
                            GH₵<?php echo number_format((float) $product['product_price'], 2); ?>
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

        <?php if ((int) $products[0]['match_score'] === 0): ?>

            <p class="recommendation-note">
                We did not find products with matching hair attributes yet.
                These products are shown within your selected budget.
            </p>

        <?php endif; ?>

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
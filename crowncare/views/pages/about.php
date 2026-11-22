<?php
// Static About page for shoppers: who CrownCare is and how the quiz helps.
require_once __DIR__ . '/../../core/core.php';

$pageTitle = 'About CrownCare';
$pageDescription = 'CrownCare is a Ghanaian natural hair-care brand for coily and curly hair, from 3C to 4C.';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-info">
    <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>

    <h1>Hair care made for your crown</h1>
    <p class="cc-info-lead">CrownCare is a Ghanaian natural hair-care brand for coily and curly hair, from 3C to 4C.</p>

    <p>We started CrownCare because finding the right products for African natural hair is often guesswork: too many products, too little guidance, and too much money spent on things that do not suit your hair.</p>

    <p>So we made it simple. Our range is built around ingredients our grandmothers trusted, such as shea butter, African black soap, baobab and hibiscus, in gentle, modern formulas. Our hair quiz matches you with a routine based on your hair type, your concerns and your goals, so you buy what your hair actually needs.</p>

    <h2>Our promise</h2>
    <ul>
        <li>Products made for coils, curls and kinks</li>
        <li>Honest guidance, not guesswork</li>
        <li>Gentle formulas with clear labels</li>
        <li>Delivery across Greater Accra</li>
    </ul>

    <p><a class="cc-button" href="<?php echo BASE_URL; ?>views/hair_quiz.php">Take the hair quiz</a></p>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

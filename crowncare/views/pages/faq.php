<?php
// Static FAQ page for shoppers. Answers are kept in one array below, so the
// page is just text and there's nothing to maintain in the database.
require_once __DIR__ . '/../../core/core.php';

$questions = [
    'How do I know which products are right for me?' =>
        'Take our hair quiz. It asks about your hair type, concerns, goals, scalp and budget, then recommends products from our range for each step of your routine.',
    'What hair types are CrownCare products for?' =>
        'Our range is made for coily and curly hair, from 3C to 4C.',
    'Where do you deliver?' =>
        'We deliver across Greater Accra through our delivery partner, a third-party delivery service. Type your delivery address at checkout. The delivery charge is not paid on this website; our delivery partner tells you the charge when they contact you.',
    'How long does delivery take?' =>
        'Orders within Greater Accra are delivered in 1 to 3 working days. We email you when your order is on its way.',
    'How can I pay?' =>
        'You pay online with mobile money or a bank card through Paystack. CrownCare never sees or stores your card details.',
    'How do I track my order?' =>
        'Sign in and open My orders. Each order shows whether it has been received, is being prepared, is out for delivery or has been delivered.',
    'Can I return a product?' =>
        'Yes. Unopened products can be returned within 14 days of delivery. See Delivery and returns for details.',
    'Can I change or cancel a consultation?' =>
        'Yes. Cancel from My consultations up to 24 hours before your appointment, or contact us to change it.',
    'Are your products safe for sensitive scalps?' =>
        'Look for products marked fragrance-free, such as the Scalp Soothing Shampoo and the Castor Oil Scalp Serum. If you have a medical scalp condition, please speak to a dermatologist first.',
];

$pageTitle = 'Frequently asked questions';
$pageDescription = 'Answers about CrownCare products, delivery in Accra, payment, returns and consultations.';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-info">
    <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>

    <h1>Frequently asked questions</h1>
    <p class="cc-info-lead">Cannot find your answer? <a href="<?php echo BASE_URL; ?>views/pages/contact.php">Contact us</a>.</p>

    <div class="cc-faq">
        <?php /* Array keys become the <summary> lines; answers sit in collapsible <details>. */ ?>
        <?php foreach ($questions as $question => $answer): ?>
            <details>
                <summary><?php echo e($question); ?></summary>
                <p><?php echo e($answer); ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

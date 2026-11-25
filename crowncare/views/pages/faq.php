<?php
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
        'If you have an account, sign in and open My orders: each order shows whether it has been received, is being prepared, is out for delivery or has been delivered. If you checked out as a guest, we email you at each step.',
    'Can I return a product?' =>
        'Yes. Unopened products can be returned within 14 days of delivery. See Delivery and returns for details.',
    'How do I pay for a consultation?' =>
        'You pay online with mobile money or a card through Paystack when you book. Your time is held while you pay.',
    'Can I cancel a consultation and get a refund?' =>
        'Yes. Use the cancel link in your booking email, or My consultations if you have an account. Cancel 24 hours or more before for a full refund; with less notice we refund 95%. If you do not come, the payment is not refunded. If we cancel, you get a full refund. Refunds take 3 to 5 working days.',
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
        <?php foreach ($questions as $question => $answer): ?>
            <details>
                <summary><?php echo e($question); ?></summary>
                <p><?php echo e($answer); ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

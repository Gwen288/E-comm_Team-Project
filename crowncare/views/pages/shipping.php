<?php
require_once __DIR__ . '/../../core/core.php';
$pageTitle = 'Delivery and returns';
$pageDescription = 'How CrownCare orders are delivered across Greater Accra, and our returns and refunds.';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-info">
    <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>

    <h1>Delivery and returns</h1>
    <p class="cc-info-lead">We deliver across Greater Accra.</p>

    <h2>Delivery</h2>
    <ul>
        <li>Orders are delivered in 1 to 3 working days.</li>
        <li>Orders are delivered by our delivery partner, a third-party delivery service.</li>
        <li>The delivery charge is not paid on this website. Our delivery partner tells you the charge when they contact you to arrange delivery.</li>
        <li>We email you when your order is out for delivery, and the rider calls you on arrival.</li>
    </ul>

    <h2>Returns</h2>
    <ul>
        <li>Unopened products can be returned within 14 days of delivery.</li>
        <li>If a product arrives damaged, send us a photo within 48 hours and we will replace it.</li>
        <li>To start a return, email <a href="mailto:<?php echo e(CC_CONTACT_EMAIL); ?>"><?php echo e(CC_CONTACT_EMAIL); ?></a> with your order number.</li>
    </ul>

    <h2>Refunds</h2>
    <p>Refunds are made to your original payment method (mobile money or card) within 5 working days of us receiving the return.</p>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

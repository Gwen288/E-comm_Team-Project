<?php
require_once __DIR__ . '/../../core/core.php';

$pageTitle = 'Privacy policy';
$pageDescription = 'How CrownCare collects, uses and protects your personal data.';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-info">
    <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>

    <h1>Privacy policy</h1>
    <p class="cc-updated">Last updated 22 November 2026</p>

    <p>This policy explains what personal data CrownCare collects when you use this website, why we collect it and how we protect it. We follow the Data Protection Act, 2012 (Act 843) of Ghana.</p>

    <h2>What we collect</h2>
    <ul>
        <li><strong>Your account:</strong> your name, email address and phone number.</li>
        <li><strong>Your orders:</strong> your email address, name and phone number, the products you buy, your delivery address and notes, and payment references. We keep these whether you check out with an account or as a guest, so we can follow up on your order. Card and mobile money details are handled by Paystack; we never see or store them.</li>
        <li><strong>Consultations:</strong> the details you give when booking, and notes our specialists make during your consultation.</li>
        <li><strong>Hair quiz:</strong> your answers, so we can recommend products. If you are signed in, we save them to your account.</li>
        <li><strong>Security:</strong> failed sign in attempts and the network address they came from, kept for one day to protect accounts.</li>
    </ul>

    <h2>How we use it</h2>
    <ul>
        <li>To deliver your orders and keep you informed by email about them.</li>
        <li>To manage your consultations and send confirmations and reminders.</li>
        <li>To recommend products that suit your hair.</li>
        <li>To keep sales records, as the law requires.</li>
    </ul>
    <p>We do not sell your data, and we do not send marketing emails.</p>

    <h2>Who we share it with</h2>
    <ul>
        <li><strong>Paystack</strong>, to process your payment.</li>
        <li><strong>Our delivery partner</strong>, a third-party delivery service, which receives your name, phone number and delivery address for your order only.</li>
        <li><strong>Our email provider</strong>, to send you emails about your account, orders and consultations.</li>
    </ul>

    <h2>Cookies</h2>
    <p>We use one cookie, which keeps you signed in and remembers your cart while you browse. It is deleted when you close your browser. We do not use advertising or tracking cookies.</p>

    <h2>How long we keep it</h2>
    <p>We keep your account details until you close your account. When you close it, we remove your name, email address, phone number, cart and hair quiz answers. We keep records of past orders and payments, without your personal details, for as long as the law requires for sales records.</p>

    <h2>Your rights</h2>
    <p>You can see and correct your details in Account settings, and close your account at any time. You can also ask us for a copy of the data we hold about you, or ask us to correct or delete it, by emailing <a href="mailto:<?php echo e(CC_CONTACT_EMAIL); ?>"><?php echo e(CC_CONTACT_EMAIL); ?></a>. If you are not satisfied with our answer, you can contact the Data Protection Commission of Ghana.</p>

    <h2>Contact</h2>
    <p>CrownCare, <?php echo e(CC_STUDIO_LOCATION); ?>. Email <a href="mailto:<?php echo e(CC_CONTACT_EMAIL); ?>"><?php echo e(CC_CONTACT_EMAIL); ?></a> or call <?php echo e(CC_CONTACT_PHONE); ?>.</p>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

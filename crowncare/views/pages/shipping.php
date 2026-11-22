<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../classes/DeliveryClass.php';

$areas = (new DeliveryClass())->getActiveAreas();
$fees = array_map('floatval', array_column($areas, 'delivery_fee'));

$pageTitle = 'Delivery and returns';
$pageDescription = 'CrownCare delivery across Greater Accra, delivery fees, returns and refunds.';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-info">
    <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>

    <h1>Delivery and returns</h1>
    <p class="cc-info-lead">We deliver across Greater Accra.</p>

    <h2>Delivery</h2>
    <ul>
        <li>Orders are delivered in 1 to 3 working days.</li>
        <?php if ($fees): ?>
            <li>The delivery fee depends on your area, from <?php echo e(cc_money(min($fees))); ?> to <?php echo e(cc_money(max($fees))); ?>. You see the exact fee at checkout.</li>
        <?php endif; ?>
        <li>Orders from <?php echo e(cc_money(FREE_DELIVERY_THRESHOLD)); ?> are delivered free.</li>
        <li>We email you when your order is out for delivery, and the rider calls you on arrival.</li>
    </ul>

    <?php if ($areas): ?>
        <h3>Areas we deliver to</h3>
        <div class="cc-table-wrap">
            <table class="cc-table">
                <thead>
                    <tr><th scope="col">Area</th><th scope="col" class="is-right">Delivery fee</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($areas as $area): ?>
                        <tr>
                            <td><?php echo e($area['area_name']); ?></td>
                            <td class="is-right"><?php echo e(cc_money($area['delivery_fee'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

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

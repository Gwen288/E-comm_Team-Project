<?php

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';
require_once __DIR__ . '/../../controllers/OrderController.php';
require_once __DIR__ . '/../../classes/ConsultationClass.php';
require_once __DIR__ . '/../../classes/RecommendationClass.php';

require_customer();

$customerId = get_customer_id();
$customer = (new CustomerController())->getCustomer($customerId);

if (!$customer) {
    cc_force_sign_out('We could not find your account. Please sign in again.');
}

$orders = array_slice((new OrderController())->getOrdersForCustomer($customerId), 0, 3);

$nextBooking = null;
foreach ((new ConsultationClass())->forCustomer($customerId) as $booking) {
    if ($booking['status'] === 'booked' && strtotime($booking['consultation_date'] . ' ' . $booking['consultation_time']) >= time()) {
        $nextBooking = $booking;
        break;
    }
}

// The products recommended by the customer's latest hair quiz.
$recommender = new RecommendationClass();
$savedReco = $recommender->getSaved($customerId);
$savedRoutine = $savedReco ? $recommender->routineFromSaved($savedReco)['routine'] : [];

$firstName = strtok($customer['customer_name'], ' ');

$pageTitle = 'My account';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-account">
    <div class="cc-account-head">
        <div>
            <h1>My account</h1>
            <p>Welcome back, <?php echo e($firstName); ?>.</p>
        </div>

        <div class="cc-account-actions">
            <a class="cc-settings-link" href="<?php echo BASE_URL; ?>views/account/edit_account.php">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
                Account settings
            </a>
            <?php $signoutClass = 'cc-settings-link cc-signout-button'; require __DIR__ . '/../layout/signout_form.php'; ?>
        </div>
    </div>

    <div class="cc-account-grid">
        <section class="cc-panel" aria-labelledby="details-title">
            <h2 id="details-title">Your details</h2>
            <dl class="cc-details">
                <div><dt>Name</dt><dd><?php echo e($customer['customer_name']); ?></dd></div>
                <div><dt>Email</dt><dd><?php echo e($customer['customer_email']); ?></dd></div>
                <div><dt>Phone</dt><dd><?php echo e($customer['customer_contact']); ?></dd></div>
                <div><dt>Country</dt><dd>Ghana</dd></div>
            </dl>
        </section>

        <section class="cc-panel" aria-labelledby="bookings-title">
            <div class="cc-panel-head">
                <h2 id="bookings-title">Consultations</h2>
                <a class="cc-link" href="<?php echo BASE_URL; ?>views/account/bookings.php">View all</a>
            </div>

            <?php if (!$nextBooking): ?>
                <p class="cc-empty">You have no upcoming consultation.</p>
                <p><a class="cc-button cc-button--small cc-button--outline" href="<?php echo BASE_URL; ?>views/book_consultation.php">Book a consultation</a></p>
            <?php else: ?>
                <dl class="cc-details">
                    <div><dt>Next</dt><dd><?php echo e($nextBooking['service']); ?></dd></div>
                    <div><dt>Date</dt><dd><?php echo e(date('l j F Y', strtotime($nextBooking['consultation_date']))); ?></dd></div>
                    <div><dt>Time</dt><dd><?php echo e(date('g:i a', strtotime($nextBooking['consultation_time']))); ?></dd></div>
                    <div><dt>Reference</dt><dd><?php echo e($nextBooking['reference']); ?></dd></div>
                </dl>
            <?php endif; ?>
        </section>
        <section class="cc-panel cc-panel--wide" aria-labelledby="reco-title">
            <div class="cc-panel-head">
                <h2 id="reco-title">Your recommended products</h2>
                <?php if ($savedRoutine): ?>
                    <a class="cc-link" href="<?php echo BASE_URL; ?>views/hair_quiz.php">Retake the quiz</a>
                <?php endif; ?>
            </div>

            <?php if (!$savedRoutine): ?>
                <p class="cc-empty">Take the hair quiz and the products recommended for you will be saved here.</p>
                <p><a class="cc-button cc-button--small cc-button--outline" href="<?php echo BASE_URL; ?>views/hair_quiz.php">Take the hair quiz</a></p>
            <?php else: ?>
                <p class="cc-reco-saved">From your hair quiz on <?php echo e(date('j F Y', strtotime($savedReco['saved_at']))); ?>.</p>
                <ul class="cc-reco-mini">
                    <?php foreach ($savedRoutine as $item): ?>
                        <li>
                            <span class="cc-reco-step"><?php echo e($item['step']); ?></span>
                            <a class="cc-link" href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $item['product']['product_id']; ?>&amp;from=recommendations"><?php echo e($item['product']['product_title']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><a class="cc-button cc-button--small" href="<?php echo BASE_URL; ?>views/recommendations.php">View and add to cart</a></p>
            <?php endif; ?>
        </section>

        <section class="cc-panel cc-panel--wide" aria-labelledby="orders-title">
            <div class="cc-panel-head">
                <h2 id="orders-title">Recent orders</h2>
                <?php if ($orders): ?>
                    <a class="cc-link" href="<?php echo BASE_URL; ?>views/account/orders.php">View all</a>
                <?php endif; ?>
            </div>

            <?php if (!$orders): ?>
                <p class="cc-empty">You have not placed an order yet.</p>
                <p><a class="cc-button cc-button--small" href="<?php echo BASE_URL; ?>views/shop.php">Start shopping</a></p>
            <?php else: ?>
                <div class="cc-table-wrap">
                    <table class="cc-table">
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><a class="cc-link" href="<?php echo BASE_URL; ?>views/account/order.php?id=<?php echo (int) $order['order_id']; ?>"><?php echo e(cc_order_number($order)); ?></a></td>
                                    <td><?php echo e(date('j M Y', strtotime($order['order_date']))); ?></td>
                                    <td><span class="<?php echo cc_order_status_class($order['order_status']); ?>"><?php echo e(cc_order_status_label($order['order_status'])); ?></span></td>
                                    <td class="is-right"><?php echo e(cc_money($order['total_amount'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<?php
// One order in the customer's account: items, totals, delivery details and progress.

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

require_customer();

$controller = new OrderController();
// Passing the session customer along as well stops someone opening another
// person's order by changing ?id= in the address.
$order = $controller->getOrderForCustomer((int) ($_GET['id'] ?? 0), get_customer_id());

if (!$order) {
    cc_not_found();
}

// The lines saved with the order, not the live cart, which may have changed.
$items = $controller->getOrderItems((int) $order['order_id']);
$status = $order['order_status'];
// Only paid orders have a progress bar; 'paid' is the first step in CC_ORDER_STEPS.
$stepIndex = array_search($status, CC_ORDER_STEPS, true);

$pageTitle = 'Order ' . cc_order_number($order);
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-account">
    <div class="cc-page-nav">
        <a class="cc-back" href="<?php echo BASE_URL; ?>views/account/orders.php">Back to my orders</a>
        <div class="cc-page-nav-links">
            <a href="<?php echo BASE_URL; ?>views/shop.php">Continue shopping</a>
        </div>
    </div>

    <div class="cc-account-head">
        <div>
            <h1>Order <?php echo e(cc_order_number($order)); ?></h1>
            <p>Placed on <?php echo e(date('j F Y', strtotime($order['order_date']))); ?></p>
        </div>
        <span class="<?php echo cc_order_status_class($status); ?>"><?php echo e(cc_order_status_label($status)); ?></span>
    </div>

    <?php if ($stepIndex !== false): ?>
        <ol class="cc-steps" aria-label="Order progress">
            <?php foreach (CC_ORDER_STEPS as $i => $step): ?>
                <li class="<?php echo $i <= $stepIndex ? 'is-done' : ''; ?>"><?php echo e(cc_order_status_label($step)); ?></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <div class="cc-account-grid">
        <section class="cc-panel" aria-labelledby="items-title">
            <h2 id="items-title">Items</h2>
            <div class="cc-order-items">
                <?php /* The image helper picks the picture for this size, if there is one. */ ?>
                <?php foreach ($items as $item): ?>
                    <?php $image = cc_cart_image($item, (string) ($item['size_name'] ?? '')); ?>
                    <div class="cc-order-item">
                        <?php if ($image): ?>
                            <img src="<?php echo e($image); ?>" alt="">
                        <?php else: ?>
                            <span aria-hidden="true"></span>
                        <?php endif; ?>
                        <div>
                            <a class="cc-link" href="<?php echo BASE_URL; ?>views/single_product.php?pro_id=<?php echo (int) $item['product_id']; ?>"><?php echo e($item['product_title']); ?></a>
                            <small>
                                <?php echo !empty($item['size_name']) ? e(cc_size_label($item['size_name'])) . ', ' : ''; ?>
                                quantity <?php echo (int) $item['qty']; ?>
                            </small>
                        </div>
                        <strong><?php echo e(cc_money((int) $item['qty'] * (float) $item['product_price'])); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cc-totals">
                <div><span>Subtotal</span><span><?php echo e(cc_money($order['subtotal'])); ?></span></div>
                <?php if ((float) $order['delivery_fee'] > 0): /* older orders paid a delivery fee at checkout */ ?>
                    <div><span>Delivery</span><span><?php echo e(cc_money($order['delivery_fee'])); ?></span></div>
                <?php endif; ?>
                <div class="is-total"><span>Total</span><span><?php echo e(cc_money($order['total_amount'])); ?></span></div>
            </div>
        </section>

        <section class="cc-panel" aria-labelledby="delivery-title">
            <h2 id="delivery-title">Delivery</h2>
            <dl class="cc-details">
                <div><dt>Name</dt><dd><?php echo e($order['delivery_name']); ?></dd></div>
                <div><dt>Phone</dt><dd><?php echo e($order['delivery_phone']); ?></dd></div>
                <div><dt>Address</dt><dd><?php echo e($order['delivery_address']); ?></dd></div>
                <?php if (!empty($order['delivery_city'])): ?>
                    <div><dt>Area</dt><dd><?php echo e($order['delivery_city']); ?></dd></div>
                <?php endif; ?>
                <?php if (!empty($order['delivery_notes'])): ?>
                    <div><dt>Notes</dt><dd><?php echo e($order['delivery_notes']); ?></dd></div>
                <?php endif; ?>
                <?php if (!empty($order['paid_at'])): ?>
                    <div><dt>Paid</dt><dd><?php echo e(date('j F Y, g:i a', strtotime($order['paid_at']))); ?></dd></div>
                <?php endif; ?>
            </dl>
            <?php /* Unpaid orders never left the cart, so send the customer back there to retry. */ ?>
            <?php if ($status === 'pending' || $status === 'failed'): ?>
                <p class="cc-hint" style="margin-top:16px">This order was not paid. The items are still in your cart if you would like to try again.</p>
                <p><a class="cc-button cc-button--small" href="<?php echo BASE_URL; ?>views/cart.php">Go to cart</a></p>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

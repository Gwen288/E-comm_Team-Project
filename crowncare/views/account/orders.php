<?php

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

require_customer();

$orders = (new OrderController())->getOrdersForCustomer(get_customer_id());

$pageTitle = 'My orders';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-account">
    <a class="cc-back" href="<?php echo BASE_URL; ?>views/account/my_account.php">Back to my account</a>

    <div class="cc-account-head">
        <div>
            <h1>My orders</h1>
        </div>
    </div>

    <section class="cc-panel">
        <?php if (!$orders): ?>
            <p class="cc-empty">You have not placed an order yet.</p>
            <p><a class="cc-button cc-button--small" href="<?php echo BASE_URL; ?>views/shop.php">Start shopping</a></p>
        <?php else: ?>
            <div class="cc-table-wrap">
                <table class="cc-table">
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Date</th>
                            <th scope="col">Items</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="is-right">Total</th>
                            <th scope="col"><span class="visually-hidden">View</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php $orderUrl = BASE_URL . 'views/account/order.php?id=' . (int) $order['order_id']; ?>
                            <tr>
                                <td><a class="cc-link" href="<?php echo $orderUrl; ?>"><?php echo e(cc_order_number($order)); ?></a></td>
                                <td><?php echo e(date('j M Y', strtotime($order['order_date']))); ?></td>
                                <td><?php echo (int) $order['item_count']; ?></td>
                                <td><span class="<?php echo cc_order_status_class($order['order_status']); ?>"><?php echo e(cc_order_status_label($order['order_status'])); ?></span></td>
                                <td class="is-right"><?php echo e(cc_money($order['total_amount'])); ?></td>
                                <td class="is-right"><a class="cc-link" href="<?php echo $orderUrl; ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

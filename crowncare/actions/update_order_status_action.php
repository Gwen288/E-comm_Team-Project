<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/OrderController.php';

require_admin();

$orderId = (int) ($_POST['order_id'] ?? 0);
$status = (string) ($_POST['status'] ?? '');
$page = BASE_URL . 'views/admin/order_view.php?id=' . $orderId;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset(CC_ORDER_STATUSES[$status])) {
    redirect(BASE_URL . 'views/admin/orders.php');
}

$result = (new OrderController())->updateStatus($orderId, $status);

if ($result === true) {
    $_SESSION['success'] = 'Order updated to "' . cc_order_status_label($status) . '". The customer has been emailed.';
} else {
    $_SESSION['error'] = $result;
}

redirect($page);

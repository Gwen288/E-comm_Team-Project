<?php

require_once __DIR__ . '/../classes/OrderClass.php';
require_once __DIR__ . '/../classes/StockClass.php';
require_once __DIR__ . '/../classes/CartClass.php';
require_once __DIR__ . '/../classes/OrderEmail.php';

class OrderController
{
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new OrderClass();
    }

    public function createOrder(
        $customerId,
        $items,
        $delivery,
        $subtotal,
        $deliveryFee,
        $total
    ) {
        return $this->orderModel->createOrder(
            $customerId,
            $items,
            $delivery,
            $subtotal,
            $deliveryFee,
            $total
        );
    }

    public function getOrderById($orderId)
    {
        return $this->orderModel->getOrderById($orderId);
    }

    public function getAllOrders()
    {
        return $this->orderModel->getAllOrders();
    }

    public function getOrderCount()
    {
        return $this->orderModel->getOrderCount();
    }

    public function getPaidOrderCount()
    {
        return $this->orderModel->getPaidOrderCount();
    }

    public function getOrderWithCustomer($orderId)
    {
        return $this->orderModel->getOrderWithCustomer($orderId);
    }

    public function getOrderForCustomer($orderId, $customerId)
    {
        return $this->orderModel->getOrderForCustomer(
            $orderId,
            $customerId
        );
    }

    public function getOrderByReference($reference)
    {
        return $this->orderModel->getOrderByReference($reference);
    }

    public function getOrderItems($orderId)
    {
        return $this->orderModel->getOrderItems($orderId);
    }

    public function setPaymentReference($orderId, $reference)
    {
        return $this->orderModel->setPaymentReference(
            $orderId,
            $reference
        );
    }

    public function markPaid($orderId, $reference, $channel, $paidAt)
    {
        return $this->orderModel->markPaid(
            $orderId,
            $reference,
            $channel,
            $paidAt
        );
    }

    public function markFailed($orderId, $reference)
    {
        return $this->orderModel->markFailed($orderId, $reference);
    }

    public function getOrdersForCustomer($customerId)
    {
        return $this->orderModel->getOrdersForCustomer((int) $customerId);
    }

    /**
     * Everything that happens once a payment is confirmed (from the browser
     * returning from Paystack, or from Paystack's webhook): record the payment,
     * take the items out of stock, remove them from the customer's cart and
     * email the receipt. Safe to call twice: only the first call does the work.
     * Returns true when the order is paid.
     */
    public function completePayment($order, $reference, $channel, $paidAt)
    {
        $orderId = (int) $order['order_id'];
        $result = $this->orderModel->markPaid($orderId, $reference, $channel, $paidAt);

        if ($result === false) {
            return false;
        }

        if ($result === 'new') {
            (new StockClass())->deductForOrder($orderId);

            $items = $this->orderModel->getOrderItems($orderId);
            $cart = new CartClass();
            foreach ($items as $item) {
                $cart->removeFromCart((int) $item['product_id'], (int) $order['customer_id'], (int) $item['size_id']);
            }

            $paidOrder = $this->orderModel->getOrderWithCustomer($orderId);
            if ($paidOrder) {
                OrderEmail::sendConfirmation($paidOrder, $items);
            }
        }

        return true;
    }

    /**
     * Admin status change. Cancelling a paid order puts its items back in
     * stock. The customer is emailed about each change.
     * Returns true, or an error message.
     */
    public function updateStatus($orderId, $newStatus)
    {
        $orderId = (int) $orderId;

        if (!$this->orderModel->updateStatus($orderId, $newStatus)) {
            return 'This order cannot be moved to that status.';
        }

        if ($newStatus === 'cancelled') {
            (new StockClass())->restockForOrder($orderId);
        }

        $order = $this->orderModel->getOrderWithCustomer($orderId);
        if ($order && in_array($newStatus, ['processing', 'out_for_delivery', 'delivered', 'cancelled'], true)) {
            OrderEmail::sendStatusUpdate($order, $this->orderModel->getOrderItems($orderId));
        }

        return true;
    }
}

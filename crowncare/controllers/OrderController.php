<?php

require_once __DIR__ . '/../classes/OrderClass.php';

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
}

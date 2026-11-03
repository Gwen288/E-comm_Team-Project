<?php

require_once __DIR__ . '/../core/db_class.php';

class OrderClass extends Database
{
    /**
     * Create an order and its line items in one transaction.
     *
     * @return int|false New order ID, or false on failure.
     */
    public function createOrder(
        $customerId,
        $items,
        $delivery,
        $subtotal,
        $deliveryFee,
        $total
    ) {
        if (empty($items)) {
            return false;
        }

        $this->conn->begin_transaction();

        try {
            $invoiceNo = 0;
            $orderDate = date('Y-m-d');
            $orderStatus = 'pending';
            $paymentStatus = 'pending';

            $notes = trim($delivery['notes'] ?? '');
            if ($notes === '') {
                $notes = null;
            }

            $deliveryName = $delivery['name'];
            $deliveryPhone = $delivery['phone'];
            $deliveryCity = $delivery['city'];
            $deliveryAddress = $delivery['address'];

            $sql = "INSERT INTO orders
                    (
                        customer_id,
                        invoice_no,
                        order_date,
                        order_status,
                        delivery_name,
                        delivery_phone,
                        delivery_city,
                        delivery_address,
                        delivery_notes,
                        subtotal,
                        delivery_fee,
                        total_amount,
                        payment_status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);

            $stmt->bind_param(
                "iisssssssddds",
                $customerId,
                $invoiceNo,
                $orderDate,
                $orderStatus,
                $deliveryName,
                $deliveryPhone,
                $deliveryCity,
                $deliveryAddress,
                $notes,
                $subtotal,
                $deliveryFee,
                $total,
                $paymentStatus
            );

            if (!$stmt->execute()) {
                throw new Exception('Could not create the order.');
            }

            $orderId = (int) $this->conn->insert_id;
            $stmt->close();

            // Use the order ID as the invoice number.
            $stmt = $this->conn->prepare(
                "UPDATE orders SET invoice_no = ? WHERE order_id = ?"
            );
            $stmt->bind_param("ii", $orderId, $orderId);
            $stmt->execute();
            $stmt->close();

            $detailSql = "INSERT INTO orderdetails (order_id, product_id, qty)
                          VALUES (?, ?, ?)";
            $detailStmt = $this->conn->prepare($detailSql);

            foreach ($items as $item) {
                $productId = (int) $item['p_id'];
                $qty = (int) $item['qty'];

                $detailStmt->bind_param("iii", $orderId, $productId, $qty);

                if (!$detailStmt->execute()) {
                    throw new Exception('Could not save an order item.');
                }
            }

            $detailStmt->close();

            $this->conn->commit();

            return $orderId;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Order creation failed: ' . $e->getMessage());

            return false;
        }
    }


    public function getOrderById($orderId)
    {
        $sql = "SELECT * FROM orders WHERE order_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        $result = $stmt->get_result();
        $order = $result->num_rows === 1 ? $result->fetch_assoc() : false;

        $stmt->close();

        return $order;
    }


    public function getOrderForCustomer($orderId, $customerId)
    {
        $sql = "SELECT *
                FROM orders
                WHERE order_id = ? AND customer_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $orderId, $customerId);
        $stmt->execute();

        $result = $stmt->get_result();
        $order = $result->num_rows === 1 ? $result->fetch_assoc() : false;

        $stmt->close();

        return $order;
    }


    public function getOrderByReference($reference)
    {
        $sql = "SELECT *
                FROM orders
                WHERE payment_reference = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $reference);
        $stmt->execute();

        $result = $stmt->get_result();
        $order = $result->num_rows === 1 ? $result->fetch_assoc() : false;

        $stmt->close();

        return $order;
    }


    public function getOrderItems($orderId)
    {
        $sql = "SELECT
                    orderdetails.product_id,
                    orderdetails.qty,
                    products.product_title,
                    products.product_price,
                    products.product_image
                FROM orderdetails
                INNER JOIN products
                    ON orderdetails.product_id = products.product_id
                WHERE orderdetails.order_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $items;
    }


    public function setPaymentReference($orderId, $reference)
    {
        $sql = "UPDATE orders
                SET payment_reference = ?
                WHERE order_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $reference, $orderId);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    /**
     * Mark an order as paid and record the successful payment.
     */
    public function markPaid($orderId, $reference, $channel, $paidAt)
    {
        $order = $this->getOrderById($orderId);

        if ($order === false) {
            return false;
        }

        // Callback pages may be refreshed; do not record the payment twice.
        if ($order['payment_status'] === 'paid') {
            return true;
        }

        $paidAt = $paidAt !== null && $paidAt !== ''
            ? $paidAt
            : date('Y-m-d H:i:s');

        $paymentDate = date('Y-m-d', strtotime($paidAt));

        $amount = $order['total_amount'];
        $orderCustomerId = $order['customer_id'];
        $currency = PAYSTACK_CURRENCY;

        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare(
                "UPDATE orders
                 SET payment_status = 'paid',
                     order_status = 'paid'
                 WHERE order_id = ?"
            );
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $stmt->close();

            $sql = "INSERT INTO payment
                    (
                        amt,
                        customer_id,
                        order_id,
                        currency,
                        reference,
                        status,
                        channel,
                        paid_at,
                        payment_date
                    )
                    VALUES (?, ?, ?, ?, ?, 'success', ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        amt = VALUES(amt),
                        status = 'success',
                        channel = VALUES(channel),
                        paid_at = VALUES(paid_at)";

            $stmt = $this->conn->prepare($sql);

            $stmt->bind_param(
                "diisssss",
                $amount,
                $orderCustomerId,
                $orderId,
                $currency,
                $reference,
                $channel,
                $paidAt,
                $paymentDate
            );

            $stmt->execute();
            $stmt->close();

            $this->conn->commit();

            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Payment recording failed: ' . $e->getMessage());

            return false;
        }
    }


    /**
     * Mark an order as failed when Paystack does not confirm payment.
     */
    public function markFailed($orderId, $reference)
    {
        $order = $this->getOrderById($orderId);

        if ($order === false) {
            return false;
        }

        if ($order['payment_status'] === 'paid') {
            return true;
        }

        $paymentDate = date('Y-m-d');

        $amount = $order['total_amount'];
        $orderCustomerId = $order['customer_id'];
        $currency = PAYSTACK_CURRENCY;

        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare(
                "UPDATE orders
                 SET payment_status = 'failed',
                     order_status = 'failed'
                 WHERE order_id = ?"
            );
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $stmt->close();

            $sql = "INSERT INTO payment
                    (
                        amt,
                        customer_id,
                        order_id,
                        currency,
                        reference,
                        status,
                        payment_date
                    )
                    VALUES (?, ?, ?, ?, ?, 'failed', ?)
                    ON DUPLICATE KEY UPDATE
                        status = 'failed'";

            $stmt = $this->conn->prepare($sql);

            $stmt->bind_param(
                "diisss",
                $amount,
                $orderCustomerId,
                $orderId,
                $currency,
                $reference,
                $paymentDate
            );

            $stmt->execute();
            $stmt->close();

            $this->conn->commit();

            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Failed payment recording failed: ' . $e->getMessage());

            return false;
        }
    }
}

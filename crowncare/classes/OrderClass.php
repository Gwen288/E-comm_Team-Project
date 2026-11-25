<?php

require_once __DIR__ . '/../core/db_class.php';

class OrderClass extends Database
{
    /**
     * Create an order and its line items in one transaction.
     * $customerId is null for a guest order. $delivery['email'] is where the
     * receipt and status emails go.
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

            $customerId = $customerId !== null && (int) $customerId > 0 ? (int) $customerId : null;
            $contactEmail = $delivery['email'];
            $deliveryName = $delivery['name'];
            $deliveryPhone = $delivery['phone'];
            $deliveryCity = $delivery['city'];
            $deliveryAddress = $delivery['address'];
            $deliveryAreaId = isset($delivery['area_id']) ? (int) $delivery['area_id'] : null;
            $now = date('Y-m-d H:i:s');

            $sql = "INSERT INTO cc_orders
                    (
                        customer_id,
                        contact_email,
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
                        payment_status,
                        delivery_area_id,
                        status_updated_at
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);

            $stmt->bind_param(
                "isisssssssdddsis",
                $customerId,
                $contactEmail,
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
                $paymentStatus,
                $deliveryAreaId,
                $now
            );

            if (!$stmt->execute()) {
                throw new Exception('Could not create the order.');
            }

            $orderId = (int) $this->conn->insert_id;
            $stmt->close();

            // Use the order ID as the invoice number.
            $stmt = $this->conn->prepare(
                "UPDATE cc_orders SET invoice_no = ? WHERE order_id = ?"
            );
            $stmt->bind_param("ii", $orderId, $orderId);
            $stmt->execute();
            $stmt->close();

            // Each line keeps the chosen size and the price paid for it.
            $detailSql = "INSERT INTO cc_orderdetails (order_id, product_id, size_id, qty, unit_price)
                          VALUES (?, ?, ?, ?, ?)";
            $detailStmt = $this->conn->prepare($detailSql);

            foreach ($items as $item) {
                $productId = (int) $item['p_id'];
                $sizeId = (int) ($item['size_id'] ?? 0);
                $qty = (int) $item['qty'];
                $unitPrice = (float) $item['product_price'];

                $detailStmt->bind_param("iiiid", $orderId, $productId, $sizeId, $qty, $unitPrice);

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
        $sql = "SELECT * FROM cc_orders WHERE order_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        $result = $stmt->get_result();
        $order = $result->num_rows === 1 ? $result->fetch_assoc() : false;

        $stmt->close();

        return $order;
    }


    /**
     * All orders for the admin store list (newest first).
     */
    public function getAllOrders()
    {
        $sql = "SELECT
                    o.*,
                    COALESCE(c.customer_name, o.delivery_name) AS customer_name,
                    COALESCE(o.contact_email, c.customer_email) AS customer_email,
                    COALESCE(c.customer_contact, o.delivery_phone) AS customer_contact,
                    (o.customer_id IS NULL) AS is_guest
                FROM cc_orders AS o
                LEFT JOIN cc_customer AS c
                    ON o.customer_id = c.customer_id
                ORDER BY o.order_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $orders;
    }


    public function getOrderCount()
    {
        $sql = "SELECT COUNT(*) AS total FROM cc_orders";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) ($row['total'] ?? 0);
    }


    /** Total of all paid orders. */
    public function getPaidRevenue()
    {
        $row = $this->conn->query(
            "SELECT COALESCE(SUM(total_amount), 0) AS total FROM cc_orders WHERE payment_status = 'paid'"
        )->fetch_assoc();

        return (float) ($row['total'] ?? 0);
    }


    public function getPaidOrderCount()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM cc_orders
                WHERE payment_status = 'paid'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) ($row['total'] ?? 0);
    }


    public function getOrderWithCustomer($orderId)
    {
        $orderId = (int) $orderId;

        $sql = "SELECT
                    o.*,
                    COALESCE(c.customer_name, o.delivery_name) AS customer_name,
                    COALESCE(o.contact_email, c.customer_email) AS customer_email,
                    COALESCE(c.customer_contact, o.delivery_phone) AS customer_contact,
                    c.customer_city AS customer_city,
                    c.customer_status,
                    (o.customer_id IS NULL) AS is_guest
                FROM cc_orders AS o
                LEFT JOIN cc_customer AS c
                    ON o.customer_id = c.customer_id
                WHERE o.order_id = ?
                LIMIT 1";

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
                FROM cc_orders
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


    /** A guest order (one placed without an account). */
    public function getGuestOrder($orderId)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM cc_orders WHERE order_id = ? AND customer_id IS NULL LIMIT 1"
        );
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $order ?: false;
    }


    public function getOrderByReference($reference)
    {
        $sql = "SELECT *
                FROM cc_orders
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
                    orderdetails.size_id,
                    orderdetails.qty,
                    products.product_title,
                    COALESCE(orderdetails.unit_price, products.product_price) AS product_price,
                    products.product_image,
                    sizes.size_name
                FROM cc_orderdetails orderdetails
                INNER JOIN cc_products products
                    ON orderdetails.product_id = products.product_id
                LEFT JOIN cc_product_sizes sizes
                    ON sizes.size_id = orderdetails.size_id
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
        $sql = "UPDATE cc_orders
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

        // The callback page may be refreshed, and Paystack's webhook may arrive
        // at the same time: only the first one records the payment.
        if ($order['payment_status'] === 'paid') {
            return 'already';
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
                "UPDATE cc_orders
                 SET payment_status = 'paid',
                     order_status = 'paid',
                     paid_at = ?,
                     status_updated_at = NOW()
                 WHERE order_id = ? AND payment_status <> 'paid'"
            );
            $stmt->bind_param("si", $paidAt, $orderId);
            $stmt->execute();
            $firstTime = $stmt->affected_rows === 1;
            $stmt->close();

            if (!$firstTime) {
                $this->conn->rollback();
                return 'already';
            }

            $sql = "INSERT INTO cc_payment
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

            return 'new';
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
                "UPDATE cc_orders
                 SET payment_status = 'failed',
                     order_status = 'failed',
                     status_updated_at = NOW()
                 WHERE order_id = ? AND payment_status <> 'paid'"
            );
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $stmt->close();

            $sql = "INSERT INTO cc_payment
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


    /** A customer's orders, newest first, with the number of items in each. */
    public function getOrdersForCustomer($customerId)
    {
        $stmt = $this->conn->prepare(
            "SELECT o.*,
                    (SELECT COALESCE(SUM(d.qty), 0) FROM cc_orderdetails d WHERE d.order_id = o.order_id) AS item_count
             FROM cc_orders o
             WHERE o.customer_id = ?
             ORDER BY o.order_id DESC"
        );
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $orders;
    }

    /** Changes an order's status. Only the moves in CC_ORDER_NEXT_STATUSES are allowed. */
    public function updateStatus($orderId, $newStatus)
    {
        $order = $this->getOrderById($orderId);
        if (!$order) {
            return false;
        }

        $current = $order['order_status'];
        if (!in_array($newStatus, CC_ORDER_NEXT_STATUSES[$current] ?? [], true)) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "UPDATE cc_orders SET order_status = ?, status_updated_at = NOW()
             WHERE order_id = ? AND order_status = ?"
        );
        $stmt->bind_param("sis", $newStatus, $orderId, $current);
        $stmt->execute();
        $changed = $stmt->affected_rows === 1;
        $stmt->close();

        return $changed;
    }
}

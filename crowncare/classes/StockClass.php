<?php

require_once __DIR__ . '/../core/db_class.php';

/*
 * Stock levels. Products sold in sizes keep stock per size
 * (cc_product_size_options.stock_qty); other products keep it on the product
 * (cc_products.product_stock). Stock goes down once, when an order is paid,
 * and goes back up if a paid order is cancelled.
 * OrderController does the deduct/restock calls; the admin pages use the
 * read helpers and the low-stock list.
 */
class StockClass extends Database
{
    /** Units available for a product and size (size 0 = product without sizes). */
    public function getAvailable($productId, $sizeId)
    {
        $productId = (int) $productId;
        $sizeId = (int) $sizeId;

        if ($sizeId > 0) {
            $stmt = $this->conn->prepare(
                "SELECT stock_qty FROM cc_product_size_options WHERE product_id = ? AND size_id = ?"
            );
            $stmt->bind_param("ii", $productId, $sizeId);
        } else {
            $stmt = $this->conn->prepare("SELECT product_stock AS stock_qty FROM cc_products WHERE product_id = ?");
            $stmt->bind_param("i", $productId);
        }

        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? max(0, (int) $row['stock_qty']) : 0;
    }

    /** Total stock of a product across its sizes (or its own stock). */
    public function getProductTotal($productId)
    {
        $productId = (int) $productId;

        $stmt = $this->conn->prepare(
            "SELECT
                (SELECT COUNT(*) FROM cc_product_size_options WHERE product_id = ?) AS size_count,
                (SELECT COALESCE(SUM(stock_qty), 0) FROM cc_product_size_options WHERE product_id = ?) AS size_stock,
                (SELECT product_stock FROM cc_products WHERE product_id = ?) AS product_stock"
        );
        $stmt->bind_param("iii", $productId, $productId, $productId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) $row['size_count'] > 0 ? (int) $row['size_stock'] : (int) $row['product_stock'];
    }

    /** Stock of each size of a product: [size_id => qty]. */
    public function getSizeStock($productId)
    {
        $productId = (int) $productId;
        $stmt = $this->conn->prepare("SELECT size_id, stock_qty FROM cc_product_size_options WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stock = [];
        foreach ($rows as $row) {
            $stock[(int) $row['size_id']] = max(0, (int) $row['stock_qty']);
        }
        return $stock;
    }

    /** Sets stock on the size, or on the product when size 0 is passed. */
    public function setStock($productId, $sizeId, $qty)
    {
        $productId = (int) $productId;
        $sizeId = (int) $sizeId;
        $qty = max(0, (int) $qty);

        if ($sizeId > 0) {
            $stmt = $this->conn->prepare(
                "UPDATE cc_product_size_options SET stock_qty = ? WHERE product_id = ? AND size_id = ?"
            );
            $stmt->bind_param("iii", $qty, $productId, $sizeId);
        } else {
            $stmt = $this->conn->prepare("UPDATE cc_products SET product_stock = ? WHERE product_id = ?");
            $stmt->bind_param("ii", $qty, $productId);
        }
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Takes an order's items out of stock, once. Returns true if stock was
     * taken now, false if it had already been taken.
     */
    public function deductForOrder($orderId)
    {
        return $this->moveStockForOrder((int) $orderId, -1);
    }

    /** Puts a cancelled order's items back into stock, if they were taken. */
    public function restockForOrder($orderId)
    {
        return $this->moveStockForOrder((int) $orderId, 1);
    }

    /** Shared by deduct and restock; the flag on the order stops a double move. */
    private function moveStockForOrder($orderId, $direction)
    {
        $from = $direction < 0 ? 0 : 1;
        $to = $direction < 0 ? 1 : 0;

        $this->conn->begin_transaction();

        try {
            // Only one request can flip the flag, so stock moves exactly once.
            $stmt = $this->conn->prepare(
                "UPDATE cc_orders SET stock_deducted = ? WHERE order_id = ? AND stock_deducted = ?"
            );
            $stmt->bind_param("iii", $to, $orderId, $from);
            $stmt->execute();
            $changed = $stmt->affected_rows === 1;
            $stmt->close();

            if (!$changed) {
                $this->conn->rollback();
                return false;
            }

            $stmt = $this->conn->prepare("SELECT product_id, size_id, qty FROM cc_orderdetails WHERE order_id = ?");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $sizeStmt = $this->conn->prepare(
                "UPDATE cc_product_size_options SET stock_qty = GREATEST(stock_qty + ?, 0)
                 WHERE product_id = ? AND size_id = ?"
            );
            $productStmt = $this->conn->prepare(
                "UPDATE cc_products SET product_stock = GREATEST(product_stock + ?, 0) WHERE product_id = ?"
            );

            foreach ($items as $item) {
                $change = $direction * (int) $item['qty'];
                $productId = (int) $item['product_id'];
                $sizeId = (int) $item['size_id'];

                if ($sizeId > 0) {
                    $sizeStmt->bind_param("iii", $change, $productId, $sizeId);
                    $sizeStmt->execute();
                } else {
                    $productStmt->bind_param("ii", $change, $productId);
                    $productStmt->execute();
                }
            }

            $sizeStmt->close();
            $productStmt->close();
            $this->conn->commit();

            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Stock update failed for order ' . $orderId . ': ' . $e->getMessage());
            return false;
        }
    }

    /** Products and sizes at or below the low-stock level, lowest first. */
    public function getLowStock($limit = CC_LOW_STOCK)
    {
        $limit = (int) $limit;
        $stmt = $this->conn->prepare(
            "SELECT p.product_id, p.product_title, s.size_name, o.stock_qty
             FROM cc_product_size_options o
             INNER JOIN cc_products p ON p.product_id = o.product_id
             INNER JOIN cc_product_sizes s ON s.size_id = o.size_id
             WHERE o.stock_qty <= ?
             UNION ALL
             SELECT p.product_id, p.product_title, NULL AS size_name, p.product_stock AS stock_qty
             FROM cc_products p
             WHERE p.product_stock <= ?
               AND NOT EXISTS (SELECT 1 FROM cc_product_size_options o WHERE o.product_id = p.product_id)
             ORDER BY stock_qty, product_title"
        );
        $stmt->bind_param("ii", $limit, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}

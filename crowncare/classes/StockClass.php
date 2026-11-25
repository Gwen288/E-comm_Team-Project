<?php

require_once __DIR__ . '/../core/db_class.php';

/*
 * Stock levels. Products sold in sizes keep stock per size
 * (cc_product_size_options.stock_qty); other products keep it on the product
 * (cc_products.product_stock). Stock goes down once, when an order is paid,
 * and goes back up if a paid order is cancelled.
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

    /**
     * Adds units to the stock of a product and size (size 0 = product without
     * sizes). Returns the new stock level, or false if that product or size
     * does not exist.
     */
    public function addStock($productId, $sizeId, $qty)
    {
        $productId = (int) $productId;
        $sizeId = (int) $sizeId;
        $qty = (int) $qty;

        if ($productId <= 0 || $qty <= 0) {
            return false;
        }

        if ($sizeId > 0) {
            $stmt = $this->conn->prepare(
                "UPDATE cc_product_size_options SET stock_qty = GREATEST(stock_qty, 0) + ?
                 WHERE product_id = ? AND size_id = ?"
            );
            $stmt->bind_param("iii", $qty, $productId, $sizeId);
        } else {
            // A product without sizes only: one with sizes keeps stock per size.
            $stmt = $this->conn->prepare(
                "UPDATE cc_products SET product_stock = GREATEST(product_stock, 0) + ?
                 WHERE product_id = ?
                   AND NOT EXISTS (SELECT 1 FROM cc_product_size_options o WHERE o.product_id = ?)"
            );
            $stmt->bind_param("iii", $qty, $productId, $productId);
        }

        $stmt->execute();
        $changed = $stmt->affected_rows === 1;
        $stmt->close();

        return $changed ? $this->getAvailable($productId, $sizeId) : false;
    }

    /**
     * One row per product size (or per product without sizes) with its stock
     * and the units sold in paid orders.
     */
    public function getInventory()
    {
        $sql = "SELECT p.product_id, p.product_title, p.product_image, c.cat_name,
                       o.size_id, s.size_name, o.stock_qty
                FROM cc_product_size_options o
                INNER JOIN cc_products p ON p.product_id = o.product_id
                INNER JOIN cc_product_sizes s ON s.size_id = o.size_id
                LEFT JOIN cc_categories c ON c.cat_id = p.product_cat
                UNION ALL
                SELECT p.product_id, p.product_title, p.product_image, c.cat_name,
                       0 AS size_id, NULL AS size_name, p.product_stock AS stock_qty
                FROM cc_products p
                LEFT JOIN cc_categories c ON c.cat_id = p.product_cat
                WHERE NOT EXISTS (SELECT 1 FROM cc_product_size_options o WHERE o.product_id = p.product_id)
                ORDER BY product_title, product_id, size_id";

        $result = $this->conn->query($sql);
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        $sold = [];
        $soldResult = $this->conn->query(
            "SELECT d.product_id, d.size_id, SUM(d.qty) AS sold
             FROM cc_orderdetails d
             INNER JOIN cc_orders o ON o.order_id = d.order_id
             WHERE o.stock_deducted = 1
             GROUP BY d.product_id, d.size_id"
        );
        if ($soldResult) {
            foreach ($soldResult->fetch_all(MYSQLI_ASSOC) as $row) {
                $sold[(int) $row['product_id'] . ':' . (int) $row['size_id']] = (int) $row['sold'];
            }
        }

        foreach ($rows as &$row) {
            $row['stock_qty'] = max(0, (int) $row['stock_qty']);
            $row['sold'] = $sold[(int) $row['product_id'] . ':' . (int) $row['size_id']] ?? 0;
        }
        unset($row);

        return $rows;
    }

    /** Total units in stock for every product: [product_id => qty]. */
    public function getAllProductTotals()
    {
        $totals = [];
        foreach ($this->getInventoryLevels() as $row) {
            $id = (int) $row['product_id'];
            $totals[$id] = ($totals[$id] ?? 0) + max(0, (int) $row['stock_qty']);
        }
        return $totals;
    }

    private function getInventoryLevels()
    {
        $result = $this->conn->query(
            "SELECT product_id, stock_qty FROM cc_product_size_options
             UNION ALL
             SELECT p.product_id, p.product_stock AS stock_qty FROM cc_products p
             WHERE NOT EXISTS (SELECT 1 FROM cc_product_size_options o WHERE o.product_id = p.product_id)"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** Products and sizes at or below the low-stock level, lowest first. */
    public function getLowStock($limit = CC_LOW_STOCK)
    {
        $limit = (int) $limit;
        $stmt = $this->conn->prepare(
            "SELECT p.product_id, p.product_title, o.size_id, s.size_name, o.stock_qty
             FROM cc_product_size_options o
             INNER JOIN cc_products p ON p.product_id = o.product_id
             INNER JOIN cc_product_sizes s ON s.size_id = o.size_id
             WHERE o.stock_qty <= ?
             UNION ALL
             SELECT p.product_id, p.product_title, 0 AS size_id, NULL AS size_name, p.product_stock AS stock_qty
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

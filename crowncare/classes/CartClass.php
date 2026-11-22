<?php

require_once __DIR__ . '/../core/db_class.php';

/*
 * Cart lines are a product plus a size (size_id 0 means the product has no sizes).
 * product_price on each line is the price of the chosen size, so checkout and
 * payment totals use the right price.
 *
 * CartController calls this for signed-in customers. Guests keep their cart
 * in the session instead, so nothing here runs for them.
 */
class CartClass extends Database
{
    /* ---------- Input checks ---------- */

    /** Every argument has to be a positive whole number. */
    private function validIds(...$ids)
    {
        foreach ($ids as $id) {
            if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id < 1) {
                return false;
            }
        }
        return true;
    }

    // Check whether this product (in this size) is already in the customer's cart.
    public function isInCart($product_id, $customer_id, $size_id = 0)
    {
        $sql = "SELECT 1
                FROM cc_cart cart
                WHERE p_id = ? AND c_id = ? AND size_id = ?
                LIMIT 1";

        $size_id = (int) $size_id;
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $product_id, $customer_id, $size_id);
        $stmt->execute();

        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    // Add a product (in a size) or increase its quantity if it is already in the cart.
    public function addToCart($product_id, $qty, $customer_id, $size_id = 0)
    {
        if (!$this->validIds($product_id, $qty, $customer_id) || (int) $size_id < 0) {
            return false;
        }

        // The unique key on (c_id, p_id, size_id) turns a repeat add into an
        // extra quantity instead of a second line.
        $sql = "INSERT INTO cc_cart (c_id, p_id, size_id, qty)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)";

        $size_id = (int) $size_id;
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $customer_id, $product_id, $size_id, $qty);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    // Retrieve cart lines with product details and the chosen size and its price.
    public function getCartItems($customer_id)
    {
        // A size can have its own price; products without sizes use product_price.
        $sql = "SELECT cart.*,
                       products.product_title,
                       COALESCE(pso.price, products.product_price) AS product_price,
                       products.product_image,
                       sizes.size_name
                FROM cc_cart cart
                INNER JOIN cc_products products
                    ON cart.p_id = products.product_id
                LEFT JOIN cc_product_size_options pso
                    ON pso.product_id = cart.p_id AND pso.size_id = cart.size_id
                LEFT JOIN cc_product_sizes sizes
                    ON sizes.size_id = cart.size_id
                WHERE cart.c_id = ?
                ORDER BY products.product_title, product_price";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $items;
    }

    /* ---------- Cart totals ---------- */

    // Count the total quantity of items in the customer's cart.
    public function getCartCount($customer_id)
    {
        $sql = "SELECT COALESCE(SUM(qty), 0) AS total_items
                FROM cc_cart
                WHERE c_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) $row['total_items'];
    }

    // Calculate the total cost of all items in the cart.
    public function getCartTotal($customer_id)
    {
        $total = 0.0;

        foreach ($this->getCartItems($customer_id) as $item) {
            $total += (int) $item['qty'] * (float) $item['product_price'];
        }

        return $total;
    }

    // Update the quantity of a cart line.
    public function updateQty($product_id, $new_qty, $customer_id, $size_id = 0)
    {
        if (!$this->validIds($product_id, $new_qty, $customer_id) || (int) $size_id < 0) {
            return false;
        }

        $sql = "UPDATE cc_cart
                SET qty = ?
                WHERE p_id = ? AND c_id = ? AND size_id = ?";

        $size_id = (int) $size_id;
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $new_qty, $product_id, $customer_id, $size_id);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    // Remove every item from the customer's cart (used after payment).
    public function clearCart($customer_id)
    {
        $sql = "DELETE FROM cc_cart WHERE c_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    // Remove one cart line.
    public function removeFromCart($product_id, $customer_id, $size_id = 0)
    {
        $sql = "DELETE FROM cc_cart
                WHERE p_id = ? AND c_id = ? AND size_id = ?";

        $size_id = (int) $size_id;
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $product_id, $customer_id, $size_id);

        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}

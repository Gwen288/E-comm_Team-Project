
<?php

require_once __DIR__ . '/../core/db_class.php';

class CartClass extends Database
{
    // Check whether this product is already in the customer's cart.
    public function isInCart($product_id, $customer_id)
    {
        $sql = "SELECT 1
                FROM cart
                WHERE p_id = ? AND c_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $product_id, $customer_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }

    // Add a product or increase its quantity if it is already in the cart.
    public function addToCart($product_id, $qty, $customer_id)
    {
        if (
            filter_var($product_id, FILTER_VALIDATE_INT) === false ||
            $product_id < 1 ||
            filter_var($qty, FILTER_VALIDATE_INT) === false ||
            $qty < 1 ||
            filter_var($customer_id, FILTER_VALIDATE_INT) === false ||
            $customer_id < 1
        ) {
            return false;
        }

        $sql = "INSERT INTO cart (c_id, p_id, qty)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $customer_id, $product_id, $qty);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Retrieve cart items and their product information.
    public function getCartItems($customer_id)
    {
        $sql = "SELECT cart.*,
                       products.product_title,
                       products.product_price,
                       products.product_image
                FROM cart
                INNER JOIN products
                    ON cart.p_id = products.product_id
                WHERE cart.c_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $items;
    }

    // Count the total quantity of items in the customer's cart.
    public function getCartCount($customer_id)
    {
        $sql = "SELECT COALESCE(SUM(qty), 0) AS total_items
                FROM cart
                WHERE c_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $count = (int) $row['total_items'];

        $stmt->close();

        return $count;
    }

    // Calculate the total cost of all items in the cart.
    public function getCartTotal($customer_id)
    {
        $items = $this->getCartItems($customer_id);
        $total = 0.0;

        foreach ($items as $item) {
            $total += (int) $item['qty']
                    * (float) $item['product_price'];
        }

        return $total;
    }

    // Update the quantity of a product in the customer's cart.
    public function updateQty($product_id, $new_qty, $customer_id)
    {
        if (
            filter_var($product_id, FILTER_VALIDATE_INT) === false ||
            $product_id < 1 ||
            filter_var($new_qty, FILTER_VALIDATE_INT) === false ||
            $new_qty < 1 ||
            filter_var($customer_id, FILTER_VALIDATE_INT) === false ||
            $customer_id < 1
        ) {
            return false;
        }

        $sql = "UPDATE cart
                SET qty = ?
                WHERE p_id = ? AND c_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $new_qty, $product_id, $customer_id);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Remove a product from the customer's cart.
    public function removeFromCart($product_id, $customer_id)
    {
        $sql = "DELETE FROM cart
                WHERE p_id = ? AND c_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $product_id, $customer_id);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }
}

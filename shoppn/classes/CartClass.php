
<?php

require_once __DIR__ . '/../core/db_class.php';

class CartClass extends Database
{
    // Check whether this product is already in the customer's cart.
    public function isInCart($product_id, $ip)
    {
        $sql = "SELECT 1 FROM cart WHERE p_id = ? AND ip_add = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $product_id, $ip);
        $stmt->execute();

        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }

    // Add a product to the cart.
    public function addToCart($product_id, $qty, $ip, $customer_id = null)
    {
        $sql = "INSERT INTO cart (p_id, ip_add, c_id, qty)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isii", $product_id, $ip, $customer_id, $qty);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Retrieve cart items and their product information.
    public function getCartItems($ip)
    {
        $sql = "SELECT cart.*,
                       products.product_title,
                       products.product_price,
                       products.product_image
                FROM cart
                INNER JOIN products
                    ON cart.p_id = products.product_id
                WHERE cart.ip_add = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ip);
        $stmt->execute();

        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $items;
    }

    // Count the number of cart rows.
    public function getCartCount($ip)
    {
        $sql = "SELECT COUNT(*) AS total_items
                FROM cart
                WHERE ip_add = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ip);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $count = (int) $row['total_items'];

        $stmt->close();

        return $count;
    }

    // Calculate the total cost of all items in the cart.
    public function getCartTotal($ip)
    {
        $items = $this->getCartItems($ip);
        $total = 0.0;

        foreach ($items as $item) {
            $total += (int) $item['qty'] * (float) $item['product_price'];
        }

        return $total;
    }

    
    // Update the quantity of a product in the customer's cart.
    
public function updateQty($product_id, $new_qty, $ip)
{
    if (
        filter_var($product_id, FILTER_VALIDATE_INT) === false ||
        $product_id < 1 ||
        filter_var($new_qty, FILTER_VALIDATE_INT) === false ||
        $new_qty < 1
    ) {
        return false;
    }

    $sql = "UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("iis", $new_qty, $product_id, $ip);

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}


    // Remove a product from the customer's cart.
    public function removeFromCart($product_id, $ip)
    {
        $sql = "DELETE FROM cart
                WHERE p_id = ? AND ip_add = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $product_id, $ip);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

}

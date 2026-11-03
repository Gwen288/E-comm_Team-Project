
<?php

require_once __DIR__ . '/../core/db_class.php';

class ProductClass extends Database
{
    // ==================== BRANDS ====================

    public function addBrand($name)
    {
        $sql = "INSERT INTO brands (brand_name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);

        return $stmt->execute();
    }

    public function getAllBrands()
    {
        $sql = "SELECT * FROM brands ORDER BY brand_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getBrandById($id)
    {
        $sql = "SELECT * FROM brands WHERE brand_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows === 1
            ? $result->fetch_assoc()
            : false;
    }

    public function updateBrand($id, $name)
    {
        $sql = "UPDATE brands SET brand_name = ? WHERE brand_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);

        return $stmt->execute();
    }

    public function deleteBrand($id)
    {
        $sql = "DELETE FROM brands WHERE brand_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function brandExists($name)
    {
        $sql = "SELECT brand_id FROM brands WHERE brand_name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }


    // ==================== CATEGORIES ====================

    public function addCategory($name)
    {
        $sql = "INSERT INTO categories (cat_name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);

        return $stmt->execute();
    }

    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories ORDER BY cat_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getCategoryById($id)
    {
        $sql = "SELECT * FROM categories WHERE cat_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows === 1
            ? $result->fetch_assoc()
            : false;
    }

    public function updateCategory($id, $name)
    {
        $sql = "UPDATE categories SET cat_name = ? WHERE cat_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);

        return $stmt->execute();
    }

    public function deleteCategory($id)
    {
        $sql = "DELETE FROM categories WHERE cat_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function categoryExists($name)
    {
        $sql = "SELECT cat_id FROM categories WHERE cat_name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }


    // ==================== PRODUCTS ====================

    public function addProduct(
        $cat,
        $brand,
        $title,
        $price,
        $desc,
        $image_filename,
        $keywords
    ) {
        $sql = "INSERT INTO products
                (
                    product_cat,
                    product_brand,
                    product_title,
                    product_price,
                    product_desc,
                    product_image,
                    product_keywords
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "iisdsss",
            $cat,
            $brand,
            $title,
            $price,
            $desc,
            $image_filename,
            $keywords
        );

        return $stmt->execute();
    }

    public function updateProduct(
        $id,
        $cat,
        $brand,
        $title,
        $price,
        $desc,
        $image_filename,
        $keywords
    ) {
        $sql = "UPDATE products
                SET product_cat = ?,
                    product_brand = ?,
                    product_title = ?,
                    product_price = ?,
                    product_desc = ?,
                    product_image = ?,
                    product_keywords = ?
                WHERE product_id = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "iisdsssi",
            $cat,
            $brand,
            $title,
            $price,
            $desc,
            $image_filename,
            $keywords,
            $id
        );

        return $stmt->execute();
    }

    public function getProductById($id)
    {
        $sql = "SELECT
                    p.*,
                    c.cat_name,
                    b.brand_name
                FROM products AS p
                LEFT JOIN categories AS c
                    ON p.product_cat = c.cat_id
                LEFT JOIN brands AS b
                    ON p.product_brand = b.brand_id
                WHERE p.product_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows === 1
            ? $result->fetch_assoc()
            : false;
    }

    public function getAllProducts()
    {
        $sql = "SELECT
                    p.*,
                    c.cat_name,
                    b.brand_name
                FROM products AS p
                LEFT JOIN categories AS c
                    ON p.product_cat = c.cat_id
                LEFT JOIN brands AS b
                    ON p.product_brand = b.brand_id
                ORDER BY p.product_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getFeaturedProducts($limit = 6)
    {
        $limit = (int) $limit;
    
        if ($limit < 1) {
            return [];
        }
    
        $sql = "SELECT *
                FROM products
                ORDER BY RAND()
                LIMIT ?";
    
        $stmt = $this->conn->prepare($sql);
    
        if (!$stmt) {
            return [];
        }
    
        $stmt->bind_param("i", $limit);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $products = [];
    
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    
        $stmt->close();
    
        return $products;
    }
    
    
    public function getProductsByCategory($cat_id)
    {
        $cat_id = (int) $cat_id;
    
        if ($cat_id < 1) {
            return [];
        }
    
        $sql = "SELECT *
                FROM products
                WHERE product_cat = ?
                ORDER BY product_id DESC";
    
        $stmt = $this->conn->prepare($sql);
    
        if (!$stmt) {
            return [];
        }
    
        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $products = [];
    
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    
        $stmt->close();
    
        return $products;
    }
    
    
    public function getProductsByBrand($brand_id)
    {
        $brand_id = (int) $brand_id;
    
        if ($brand_id < 1) {
            return [];
        }
    
        $sql = "SELECT *
                FROM products
                WHERE product_brand = ?
                ORDER BY product_id DESC";
    
        $stmt = $this->conn->prepare($sql);
    
        if (!$stmt) {
            return [];
        }
    
        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $products = [];
    
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    
        $stmt->close();
    
        return $products;
    }
    
    
    public function searchProducts($query)
    {
        $query = trim($query);
    
        if ($query === '') {
            return [];
        }
    
        $searchTerm = '%' . $query . '%';
    
        $sql = "SELECT *
                FROM products
                WHERE product_title LIKE ?
                   OR product_keywords LIKE ?
                ORDER BY product_id DESC";
    
        $stmt = $this->conn->prepare($sql);
    
        if (!$stmt) {
            return [];
        }
    
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $products = [];
    
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    
        $stmt->close();
    
        return $products;
    }


    
public function deleteProduct($id)
{
    $id = (int) $id;

    if ($id < 1) {
        return false;
    }

    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id);
    $success = $stmt->execute();

    $stmt->close();

    return $success;
}




}

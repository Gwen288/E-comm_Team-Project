
<?php
require_once __DIR__ . '/../core/db_class.php';

class ProductClass extends Database
{
    // BRANDS

    public function addBrand($name)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO brands (brand_name) VALUES (?)"
        );
        $stmt->bind_param("s", $name);
        return $stmt->execute();
    }

    public function getAllBrands()
    {
        $result = $this->conn->query(
            "SELECT * FROM brands ORDER BY brand_name ASC"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getBrandById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM brands WHERE brand_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: false;
    }

    public function updateBrand($id, $name)
    {
        $stmt = $this->conn->prepare(
            "UPDATE brands SET brand_name = ? WHERE brand_id = ?"
        );
        $stmt->bind_param("si", $name, $id);
        return $stmt->execute();
    }

    public function deleteBrand($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM brands WHERE brand_id = ?"
        );
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function brandExists($name)
    {
        $stmt = $this->conn->prepare(
            "SELECT brand_id FROM brands WHERE brand_name = ?"
        );
        $stmt->bind_param("s", $name);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }


    // CATEGORIES

    
public function addCategory($name, $parentId = null)
{
    if ($parentId === null) {
        $stmt = $this->conn->prepare(
            "INSERT INTO categories (cat_name, parent_category_id)
             VALUES (?, NULL)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $name);

    } else {
        $parentId = (int) $parentId;

        // The parent must exist and must be a main category.
        $parent = $this->getCategoryById($parentId);

        if (
            $parent === false ||
            $parent['parent_category_id'] !== null
        ) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO categories (cat_name, parent_category_id)
             VALUES (?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $name, $parentId);
    }

    return $stmt->execute();
}

   
public function getAllCategories()
{
    $result = $this->conn->query(
        "SELECT
            c.*,
            p.cat_name AS parent_category_name
         FROM categories c
         LEFT JOIN categories p
            ON c.parent_category_id = p.cat_id
         ORDER BY
            COALESCE(p.cat_name, c.cat_name),
            (c.parent_category_id IS NOT NULL),
            c.cat_name"
    );

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}


    public function getMainCategories()
    {
        $result = $this->conn->query(
            "SELECT *
             FROM categories
             WHERE parent_category_id IS NULL
             ORDER BY cat_name"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getCategoryById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM categories WHERE cat_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: false;
    }

   
public function updateCategory($id, $name, $parentId = null)
{
    $id = (int) $id;

    // Confirm that the category exists.
    $existingCategory = $this->getCategoryById($id);

    if ($existingCategory === false) {
        return false;
    }

    if ($parentId === null) {

        // A main category with existing subcategories cannot become
        // a subcategory, because that would create nested categories.
        $stmt = $this->conn->prepare(
            "UPDATE categories
             SET cat_name = ?, parent_category_id = NULL
             WHERE cat_id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $name, $id);

    } else {
        $parentId = (int) $parentId;

        // A category cannot be its own parent.
        if ($parentId === $id) {
            return false;
        }

        // Only an existing main category can be a parent.
        $parent = $this->getCategoryById($parentId);

        if (
            $parent === false ||
            $parent['parent_category_id'] !== null
        ) {
            return false;
        }

        // Prevent a main category with subcategories from becoming
        // a subcategory itself.
        $children = $this->conn->prepare(
            "SELECT cat_id
             FROM categories
             WHERE parent_category_id = ?
             LIMIT 1"
        );

        if (!$children) {
            return false;
        }

        $children->bind_param("i", $id);
        $children->execute();

        if ($children->get_result()->num_rows > 0) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "UPDATE categories
             SET cat_name = ?, parent_category_id = ?
             WHERE cat_id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sii", $name, $parentId, $id);
    }

    return $stmt->execute();
}

    public function deleteCategory($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM categories WHERE cat_id = ?"
        );
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function categoryExists($name)
    {
        $stmt = $this->conn->prepare(
            "SELECT cat_id FROM categories WHERE cat_name = ?"
        );
        $stmt->bind_param("s", $name);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }


    // PRODUCT SIZES

    public function getAllSizes()
    {
        $result = $this->conn->query(
            "SELECT * FROM product_sizes ORDER BY size_name"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getProductSizeIds($productId)
    {
        $stmt = $this->conn->prepare(
            "SELECT size_id
             FROM product_size_options
             WHERE product_id = ?"
        );

        $stmt->bind_param("i", $productId);
        $stmt->execute();

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_map('intval', array_column($rows, 'size_id'));
    }

    private function saveProductSizes($productId, $sizeIds)
    {
        if (!is_array($sizeIds)) {
            $sizeIds = [];
        }

        $cleanIds = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $sizeIds),
                    function ($value) {
                        return $value > 0;
                    }
                )
            )
        );

        // Validate all submitted size IDs before changing existing options.
        if (count($cleanIds) > 0) {
            $placeholders = implode(
                ',',
                array_fill(0, count($cleanIds), '?')
            );

            $check = $this->conn->prepare(
                "SELECT COUNT(*) AS total
                 FROM product_sizes
                 WHERE size_id IN ($placeholders)"
            );

            $types = str_repeat('i', count($cleanIds));
            $bind = [$types];

            foreach ($cleanIds as $index => $value) {
                $bind[] = &$cleanIds[$index];
            }

            call_user_func_array([$check, 'bind_param'], $bind);
            $check->execute();

            $found = (int) $check->get_result()->fetch_assoc()['total'];

            if ($found !== count($cleanIds)) {
                return false;
            }
        }

        $delete = $this->conn->prepare(
            "DELETE FROM product_size_options WHERE product_id = ?"
        );

        $delete->bind_param("i", $productId);

        if (!$delete->execute()) {
            return false;
        }

        if (!$cleanIds) {
            return true;
        }

        $insert = $this->conn->prepare(
            "INSERT IGNORE INTO product_size_options
             (product_id, size_id)
             VALUES (?, ?)"
        );

        foreach ($cleanIds as $sizeId) {
            $insert->bind_param("ii", $productId, $sizeId);

            if (!$insert->execute()) {
                return false;
            }
        }

        return true;
    }


    // PRODUCTS

   
public function addProduct(
    $cat,
    $brand,
    $title,
    $price,
    $desc,
    $image,
    $keywords,
    $sizeIds = []
) {
    $this->conn->begin_transaction();

    try {
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

        if (!$stmt) {
            throw new Exception('Could not prepare product insertion.');
        }

        $stmt->bind_param(
            "iisdsss",
            $cat,
            $brand,
            $title,
            $price,
            $desc,
            $image,
            $keywords
        );

        if (!$stmt->execute()) {
            throw new Exception('Could not insert product.');
        }

        $id = $this->conn->insert_id;

        if (!$this->saveProductSizes($id, $sizeIds)) {
            throw new Exception('Could not save product sizes.');
        }

        $this->conn->commit();

        return true;

    } catch (Throwable $e) {
        $this->conn->rollback();

        return false;
    }
}


   
public function updateProduct(
    $id,
    $cat,
    $brand,
    $title,
    $price,
    $desc,
    $image,
    $keywords,
    $sizeIds = []
) {
    $this->conn->begin_transaction();

    try {
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

        if (!$stmt) {
            throw new Exception('Could not prepare product update.');
        }

        $stmt->bind_param(
            "iisdsssi",
            $cat,
            $brand,
            $title,
            $price,
            $desc,
            $image,
            $keywords,
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception('Could not update product.');
        }

        if (!$this->saveProductSizes($id, $sizeIds)) {
            throw new Exception('Could not update product sizes.');
        }

        $this->conn->commit();

        return true;

    } catch (Throwable $e) {
        $this->conn->rollback();

        return false;
    }
}


    public function getAllProducts()
    {
        $result = $this->conn->query(
            "SELECT p.*, c.cat_name, b.brand_name
             FROM products p
             LEFT JOIN categories c ON p.product_cat = c.cat_id
             LEFT JOIN brands b ON p.product_brand = b.brand_id
             ORDER BY p.product_id DESC"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getFeaturedProducts($limit = 6)
    {
        $limit = max(1, (int) $limit);

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM products
             ORDER BY product_id DESC
             LIMIT ?"
        );

        $stmt->bind_param("i", $limit);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductsByCategory($catId)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM products
             WHERE product_cat = ?
             ORDER BY product_id DESC"
        );

        $stmt->bind_param("i", $catId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductsByBrand($brandId)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM products
             WHERE product_brand = ?
             ORDER BY product_id DESC"
        );

        $stmt->bind_param("i", $brandId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function searchProducts($query)
    {
        $term = '%' . trim($query) . '%';

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM products
             WHERE product_title LIKE ?
                OR product_keywords LIKE ?
             ORDER BY product_id DESC"
        );

        $stmt->bind_param("ss", $term, $term);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    // SHOP FILTERING AND SORTING

    public function getShopProducts($filters)
    {
        $sql = "SELECT DISTINCT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c
                    ON p.product_cat = c.cat_id
                LEFT JOIN brands b
                    ON p.product_brand = b.brand_id
                LEFT JOIN product_size_options pso
                    ON p.product_id = pso.product_id
                WHERE 1=1";

        $types = '';
        $values = [];

        if (!empty($filters['query'])) {
            $sql .= " AND (
                p.product_title LIKE ?
                OR p.product_keywords LIKE ?
                OR p.product_desc LIKE ?
            )";

            $term = '%' . $filters['query'] . '%';

            array_push($values, $term, $term, $term);
            $types .= 'sss';
        }

        if (!empty($filters['category'])) {
            $sql .= " AND p.product_cat = ?";

            $values[] = (int) $filters['category'];
            $types .= 'i';
        }

        if (!empty($filters['main_category'])) {
            $sql .= " AND (
                p.product_cat = ?
                OR c.parent_category_id = ?
            )";

            $value = (int) $filters['main_category'];

            array_push($values, $value, $value);
            $types .= 'ii';
        }

        if (isset($filters['min_price'])) {
            $sql .= " AND p.product_price >= ?";

            $values[] = (float) $filters['min_price'];
            $types .= 'd';
        }

        if (isset($filters['max_price'])) {
            $sql .= " AND p.product_price <= ?";

            $values[] = (float) $filters['max_price'];
            $types .= 'd';
        }

        if (!empty($filters['size'])) {
            $sql .= " AND pso.size_id = ?";

            $values[] = (int) $filters['size'];
            $types .= 'i';
        }

        if (!empty($filters['brand'])) {
            $sql .= " AND p.product_brand = ?";

            $values[] = (int) $filters['brand'];
            $types .= 'i';
        }

        $sorts = [
            'newest' => 'p.created_at DESC, p.product_id DESC',
            'price_high' => 'p.product_price DESC',
            'price_low' => 'p.product_price ASC',
            'name_az' => 'p.product_title ASC',
            'name_za' => 'p.product_title DESC',
            'recommendation' => 'p.product_id DESC'
        ];

        $sort = $filters['sort'] ?? 'recommendation';

        $sql .= " ORDER BY " .
            ($sorts[$sort] ?? $sorts['recommendation']);

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        if ($types !== '') {
            $bind = [$types];

            foreach ($values as $key => $value) {
                $bind[] = &$values[$key];
            }

            call_user_func_array(
                [$stmt, 'bind_param'],
                $bind
            );
        }

        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAvailableShopSizes()
    {
        $result = $this->conn->query(
            "SELECT DISTINCT s.size_id, s.size_name
             FROM product_sizes s
             INNER JOIN product_size_options o
                ON s.size_id = o.size_id
             INNER JOIN products p
                ON p.product_id = o.product_id
             ORDER BY s.size_name"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getPriceBounds()
    {
        $result = $this->conn->query(
            "SELECT
                COALESCE(MIN(product_price), 0) AS min_price,
                COALESCE(MAX(product_price), 1000) AS max_price
             FROM products"
        );

        return $result
            ? $result->fetch_assoc()
            : ['min_price' => 0, 'max_price' => 1000];
    }

    public function deleteProduct($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM products WHERE product_id = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    
public function getProductById($id)
{
    $sql = "SELECT
                p.*,
                c.cat_name,
                b.brand_name
            FROM products p
            LEFT JOIN categories c
                ON p.product_cat = c.cat_id
            LEFT JOIN brands b
                ON p.product_brand = b.brand_id
            WHERE p.product_id = ?";

    $stmt = $this->conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    $stmt->close();

    return $product ?: false;
}

}

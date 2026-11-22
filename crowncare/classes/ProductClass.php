
<?php
require_once __DIR__ . '/../core/db_class.php';

/*
 * Products, categories and sizes, including each size's own price and stock.
 * ProductController calls this for the shop pages and the admin product forms.
 */
class ProductClass extends Database
{
    // CATEGORIES

    /** Adds a main category, or a subcategory under an existing main category. */
public function addCategory($name, $parentId = null)
{
    if ($parentId === null) {
        $stmt = $this->conn->prepare(
            "INSERT INTO cc_categories (cat_name, parent_category_id)
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
            "INSERT INTO cc_categories (cat_name, parent_category_id)
             VALUES (?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $name, $parentId);
    }

    return $stmt->execute();
}

   
/** All categories with their parent's name, grouped by main category. */
public function getAllCategories()
{
    $result = $this->conn->query(
        "SELECT
            c.*,
            p.cat_name AS parent_category_name
         FROM cc_categories c
         LEFT JOIN cc_categories p
            ON c.parent_category_id = p.cat_id
         ORDER BY
            COALESCE(p.cat_name, c.cat_name),
            (c.parent_category_id IS NOT NULL),
            c.cat_name"
    );

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}


    /** Main categories only, with no parent. */
    public function getMainCategories()
    {
        $result = $this->conn->query(
            "SELECT *
             FROM cc_categories
             WHERE parent_category_id IS NULL
             ORDER BY cat_name"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** One category row, or false. */
    public function getCategoryById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM cc_categories WHERE cat_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: false;
    }

   
/** Edits a category and, if allowed, moves it under a different parent. */
public function updateCategory($id, $name, $parentId = null)
{
    $id = (int) $id;

    // Confirm that the category exists.
    $existingCategory = $this->getCategoryById($id);

    if ($existingCategory === false) {
        return false;
    }

    if ($parentId === null) {

        // No parent means this is a main category.
        $stmt = $this->conn->prepare(
            "UPDATE cc_categories
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
             FROM cc_categories
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
            "UPDATE cc_categories
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

    /** Deletes a category by ID; the admin action doesn't check for products first. */
    public function deleteCategory($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM cc_categories WHERE cat_id = ?"
        );
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function categoryExists($name)
    {
        $stmt = $this->conn->prepare(
            "SELECT cat_id FROM cc_categories WHERE cat_name = ?"
        );
        $stmt->bind_param("s", $name);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }


    // PRODUCT SIZES

    public function getAllSizes()
    {
        $result = $this->conn->query(
            "SELECT * FROM cc_product_sizes ORDER BY size_name"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getProductSizeIds($productId)
    {
        $stmt = $this->conn->prepare(
            "SELECT size_id
             FROM cc_product_size_options
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
                 FROM cc_product_sizes
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

        // Remove only the sizes that were unticked, so the sizes that stay keep
        // their own price and stock.
        if ($cleanIds) {
            $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
            $delete = $this->conn->prepare(
                "DELETE FROM cc_product_size_options WHERE product_id = ? AND size_id NOT IN ($placeholders)"
            );
            $delete->bind_param('i' . str_repeat('i', count($cleanIds)), $productId, ...$cleanIds);
        } else {
            $delete = $this->conn->prepare("DELETE FROM cc_product_size_options WHERE product_id = ?");
            $delete->bind_param("i", $productId);
        }

        if (!$delete->execute()) {
            return false;
        }

        if (!$cleanIds) {
            return true;
        }

        $insert = $this->conn->prepare(
            "INSERT IGNORE INTO cc_product_size_options
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

   
/** Adds the product and its ticked sizes in one transaction. */
public function addProduct(
    $cat,
    $title,
    $price,
    $desc,
    $image,
    $keywords,
    $sizeIds = []
) {
    $this->conn->begin_transaction();

    try {
        $sql = "INSERT INTO cc_products
                (
                    product_cat,
                    product_title,
                    product_price,
                    product_desc,
                    product_image,
                    product_keywords
                )
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Could not prepare product insertion.');
        }

        $stmt->bind_param(
            "isdsss",
            $cat,
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

        // The new product's ID (a true value, so older callers still work).
        return (int) $id;

    } catch (Throwable $e) {
        $this->conn->rollback();

        return false;
    }
}


   
/** Updates the product and replaces its ticked size options. */
public function updateProduct(
    $id,
    $cat,
    $title,
    $price,
    $desc,
    $image,
    $keywords,
    $sizeIds = []
) {
    $this->conn->begin_transaction();

    try {
        $sql = "UPDATE cc_products
                SET product_cat = ?,
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
            "isdsssi",
            $cat,
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


    /** All products with their category name, newest first. */
    public function getAllProducts()
    {
        $result = $this->conn->query(
            "SELECT p.*, c.cat_name
             FROM cc_products p
             LEFT JOIN cc_categories c ON p.product_cat = c.cat_id
             ORDER BY p.product_id DESC"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** Newest products for the home page; there is no featured flag in the database. */
    public function getFeaturedProducts($limit = 6)
    {
        $limit = max(1, (int) $limit);

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM cc_products
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
            "SELECT * FROM cc_products
             WHERE product_cat = ?
             ORDER BY product_id DESC"
        );

        $stmt->bind_param("i", $catId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function searchProducts($query)
    {
        $term = '%' . trim($query) . '%';

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM cc_products
             WHERE product_title LIKE ?
                OR product_keywords LIKE ?
             ORDER BY product_id DESC"
        );

        $stmt->bind_param("ss", $term, $term);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    // SHOP FILTERING AND SORTING

    /** Shop listing built from the filters the shop page sends, plus the chosen sort. */
    public function getShopProducts($filters)
    {
        // DISTINCT stops a product appearing twice when several of its sizes
        // match the size filter.
        $sql = "SELECT DISTINCT p.*, c.cat_name
                FROM cc_products p
                LEFT JOIN cc_categories c
                    ON p.product_cat = c.cat_id
                LEFT JOIN cc_product_size_options pso
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

        // Product type checkboxes: a product matches its own category or its parent category.
        $categoryIds = array_values(array_filter(array_map('intval', (array) ($filters['categories'] ?? []))));
        if ($categoryIds) {
            $marks = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql .= " AND (p.product_cat IN ($marks) OR c.parent_category_id IN ($marks))";
            foreach ([1, 2] as $pass) {
                foreach ($categoryIds as $id) {
                    $values[] = $id;
                    $types .= 'i';
                }
            }
        }

        // Size checkboxes.
        $sizeIds = array_values(array_filter(array_map('intval', (array) ($filters['sizes'] ?? []))));
        if ($sizeIds) {
            $marks = implode(',', array_fill(0, count($sizeIds), '?'));
            $sql .= " AND pso.size_id IN ($marks)";
            foreach ($sizeIds as $id) {
                $values[] = $id;
                $types .= 'i';
            }
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

    /** Only sizes that at least one product actually offers. */
    public function getAvailableShopSizes()
    {
        $result = $this->conn->query(
            "SELECT DISTINCT s.size_id, s.size_name
             FROM cc_product_sizes s
             INNER JOIN cc_product_size_options o
                ON s.size_id = o.size_id
             INNER JOIN cc_products p
                ON p.product_id = o.product_id
             ORDER BY s.size_name"
        );

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Sizes of one product with their own price (falls back to the product price),
     * cheapest first, as on the CMS product page.
     */
    public function getProductSizes($productId)
    {
        $stmt = $this->conn->prepare(
            "SELECT s.size_id, s.size_name,
                    COALESCE(o.price, p.product_price) AS price
             FROM cc_product_size_options o
             INNER JOIN cc_product_sizes s ON s.size_id = o.size_id
             INNER JOIN cc_products p ON p.product_id = o.product_id
             WHERE o.product_id = ?
             ORDER BY price ASC, s.size_id ASC"
        );

        if (!$stmt) {
            return [];
        }

        $productId = (int) $productId;
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /** One size of a product, or false when the product does not offer it. */
    public function getProductSize($productId, $sizeId)
    {
        foreach ($this->getProductSizes($productId) as $size) {
            if ((int) $size['size_id'] === (int) $sizeId) {
                return $size;
            }
        }

        return false;
    }

    /** Limits for the shop's price slider, with defaults for an empty catalogue. */
    public function getPriceBounds()
    {
        $result = $this->conn->query(
            "SELECT
                COALESCE(MIN(product_price), 0) AS min_price,
                COALESCE(MAX(product_price), 1000) AS max_price
             FROM cc_products"
        );

        return $result
            ? $result->fetch_assoc()
            : ['min_price' => 0, 'max_price' => 1000];
    }

    public function deleteProduct($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM cc_products WHERE product_id = ?"
        );

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    
/** One product with its category name, or false. */
public function getProductById($id)
{
    $sql = "SELECT
                p.*,
                c.cat_name
            FROM cc_products p
            LEFT JOIN cc_categories c
                ON p.product_cat = c.cat_id
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



    // SIZE PRICES AND STOCK

    /**
     * Price and stock of each size of a product, set on the admin product form.
     * $details = [size_id => ['price' => float|null, 'stock' => int]]
     */
    public function saveSizeDetails($productId, array $details)
    {
        $stmt = $this->conn->prepare(
            "UPDATE cc_product_size_options SET price = ?, stock_qty = ? WHERE product_id = ? AND size_id = ?"
        );
        foreach ($details as $sizeId => $detail) {
            $price = $detail['price'];
            $stock = max(0, (int) $detail['stock']);
            $sizeId = (int) $sizeId;
            $stmt->bind_param("diii", $price, $stock, $productId, $sizeId);
            $stmt->execute();
        }
        $stmt->close();
    }

    /** Own price (NULL = product price) and stock of each size: [size_id => [price, stock]]. */
    public function getSizeDetails($productId)
    {
        $stmt = $this->conn->prepare(
            "SELECT size_id, price, stock_qty FROM cc_product_size_options WHERE product_id = ?"
        );
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $details = [];
        foreach ($rows as $row) {
            $details[(int) $row['size_id']] = ['price' => $row['price'], 'stock' => (int) $row['stock_qty']];
        }
        return $details;
    }
}

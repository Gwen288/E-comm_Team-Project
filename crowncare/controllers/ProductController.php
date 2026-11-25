
<?php
require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new ProductClass();
    }

    // Categories
    public function addCategory($name, $parentId = null) { return $this->product->addCategory($name, $parentId); }
    public function getAllCategories() { return $this->product->getAllCategories(); }
    public function getMainCategories() { return $this->product->getMainCategories(); }
    public function getCategoryById($id) { return $this->product->getCategoryById($id); }
    public function updateCategory($id, $name, $parentId = null) { return $this->product->updateCategory($id, $name, $parentId); }
    public function deleteCategory($id) { return $this->product->deleteCategory($id); }
    public function categoryExists($name) { return $this->product->categoryExists($name); }

    // Product sizes
    public function getAllSizes() { return $this->product->getAllSizes(); }
    public function getProductSizeIds($id) { return $this->product->getProductSizeIds($id); }

    // Product management
    public function addProduct($cat, $title, $price, $desc, $image, $keywords, $sizes = [])
    {
        return $this->product->addProduct(
            $cat, $title, $price, $desc, $image, $keywords, $sizes
        );
    }

    public function updateProduct($id, $cat, $title, $price, $desc, $image, $keywords, $sizes = [])
    {
        return $this->product->updateProduct(
            $id, $cat, $title, $price, $desc, $image, $keywords, $sizes
        );
    }

    public function getProductById($id) { return $this->product->getProductById($id); }
    public function getAllProducts() { return $this->product->getAllProducts(); }
    public function getFeaturedProducts($limit = 6) { return $this->product->getFeaturedProducts($limit); }
    public function getProductsByCategory($id) { return $this->product->getProductsByCategory($id); }
    public function searchProducts($query) { return $this->product->searchProducts($query); }
    public function getShopProducts($filters) { return $this->product->getShopProducts($filters); }
    public function getAvailableShopSizes() { return $this->product->getAvailableShopSizes(); }
    public function getPriceBounds() { return $this->product->getPriceBounds(); }
    public function getProductSizes($id) { return $this->product->getProductSizes($id); }
    public function getProductSize($id, $sizeId) { return $this->product->getProductSize($id, $sizeId); }
    public function deleteProduct($id) { return $this->product->deleteProduct($id); }

    public function saveSizeDetails($productId, array $details)
    {
        $this->product->saveSizeDetails((int) $productId, $details);
    }

    public function getSizeDetails($productId)
    {
        return $this->product->getSizeDetails((int) $productId);
    }

    /**
     * Saves each ticked size's own price, and the opening stock of a new product.
     * When an existing product is edited ($isNew false), stock is not changed
     * here: it is added on the Inventory page. A size ticked for the first time
     * while editing starts at 0.
     * $sizeIds are the ticked sizes; $existingSizeIds the sizes it had before.
     */
    public function saveStockFromForm($productId, array $sizeIds, array $post, $isNew = true, array $existingSizeIds = [])
    {
        require_once __DIR__ . '/../classes/StockClass.php';

        $existing = array_fill_keys(array_map('intval', $existingSizeIds), true);
        $current = $isNew ? [] : $this->getSizeDetails($productId);

        $details = [];
        foreach ($sizeIds as $sizeId) {
            $sizeId = (int) $sizeId;
            $price = $post['size_price'][$sizeId] ?? '';
            $price = $price === '' ? null : filter_var($price, FILTER_VALIDATE_FLOAT);

            if ($isNew) {
                $stock = max(0, (int) ($post['size_stock'][$sizeId] ?? 0));
            } elseif (isset($existing[$sizeId])) {
                $stock = (int) ($current[$sizeId]['stock'] ?? 0);
            } else {
                $stock = 0;
            }

            $details[$sizeId] = [
                'price' => ($price === false || $price === null || $price < 0) ? null : round($price, 2),
                'stock' => $stock,
            ];
        }

        if ($details) {
            $this->saveSizeDetails($productId, $details);
        } elseif ($isNew) {
            (new StockClass())->setStock($productId, 0, max(0, (int) ($post['product_stock'] ?? 0)));
        }
    }
}

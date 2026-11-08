
<?php
require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new ProductClass();
    }

    // Brands
    public function addBrand($name) { return $this->product->addBrand($name); }
    public function getAllBrands() { return $this->product->getAllBrands(); }
    public function getBrandById($id) { return $this->product->getBrandById($id); }
    public function updateBrand($id, $name) { return $this->product->updateBrand($id, $name); }
    public function deleteBrand($id) { return $this->product->deleteBrand($id); }
    public function brandExists($name) { return $this->product->brandExists($name); }

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
    public function addProduct($cat, $brand, $title, $price, $desc, $image, $keywords, $sizes = [])
    {
        return $this->product->addProduct(
            $cat, $brand, $title, $price, $desc, $image, $keywords, $sizes
        );
    }

    public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image, $keywords, $sizes = [])
    {
        return $this->product->updateProduct(
            $id, $cat, $brand, $title, $price, $desc, $image, $keywords, $sizes
        );
    }

    public function getProductById($id) { return $this->product->getProductById($id); }
    public function getAllProducts() { return $this->product->getAllProducts(); }
    public function getFeaturedProducts($limit = 6) { return $this->product->getFeaturedProducts($limit); }
    public function getProductsByCategory($id) { return $this->product->getProductsByCategory($id); }
    public function getProductsByBrand($id) { return $this->product->getProductsByBrand($id); }
    public function searchProducts($query) { return $this->product->searchProducts($query); }
    public function getShopProducts($filters) { return $this->product->getShopProducts($filters); }
    public function getAvailableShopSizes() { return $this->product->getAvailableShopSizes(); }
    public function getPriceBounds() { return $this->product->getPriceBounds(); }
    public function deleteProduct($id) { return $this->product->deleteProduct($id); }
}

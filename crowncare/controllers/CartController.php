<?php

require_once __DIR__ . '/../classes/CartClass.php';
require_once __DIR__ . '/../classes/ProductClass.php';
require_once __DIR__ . '/../classes/StockClass.php';

/*
 * Cart for logged-in customers (database) and guests (session).
 * A cart line is a product plus a size; size_id 0 means the product has no sizes.
 * Guest lines are stored in $_SESSION['guest_cart'] under the key "productId:sizeId".
 * Used by the cart page, the header count and the add/update/remove actions.
 */
class CartController
{
    private $cartModel;
    private $productModel;
    private $stock;

    /** Why the last add or update didn't go as asked (shown to the shopper). */
    public $lastMessage = '';

    public function __construct()
    {
        $this->cartModel = new CartClass();
        $this->productModel = new ProductClass();
        $this->stock = new StockClass();
    }

    /** Units of this product and size already in the cart. */
    public function qtyInCart($product_id, $customer_id = 0, $size_id = 0)
    {
        foreach ($this->rawCartItems($customer_id) as $item) {
            if ((int) $item['p_id'] === (int) $product_id && (int) $item['size_id'] === (int) $size_id) {
                return (int) $item['qty'];
            }
        }
        return 0;
    }

    /**
     * Cart lines asking for more than is in stock, with the stock left.
     * Used before checkout and payment.
     */
    public function stockProblems($customer_id = 0)
    {
        $problems = [];
        foreach ($this->getCartItems($customer_id) as $item) {
            if ((int) $item['qty'] > (int) $item['stock']) {
                $problems[] = $item;
            }
        }
        return $problems;
    }

    /* ---------- Guest cart helpers ---------- */

    /** Makes sure the session has a cart, upgrading old product-only keys. */
    private function ensureGuestCart()
    {
        if (!isset($_SESSION['guest_cart']) || !is_array($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }

        // Older guest carts were keyed by product id only.
        foreach ($_SESSION['guest_cart'] as $key => $qty) {
            if (strpos((string) $key, ':') === false) {
                unset($_SESSION['guest_cart'][$key]);
                $_SESSION['guest_cart'][(int) $key . ':0'] = (int) $qty;
            }
        }
    }

    private function isGuest()
    {
        return !is_logged_in();
    }

    /** The "product:size" key used by the session cart and the sign-in merge. */
    private function guestKey($product_id, $size_id)
    {
        return (int) $product_id . ':' . (int) $size_id;
    }

    /** True when the product is sold in sizes, so a size must be chosen. */
    public function productHasSizes($product_id)
    {
        return count($this->productModel->getProductSizes((int) $product_id)) > 0;
    }

    /** Checks that the size belongs to the product (or is 0 for a product without sizes). */
    public function isValidSize($product_id, $size_id)
    {
        $size_id = (int) $size_id;

        if (!$this->productHasSizes($product_id)) {
            return $size_id === 0;
        }

        return $size_id > 0 && $this->productModel->getProductSize($product_id, $size_id) !== false;
    }

    /** Session lookup for guests, database lookup for signed-in customers. */
    public function isInCart($product_id, $customer_id = 0, $size_id = 0)
    {
        if ($this->isGuest()) {
            $this->ensureGuestCart();
            return isset($_SESSION['guest_cart'][$this->guestKey($product_id, $size_id)]);
        }

        return $this->cartModel->isInCart((int) $product_id, (int) $customer_id, (int) $size_id);
    }

    /** Caps the quantity at what's in stock and explains the cap in lastMessage. */
    public function addToCart($product_id, $qty, $customer_id = 0, $size_id = 0)
    {
        $product_id = (int) $product_id;
        $qty = (int) $qty;
        $size_id = (int) $size_id;

        if ($product_id < 1 || $qty < 1) {
            return false;
        }

        if ($this->productModel->getProductById($product_id) === false) {
            return false;
        }

        if (!$this->isValidSize($product_id, $size_id)) {
            return false;
        }

        $available = $this->stock->getAvailable($product_id, $size_id);
        $inCart = $this->qtyInCart($product_id, $customer_id, $size_id);

        if ($available <= 0) {
            $this->lastMessage = 'Sorry, this item is out of stock.';
            return false;
        }

        if ($inCart + $qty > $available) {
            $qty = $available - $inCart;
            if ($qty <= 0) {
                $this->lastMessage = 'You already have all ' . $available . ' in stock in your cart.';
                return false;
            }
            $this->lastMessage = 'Only ' . $available . ' in stock, so your cart now has ' . $available . '.';
        }

        if ($this->isGuest()) {
            $this->ensureGuestCart();
            $key = $this->guestKey($product_id, $size_id);
            $_SESSION['guest_cart'][$key] = ($_SESSION['guest_cart'][$key] ?? 0) + $qty;
            return true;
        }

        return $this->cartModel->addToCart($product_id, $qty, (int) $customer_id, $size_id);
    }

    /** Cart lines, each with 'stock' (units available for that product and size). */
    public function getCartItems($customer_id = 0)
    {
        $items = $this->rawCartItems($customer_id);

        foreach ($items as &$item) {
            $item['stock'] = $this->stock->getAvailable((int) $item['p_id'], (int) ($item['size_id'] ?? 0));
        }
        unset($item);

        return $items;
    }

    /* ---------- Reading the cart ---------- */

    /** Rebuilds guest lines from the session, dropping products that no longer exist. */
    private function rawCartItems($customer_id = 0)
    {
        if (!$this->isGuest()) {
            return $this->cartModel->getCartItems((int) $customer_id);
        }

        $this->ensureGuestCart();
        $items = [];

        foreach ($_SESSION['guest_cart'] as $key => $qty) {
            [$productId, $sizeId] = array_map('intval', explode(':', $key));
            $product = $this->productModel->getProductById($productId);
            $size = $sizeId > 0 ? $this->productModel->getProductSize($productId, $sizeId) : null;

            if ($product === false || $size === false) {
                unset($_SESSION['guest_cart'][$key]);
                continue;
            }

            $items[] = [
                'c_id' => 0,
                'p_id' => $productId,
                'size_id' => $sizeId,
                'qty' => (int) $qty,
                'product_title' => $product['product_title'],
                'product_price' => $size ? $size['price'] : $product['product_price'],
                'product_image' => $product['product_image'] ?? '',
                'size_name' => $size ? $size['size_name'] : null,
            ];
        }

        return $items;
    }

    public function getCartCount($customer_id = 0)
    {
        if ($this->isGuest()) {
            $this->ensureGuestCart();
            return (int) array_sum(array_map('intval', $_SESSION['guest_cart']));
        }

        return $this->cartModel->getCartCount((int) $customer_id);
    }

    public function getCartTotal($customer_id = 0)
    {
        $total = 0.0;

        foreach ($this->rawCartItems($customer_id) as $item) {
            $total += (int) $item['qty'] * (float) $item['product_price'];
        }

        return $total;
    }

    /** Clamps to available stock for both the session cart and the database cart. */
    public function updateQty($product_id, $new_qty, $customer_id = 0, $size_id = 0)
    {
        $product_id = (int) $product_id;
        $new_qty = (int) $new_qty;

        if ($product_id < 1 || $new_qty < 1) {
            return false;
        }

        $available = $this->stock->getAvailable($product_id, $size_id);
        if ($new_qty > $available) {
            $new_qty = max(1, $available);
            $this->lastMessage = $available > 0
                ? 'Only ' . $available . ' in stock, so the quantity is now ' . $available . '.'
                : 'This item is out of stock. Remove it to continue to checkout.';
        }

        if ($this->isGuest()) {
            $this->ensureGuestCart();
            $key = $this->guestKey($product_id, $size_id);

            if (!isset($_SESSION['guest_cart'][$key])) {
                return false;
            }

            $_SESSION['guest_cart'][$key] = $new_qty;
            return true;
        }

        return $this->cartModel->updateQty($product_id, $new_qty, (int) $customer_id, (int) $size_id);
    }

    public function removeFromCart($product_id, $customer_id = 0, $size_id = 0)
    {
        if ($this->isGuest()) {
            $this->ensureGuestCart();
            unset($_SESSION['guest_cart'][$this->guestKey($product_id, $size_id)]);
            return true;
        }

        return $this->cartModel->removeFromCart((int) $product_id, (int) $customer_id, (int) $size_id);
    }

    public function clearCart($customer_id = 0)
    {
        if ($this->isGuest()) {
            $_SESSION['guest_cart'] = [];
            return true;
        }

        return $this->cartModel->clearCart((int) $customer_id);
    }

    /* ---------- After sign-in ---------- */

    /**
     * Move the guest session cart into the logged-in customer's database cart.
     * Matching lines (same product and size) have their quantities added together.
     */
    public function mergeGuestCartIntoCustomer($customer_id)
    {
        $customer_id = (int) $customer_id;

        if ($customer_id < 1) {
            return false;
        }

        $this->ensureGuestCart();

        foreach ($_SESSION['guest_cart'] as $key => $qty) {
            [$productId, $sizeId] = array_map('intval', explode(':', $key));
            $qty = (int) $qty;

            if ($productId > 0 && $qty > 0) {
                $this->cartModel->addToCart($productId, $qty, $customer_id, $sizeId);
            }
        }

        $_SESSION['guest_cart'] = [];

        return true;
    }
}

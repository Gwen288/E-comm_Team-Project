<?php

require_once __DIR__ . '/../classes/CartClass.php';
require_once __DIR__ . '/../classes/ProductClass.php';

class CartController
{
    private $cartModel;
    private $productModel;

    public function __construct()
    {
        $this->cartModel = new CartClass();
        $this->productModel = new ProductClass();
    }

    private function ensureGuestCart()
    {
        if (!isset($_SESSION['guest_cart']) || !is_array($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
    }

    private function isGuest()
    {
        return !is_logged_in();
    }

    public function isInCart($product_id, $customer_id = 0)
    {
        $product_id = (int) $product_id;

        if ($this->isGuest()) {
            $this->ensureGuestCart();
            return isset($_SESSION['guest_cart'][$product_id]);
        }

        return $this->cartModel->isInCart($product_id, (int) $customer_id);
    }

    public function addToCart($product_id, $qty, $customer_id = 0)
    {
        $product_id = (int) $product_id;
        $qty = (int) $qty;

        if ($product_id < 1 || $qty < 1) {
            return false;
        }

        if ($this->productModel->getProductById($product_id) === false) {
            return false;
        }

        if ($this->isGuest()) {
            $this->ensureGuestCart();

            if (isset($_SESSION['guest_cart'][$product_id])) {
                $_SESSION['guest_cart'][$product_id] += $qty;
            } else {
                $_SESSION['guest_cart'][$product_id] = $qty;
            }

            return true;
        }

        return $this->cartModel->addToCart($product_id, $qty, (int) $customer_id);
    }

    public function getCartItems($customer_id = 0)
    {
        if ($this->isGuest()) {
            $this->ensureGuestCart();
            $items = [];

            foreach ($_SESSION['guest_cart'] as $productId => $qty) {
                $product = $this->productModel->getProductById((int) $productId);

                if ($product === false) {
                    unset($_SESSION['guest_cart'][$productId]);
                    continue;
                }

                $items[] = [
                    'c_id' => 0,
                    'p_id' => (int) $productId,
                    'qty' => (int) $qty,
                    'product_title' => $product['product_title'],
                    'product_price' => $product['product_price'],
                    'product_image' => $product['product_image'] ?? ''
                ];
            }

            return $items;
        }

        return $this->cartModel->getCartItems((int) $customer_id);
    }

    public function getCartCount($customer_id = 0)
    {
        if ($this->isGuest()) {
            $this->ensureGuestCart();
            $total = 0;

            foreach ($_SESSION['guest_cart'] as $qty) {
                $total += (int) $qty;
            }

            return $total;
        }

        return $this->cartModel->getCartCount((int) $customer_id);
    }

    public function getCartTotal($customer_id = 0)
    {
        $items = $this->getCartItems($customer_id);
        $total = 0.0;

        foreach ($items as $item) {
            $total += (int) $item['qty'] * (float) $item['product_price'];
        }

        return $total;
    }

    public function updateQty($product_id, $new_qty, $customer_id = 0)
    {
        $product_id = (int) $product_id;
        $new_qty = (int) $new_qty;

        if ($product_id < 1 || $new_qty < 1) {
            return false;
        }

        if ($this->isGuest()) {
            $this->ensureGuestCart();

            if (!isset($_SESSION['guest_cart'][$product_id])) {
                return false;
            }

            $_SESSION['guest_cart'][$product_id] = $new_qty;
            return true;
        }

        return $this->cartModel->updateQty($product_id, $new_qty, (int) $customer_id);
    }

    public function removeFromCart($product_id, $customer_id = 0)
    {
        $product_id = (int) $product_id;

        if ($this->isGuest()) {
            $this->ensureGuestCart();
            unset($_SESSION['guest_cart'][$product_id]);
            return true;
        }

        return $this->cartModel->removeFromCart($product_id, (int) $customer_id);
    }

    public function clearCart($customer_id = 0)
    {
        if ($this->isGuest()) {
            $_SESSION['guest_cart'] = [];
            return true;
        }

        return $this->cartModel->clearCart((int) $customer_id);
    }

    /**
     * Move guest session cart into the logged-in customer's database cart.
     */
    public function mergeGuestCartIntoCustomer($customer_id)
    {
        $customer_id = (int) $customer_id;

        if ($customer_id < 1) {
            return false;
        }

        $this->ensureGuestCart();

        if (empty($_SESSION['guest_cart'])) {
            return true;
        }

        foreach ($_SESSION['guest_cart'] as $productId => $qty) {
            $productId = (int) $productId;
            $qty = (int) $qty;

            if ($productId < 1 || $qty < 1) {
                continue;
            }

            if ($this->cartModel->isInCart($productId, $customer_id)) {
                $items = $this->cartModel->getCartItems($customer_id);
                $currentQty = 0;

                foreach ($items as $item) {
                    if ((int) $item['p_id'] === $productId) {
                        $currentQty = (int) $item['qty'];
                        break;
                    }
                }

                $this->cartModel->updateQty($productId, $currentQty + $qty, $customer_id);
            } else {
                $this->cartModel->addToCart($productId, $qty, $customer_id);
            }
        }

        $_SESSION['guest_cart'] = [];

        return true;
    }
}

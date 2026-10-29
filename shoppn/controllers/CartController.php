
<?php

require_once __DIR__ . '/../classes/CartClass.php';

class CartController
{
    private $cartModel;

    public function __construct()
    {
        $this->cartModel = new CartClass();
    }

    public function isInCart($product_id, $customer_id)
    {
        return $this->cartModel->isInCart(
            $product_id,
            $customer_id
        );
    }

    public function addToCart($product_id, $qty, $customer_id)
    {
        return $this->cartModel->addToCart(
            $product_id,
            $qty,
            $customer_id
        );
    }

    public function getCartItems($customer_id)
    {
        return $this->cartModel->getCartItems($customer_id);
    }

    public function getCartCount($customer_id)
    {
        return $this->cartModel->getCartCount($customer_id);
    }

    public function getCartTotal($customer_id)
    {
        return $this->cartModel->getCartTotal($customer_id);
    }

    public function updateQty($product_id, $new_qty, $customer_id)
    {
        return $this->cartModel->updateQty(
            $product_id,
            $new_qty,
            $customer_id
        );
    }

    public function removeFromCart($product_id, $customer_id)
    {
        return $this->cartModel->removeFromCart(
            $product_id,
            $customer_id
        );
    }
}

<?php

namespace Components\Shop\Model;

use Framework\CMS as CMS,
    Bazalt\ORM;

class Cart extends Base\Cart
{
    public static function create(CMS\Model\User $user)
    {
        $cart = new Cart();
        $cart->site_id = CMS\Bazalt::getSiteId();
        if ($user->isGuest()) {
            $cart->session_id = Session::Singleton()->getSessionId();
        } else {
            $cart->user_id = $user->id;
        }

        return $cart;
    }

    public static function getByIdAndSiteId($id, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\Ecommerce\Model\Cart c')
                ->where('c.id = ?', (int)$id)
                ->andWhere('c.site_id = ?', (int)$siteId);

        return $q->fetch();
    }

    public static function getUserCart(CMS\Model\User $user, $siteId = null, $is_edit)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\Ecommerce\Model\Cart c')
                ->where('c.site_id = ?', $siteId);

        if ($user->isGuest()) {
            $q->andWhere('c.session_id = ?', Session::Singleton()->getSessionId());
        } else {
            $q->andWhere('c.user_id = ?', $user->id);
        }

        $cart = $q->fetch();
        if (!$cart) {
            if ($is_edit) {
                $cart = self::create($user);
                $cart->save();
            }
        }
        return $cart;
    }

    public function addProduct($productId, $count = 1)
    {
        $product = Components\Ecommerce\Model\Product::getByIdAndSiteId($productId, $this->site_id);
        if (!$product) {
            throw new Exception('Product not found');
        }

        $q = ORM::select('Components\Ecommerce\Model\CartRefProduct r')
                ->where('r.cart_id = ?', $this->id)
                ->andWhere('r.product_id = ?', $productId);

        $productCart = $q->fetch();
        if (!$productCart) {
            $productCart = new Components\Ecommerce\Model\CartRefProduct();
            $productCart->cart_id = $this->id;
            $productCart->product_id = $productId;
            $productCart->count = 0;
        }
        $productCart->count += (int)$count;
        $productCart->save();

        $product->count = $productCart->count;
        return $product;
    }

    public function removeProduct($productId, $count = null)
    {
        $product = Components\Ecommerce\Model\Product::getByIdAndSiteId($productId, $this->site_id);
        if (!$product) {
            throw new Exception('Product not found');
        }

        $q = ORM::select('Components\Ecommerce\Model\CartRefProduct r')
                ->where('r.cart_id = ?', $this->id)
                ->andWhere('r.product_id = ?', $productId);

        $productCart = $q->fetch();
        if ($productCart) {
            if (is_numeric($count)) {
                $productCart->count -= $count;
                if ($productCart->count > 0) {
                    $productCart->save();
                    return;
                }
            }
            $productCart->delete();
        }
    }

    public function changeProductCount($productId, $count)
    {
        $product = Components\Ecommerce\Model\Product::getByIdAndSiteId($productId, $this->site_id);
        if (!$product) {
            throw new Exception('Product not found');
        }

        $q = ORM::select('Components\Ecommerce\Model\CartRefProduct r')
                ->where('r.cart_id = ?', $this->id)
                ->andWhere('r.product_id = ?', $productId);

        $productCart = $q->fetch();
        if (!$productCart) {
            throw new Exception('Product not found in cart');
        }
        if (is_numeric($count) && $count >= 1 && $count < 100000) {
            $productCart->count = $count;
            $productCart->save();
        }
        return $productCart;
    }

    public function getSum()
    {
        $products = $this->Products->get();

        $sum = 0;
        foreach ($products as $product) {
            $sum += $product->price * $product->count;
        }
        return $sum;
    }

    public function saveAsOrder()
    {
        $order = Components\Ecommerce\Model\Order::create($this);

        $order->save();

        $products = $this->Products->get();
        foreach ($products as $product) {
            $order->Products->add($product, array('count' => $product->count));
            $this->Products->remove($product);
        }
        return $order;
    }

    public function getOrderById($orderId)
    {
        $q = ORM::select('Components\Ecommerce\Model\Order o')
                ->where('cart_id = ?', $this->id)
                ->andWhere('id = ?', $orderId);

        return $q->fetch();
    }

    public function addToCompare($productId)
    {
        $product = Components\Ecommerce\Model\Product::getByIdAndSiteId($productId, $this->site_id);
        if (!$product) {
            throw new Exception('Product not found');
        }
        $Components\p = ComEcommerce\Model\Compare::getByCartAndProduct($this, $product);
        if (!$comp) {
            $Components\p = ComEcommerce\Model\Compare::create($this, $product);
            $comp->save();
        }
        return $product;
    }
}
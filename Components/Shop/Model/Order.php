<?php

class ComEcommerce_Model_Order extends ComEcommerce_Model_Base_Order
{
    const STATUS_IS_PENDING = 0;

    const STATUS_PAID = 1;

    const STATUS_WAIT_FOR_CLIENT = 2;

    const STATUS_DELIVERING = 3;

    const STATUS_WAITING_FOR_PRODUCT = 4;

    const STATUS_CANCELED = 5;

    public static function getStatuses()
    {
        return array(
            self::STATUS_IS_PENDING             => __('is pending', ComEcommerce::getName()),
            self::STATUS_PAID                   => __('paid', ComEcommerce::getName()),
            self::STATUS_WAIT_FOR_CLIENT        => __('the product is waiting for a client', ComEcommerce::getName()),
            self::STATUS_DELIVERING             => __('the order is delivering', ComEcommerce::getName()),
            self::STATUS_WAITING_FOR_PRODUCT    => __('waiting for the product', ComEcommerce::getName()),
            self::STATUS_CANCELED               => __('canceled', ComEcommerce::getName())
        );
    }

    public static function deleteByIds($ids)
    {
        if(!is_array($ids)) {
            $ids = array($ids);
        }
        $q = ORM::delete('ComEcommerce_Model_Order o')
            ->whereIn('o.id', $ids)
            ->andWhere('o.site_id = ?', CMS_Bazalt::getSiteId());

        return $q->exec();
    }

    public function getStatus()
    {
        $statuses = self::getStatuses();

        return $statuses[$this->status];
    }

    public static function create(ComEcommerce_Model_Cart $cart = null)
    {
        $order = new ComEcommerce_Model_Order();
        if ($cart != null) {
            $order->site_id = CMS_Bazalt::getSiteId();
            $order->cart_id = $cart->id;
            $order->price = $cart->getSum();
        }

        return $order;
    }

    public static function getCollection()
    {
        $q = ORM::select('ComEcommerce_Model_Order o')
                ->where('o.site_id = ?', CMS_Bazalt::getSiteId())
                ->orderBy('o.created_at DESC, o.id DESC');

        return new CMS_ORM_Collection($q);
    }
    
    public static function getCountFromDate($date = null)
    {
        $q = ORM::select('ComEcommerce_Model_Order o');
        if ($date != null) {
            $q->where('o.created_at > FROM_UNIXTIME(?)', $date);
        }
        $q->andWhere('o.site_id = ?', CMS_Bazalt::getSiteId());
        return $q->exec();
    }

    public static function getByIdAndSiteId($id, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS_Bazalt::getSiteId();
        }
        $q = ORM::select('ComEcommerce_Model_Cart c')
                ->where('c.id = ?', (int)$id)
                ->andWhere('c.site_id = ?', (int)$siteId);

        return $q->fetch();
    }

    public static function getUserCart(CMS_Model_User $user, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS_Bazalt::getSiteId();
        }
        $q = ORM::select('ComEcommerce_Model_Cart c')
                ->where('c.site_id = ?', $siteId);

        if ($user->isGuest()) {
            $q->andWhere('c.session_id = ?', Session::Singleton()->getSessionId());
        } else {
            $q->andWhere('c.user_id = ?', $user->id);
        }

        $cart = $q->fetch();
        if (!$cart) {
            $cart = self::create($user);
            $cart->save();
        }
        return $cart;
    }

    public function addProduct($productId, $count = 1)
    {
        $product = ComEcommerce_Model_Product::getByIdAndSiteId($productId, $this->site_id);
        if (!$product) {
            throw new Exception('Product not found');
        }

        $q = ORM::select('ComEcommerce_Model_CartRefProduct r')
                ->where('r.cart_id = ?', $this->id)
                ->andWhere('r.product_id = ?', $productId);

        $productCart = $q->fetch();
        if (!$productCart) {
            $productCart = new ComEcommerce_Model_CartRefProduct();
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
        $product = ComEcommerce_Model_Product::getByIdAndSiteId($productId, $this->site_id);
        if (!$product) {
            throw new Exception('Product not found');
        }

        $q = ORM::select('ComEcommerce_Model_CartRefProduct r')
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

    public function getSum()
    {
        $products = $this->Products->get();

        $sum = 0;
        foreach ($products as $product) {
            $sum += $product->price * $product->count;
        }
        return $sum;
    }

    public static function getNewOrders()
    {
        $q = ORM::select('ComEcommerce_Model_Order o')
                ->where('o.status = ?', self::STATUS_IS_PENDING);

        $q->andWhere('o.site_id = ?', CMS_Bazalt::getSiteId());

        return $q->fetchAll();
    }
}
<?php

namespace Components\Shop\Model;

use Framework\CMS as CMS,
    Bazalt\ORM;

class WishList extends Base\WishList
{
    const TYPE_WISH = 'wish';

    public static function getForUser(CMS\Model\User $user, $type = self::TYPE_WISH)
    {
        $q = WishList::select()
            ->where('user_id = ?', (int)$user->id)
            ->andWhere('type = ?', $type);
        return $q->fetchAll();
    }

    public static function getCountForUser(CMS\Model\User $user)
    {
        $q = WishList::select('COUNT(*) as cnt')
            ->where('user_id = ?', (int)$user->id);
        return (int)$q->fetch('stdClass')->cnt;
    }

    public static function saveProduct(Product $product, CMS\Model\User $user, $type = self::TYPE_WISH)
    {
        $o = new WishList();
        $o->user_id = (int)$user->id;
        $o->product_id = (int)$product->id;
        $o->type = $type;
        $o->created_at = date('Y-m-d H:i:s');
//        print_r($o);exit;
        $o->save();
        return $o;
    }

    public static function deleteProduct(Product $product, CMS\Model\User $user, $type = self::TYPE_WISH)
    {
        $q = ORM::delete('Components\Shop\Model\WishList')
            ->where('user_id = ?', (int)$user->id)
            ->andWhere('product_id = ?', (int)$product->id)
            ->andWhere('type = ?', $type);
        $q->exec();
    }

    public static function getProductsCollection(CMS\Model\User $user, $type = self::TYPE_WISH)
    {
        $q = ORM::select('Components\Shop\Model\Product p')
            ->innerJoin('Components\Shop\Model\WishList wl', array('product_id', 'p.id'))
            ->andWhere('p.shop_id = ?', \Components\Shop\Component::currentShop()->id)
            ->andWhere('wl.user_id = ?', (int)$user->id)
            ->andWhere('wl.type = ?', $type)
            ->andWhere('p.is_published = ?', 1)
            ->groupBy('p.id')
            ->orderBy('p.price');

        return new CMS\ORM\Collection($q);
    }
}
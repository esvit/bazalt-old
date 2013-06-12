<?php

namespace Components\Shop\Model;

use Framework\CMS as CMS,
    Bazalt\ORM;

class Shop extends Base\Shop
{
    public static function create()
    {
        $o = new Shop();
        $o->site_id = CMS\Bazalt::getSiteId();
        return $o;
    }

    public static function getCollection()
    {
        $q = Shop::select();

        return new CMS\ORM\Collection($q);
    }
}
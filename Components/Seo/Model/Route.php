<?php

namespace Components\Seo\Model;

use Framework\System\ORM\ORM,
    Framework\CMS as CMS;

class Route extends Base\Route
{
    public static function create()
    {
        $route = new Route();
        $route->site_id = CMS\Bazalt::getSiteId();
        return $route;
    }

    public static function getByName($name)
    {
        $q = Route::select()
                ->where('(site_id IS NULL OR site_id = ?)', CMS\Bazalt::getSiteId())
                ->andWhere('name = ?', $name)
                ->orderBy('site_id DESC')
                ->limit(1);

        return $q->fetch();
    }
}
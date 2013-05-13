<?php

namespace Components\News\Model;

use \Framework\CMS as CMS;
use Framework\System\Routing\Route;
use Framework\Core\Helper\Url;
use Framework\System\ORM\ORM;

class Region extends Base\Region implements \Framework\System\Routing\Sluggable
{
    public function toUrl(Route $route)
    {
        return $this->alias;
    }

    public static function getByAlias($alias)
    {
        $q = Region::select()->where('alias = ?', $alias);

        return $q->fetch();
    }

    public function toCase()
    {
        return $this->title_in_case;
    }
}
<?php

namespace Components\Shop\Model;

use Framework\CMS as CMS,
    Framework\System\ORM\ORM;

class Brand extends Base\Brand
{
    public static function create()
    {
        $o = new Brand();
        $o->site_id = CMS\Bazalt::getSiteId();
        return $o;
    }

    public static function getByIdAndSiteId($id, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\Ecommerce\Model\Brands b')
                ->where('b.id = ?', (int)$id)
                ->andWhere('b.site_id = ?', (int)$siteId);

        return $q->fetch();
    }

    public static function getCollection()
    {
        $q = ORM::select('Components\Ecommerce\Model\Brands b')
                ->where('site_id = ?', CMS\Bazalt::getSiteId())
                ->orderBy('b.title');

        return new CMS\ORM_Collection($q);
    }
    
    public static function getList()
    {
        $q = ORM::select('Components\Ecommerce\Model\Brands b')
            ->where('b.site_id = ?', CMS\Bazalt::getSiteId())
            ->orderBy('b.title');
        return $q->fetchAll();
    }

    public static function getRoot()
    {
        $q = ORM::select('Components\Ecommerce\Model\Brands')
                ->where('depth = ?', 0);

        $root = $q->fetch();
        if (!$root) {
            $root = new Components\Ecommerce\Model\Brands();
            $root->brand_id = 1;
            $root->lft = 1;
            $root->rgt = 2;
            $root->depth = 0;
            $root->save();
        }
        return $root;
    }

    public function getThumb($size = 'big')
    {
        return CMS\Image::getThumb($this->logo, $size);
    }
}

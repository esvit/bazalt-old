<?php

namespace Components\Pages\Model;

use Framework\CMS as CMS,
    Bazalt\ORM;

class Category extends Base\Category
{
    public static function create()
    {
        $category = new Category();
        $category->site_id = CMS\Bazalt::getSiteId();
        $category->is_publish = 0;

        return $category;
    }

    public static function getSiteRootCategory($siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\Pages\Model\Category c')
                ->where('c.site_id = ?', $siteId)
                ->andWhere('c.depth = 0');

        $category = $q->fetch();
        if (!$category) {
            $category = Category::create();
            $category->site_id = $siteId;
            $category->lft = 1;
            $category->rgt = 2;
            $category->depth = 0;
            $category->save();
        }
        return $category;
    }

    public function getSubcategories($depth = 1)
    {
        return $this->PublicElements->get($depth);
    }

    public static function getByAlias($alias, $category = null, $siteId = null)
    {
		if (!$siteId) {
			$siteId = CMS\Bazalt::getSiteId();
		}
        $q = ORM::select('ComPages_Model_Category r')
				->andWhere('r.site_id = ?', $siteId)
				->andWhere('r.alias LIKE ?', $alias);

        if ($category != null) {
            if(is_numeric($category)) {
                $q->andWhere('r.site_id = ?', (int)$category);
            } elseif ($category instanceof Components\Pages\Model\Category) {
                $q->andWhere('r.site_id = ?', $category->site_id);
                $q->andWhere('r.lft > ?', $category->lft);
                $q->andWhere('r.rgt < ?', $category->rgt);
            }
        }
        return $q->limit(1)->fetch();
    }

    /**
     * Return category by path parts
     */
    public static function getByPath(array $parts, Category $root = null)
    {
        if (!is_array($parts) || count($parts) == 0) {
            return null;
        }

        $langId = CMS\Language::getCurrentLanguage()->id;

        $nextElement = $root;
        foreach ($parts as $i => $part) {
            $qByAlias = ORM::select('Components\Pages\Model\Category c')
                            ->andWhere('c.alias = ?', $part)
                            ->andWhere('c.is_publish = ?', 1);

            if ($nextElement) {
                $qByAlias->andWhere('c.depth = ?', $nextElement->depth + 1)
                         ->andWhere('c.lft >= ? AND c.rgt <= ?', array($nextElement->lft, $nextElement->rgt))
                         ->andWhere('c.site_id = ?', $nextElement->site_id);
            }

            $nextElement = $qByAlias->fetch();
            if (!$nextElement) {
                return null;
            }
        }
        return $nextElement;
    }



    public function toArray()
    {
        $res = parent::toArray();
        $res['is_publish'] = $this->is_publish == 1;
        unset($res['Childrens']);
        $elements = $this->Elements->get();
        $count = 0;
        $toArray = function($items) use (&$toArray, &$count) {
            $result = [];
            foreach ($items as $key => $item) {
                $count++;
                $res = $item->toArray();
                $res['children'] = (is_array($item->Childrens) && count($item->Childrens)) ? $toArray($item->Childrens) : [];
                $result[$key] = $res;
            }
            return $result;
        };
        $res['children'] = $toArray($elements);
        $res['count'] = $count;
        if (!$res['config']) {
            $res['config'] = new \stdClass();
        }
        return $res;
    }
}

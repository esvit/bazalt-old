<?php

namespace Components\News\Model;

use Bazalt\ORM,
    Framework\CMS as CMS;
use Framework\System\Routing\Route;
use Components\News\Component;

class Category extends Base\Category
{
    public static function create()
    {
        $category = new Category();
        $category->site_id = CMS\Bazalt::getSiteId();

        return $category;
    }

    public static function getSiteRootCategory($siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\News\Model\Category c')
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

    public static function getCategories($onlyPublished = false, $siteId = null)
    {
        if ($siteId === null) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('\Components\NewsChannel\Model\Category c')
            ->where('site_id = ?', $siteId)
            ->andWhere('depth > 0')
            ->orderBy('lft');

        if ($onlyPublished) {
            $q->andWhere('is_hidden = ?', 0)
              ->andWhere('is_publish = ?', 1);
        }
        return $q->fetchAll();
    }

    public static function getByAlias($alias, $category = null, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('\Components\NewsChannel\Model\Category r')
                ->andWhere('r.site_id = ?', $siteId)
                ->andWhere('r.alias LIKE ?', $alias);

        if ($category != null) {
            if(is_numeric($category)) {
                $q->andWhere('r.category_id = ?', (int)$category);
            } elseif ($category instanceof Category) {
                $q->andWhere('r.site_id = ?', $category->site_id);
                $q->andWhere('r.lft > ?', $category->lft);
                $q->andWhere('r.rgt < ?', $category->rgt);
            }
        }
        return $q->limit(1)->fetch();
    }

    public function hasArticles($region = null, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }

        $qCategory = ORM::select('\Components\NewsChannel\Model\Category c', 'c.id')
                        ->andWhere('c.is_publish = ?', 1)
                        ->andWhere('c.lft >= ? AND c.rgt <= ?', array($this->lft, $this->rgt))
                        ->andWhere('c.site_id = ?', $siteId);

        $q = ORM::select('\Components\NewsChannel\Model\Article a', 'COUNT(*) AS cnt')
            ->andWhere('a.site_id = ?', $siteId)
            ->andWhereIn('a.category_id', $qCategory);

        if ($region) {
            $q->andWhere('a.region_id = ?', $region->id);
        }

        return $q->fetch()->cnt > 0;
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
            $qByAlias = ORM::select('\Components\NewsChannel\Model\Category c')
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

    public function isActive()
    {
        static $currentCategory;

        $newsitem = Component::currentNews();
        if ($newsitem && $newsitem->category_id && !$currentCategory) {
            $currentCategory = $newsitem->Category;
        }
        if ($currentCategory && (($this->id == $currentCategory->id) || ($this->lft > $currentCategory->lft && $this->rgt < $currentCategory->rgt))) {
            return true;
        }
        return false;
    }

    public function getUrl($region = null, $withHost = false)
    {
        if ($region instanceof \Components\Geo\Model\State) {
            return Route::urlFor('ComNewsChannel.Region.Category', array('region' => $region->alias, 'category' => $this->Elements), $withHost);
        }
        return Route::urlFor('News.Category', array('category' => $this->Elements), $withHost);
    }

    public function toArray()
    {
        $arr = parent::toArray();
        $arr['childrens_count'] = isset($this->Childrens) ? count($this->Childrens) : 0;

        return $arr;
    }

    /**
     * @param null $siteId
     * @return CMS_ORM_Collection
     */
    public static function getCategoriesCollection($siteId = null)
    {
        if ($siteId == null) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $root = self::getSiteRootCategory($siteId);

        $q = $root->PublicElements->getQuery();

        return new CMS\ORM\Collection($q);
    }
}

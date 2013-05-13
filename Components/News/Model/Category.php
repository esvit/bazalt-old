<?php

namespace Components\News\Model;

use Framework\System\ORM\ORM,
    Framework\CMS as CMS;
use Framework\System\Routing\Route;
use Components\News\Component;

class Category extends Base\Category implements \Framework\System\Routing\Sluggable
{
    public static function create()
    {
        $category = new Category();
        $category->site_id = CMS\Bazalt::getSiteId();

        return $category;
    }

    public function toUrl(Route $route)
    {
        $url = urlencode($this->alias);
        if ($route->param('fullPath')) {
            foreach ($this->Elements->getPath() as $elem) {
                if ($elem->depth > 0) {
                    $url = urlencode($elem->alias) . '/' . $url;
                }
            }
        }
        return $url;
    }

    public function toBreadcrumb($breadcrumb, $region = null)
    {
        $params = ['region' => $region, 'category' => $this];
        $routeName = ($region) ? 'News.Region.Category' : 'News.Category';
        $url = urlencode($this->alias);
        foreach ($this->Elements->getPath() as $elem) {
            if ($elem->depth > 0) {
                $params['category'] = $elem;
                $breadcrumb = $breadcrumb->insert(Route::urlFor($routeName, $params), $elem->title);
            }
        }
        $params['category'] = $this;
        return $breadcrumb->insert(Route::urlFor($routeName, $params), $this->title);
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

        $qCategory = ORM::select('Components\News\Model\Category c', 'c.id')
                        ->andWhere('c.is_publish = ?', 1)
                        ->andWhere('c.lft >= ? AND c.rgt <= ?', array($this->lft, $this->rgt))
                        ->andWhere('c.site_id = ?', $siteId);

        $q = ORM::select('Components\News\Model\Article a', 'COUNT(*) AS cnt')
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
            $qByAlias = Category::select()
                            ->andWhere('alias = ?', $part)
                            ->andWhere('is_publish = ?', 1);

            if ($nextElement) {
                $qByAlias->andWhere('depth = ?', $nextElement->depth + 1)
                         ->andWhere('lft >= ? AND rgt <= ?', array($nextElement->lft, $nextElement->rgt))
                         ->andWhere('site_id = ?', $nextElement->site_id);
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
        $routeName = ($region instanceof Region) ? 'News.Region.Category' : 'News.Category';

        return Route::urlFor($routeName, array('category' => $this, 'region' => $region), $withHost);
    }

    public function toArray()
    {
        $res = parent::toArray();
        
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

        return $res;
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

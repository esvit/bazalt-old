<?php

namespace Components\Shop\Model;

use Framework\CMS as CMS,
    Bazalt\ORM,
    Framework\System\Routing as Routing;

class Category extends Base\Category implements Routing\Sluggable
{
    public static function create(Shop $shop = null)
    {
        $category = new Category();
        if ($shop || ($shop = \Components\Shop\Component::currentShop())) {
            $category->shop_id = $shop->id;
        }
        $category->lft = 1;
        $category->rgt = 2;

        return $category;
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

    public function toUrl(Routing\Route $route)
    {
        $url = urlencode($this->alias);
        if ($route->param('_fullPath')) {
            foreach ($this->Elements->getPath() as $elem) {
                if ($elem->depth > 0) {
                    $url = urlencode($elem->alias) . '/' . $url;
                }
            }
        }
        return $url;
    }

    /**
     * Return root category of shop
     */
    public static function getShopRootCategory(Shop $shop = null)
    {
        if (!$shop) {
            $shop = \Components\Shop\Component::currentShop();
        }
        $q = ORM::select('Components\Shop\Model\Category c')
                ->where('shop_id = ?', $shop->id)
                ->andWhere('depth = ?', 0);

        $category = $q->fetch();
        if (!$category) {
            $category = self::create($shop);
            $category->save();
        }
        return $category;
    }

    public static function getCategories($depth = null)
    {
        $root = self::getSiteRootCategory();

        return $root->Elements->get($depth);
    }

    public static function getByIdAndCompanyId($id, $companyId = null)
    {
        if (!$companyId) {
            $companyId = ComEcommerce::getCompanyId();
        }
        $q = ORM::select('Components\Ecommerce\Model\Category c')
                ->where('c.id = ?', (int)$id)
                ->andWhere('c.company_id = ?', (int)$companyId);

        return $q->fetch();
    }

    public function getCategoryBrands($onlyPublished = false)
    {
        $q = ORM::select('Components\Ecommerce\Model\Brands b', 'b.*, COUNT(DISTINCT p.id) AS `count`')
                ->leftJoin('Components\Ecommerce\Model\Product p', array('brand_id', 'b.id'))
                ->leftJoin('Components\Ecommerce\Model\ProductsFields f', array('product_id', 'p.id'))
                ->leftJoin('Components\Ecommerce\Model\Category c', array('id', 'pc.category_id'))
                ->where('c.lft >= ? AND c.rgt <= ?', array($this->lft, $this->rgt))
                ->andWhere('c.site_id = ?', CMS\Bazalt::getSiteId())
                ->groupBy('b.id')
                ->orderBy('b.title');

        if ($onlyPublished) {
            $q->andWhere('p.is_published = ?', 1);
        }
        return $q->fetchAll();
    }

    public function getProductTypes($onlyPublished = false)
    {
        $q = ORM::select('Components\Ecommerce\Model\ProductTypes pt', 'pt.*, COUNT(*) AS `count`')
                ->leftJoin('Components\Ecommerce\Model\Product p', array('type_id', 'pt.id'))
                ->leftJoin('Components\Ecommerce\Model\Category c', array('id', 'pc.category_id'))
                ->where('c.lft >= ? AND c.rgt <= ?', array($this->lft, $this->rgt))
                ->andWhere('c.site_id = ?', CMS\Bazalt::getSiteId())
                ->groupBy('pt.id');

        if ($onlyPublished) {
            $q->andWhere('p.is_published = ?', 1);
        }
        return $q->fetchAll();
    }

    public function getMinMaxPrice($onlyPublished = false)
    {
        $q = ORM::select('Components\Ecommerce\Model\Product p', 'MIN(p.price) AS min, MAX(p.price) AS max')
                ->leftJoin('Components\Ecommerce\Model\Category c', array('id', 'p.category_id'))
                ->where('c.lft >= ? AND c.rgt <= ?', array($this->lft, $this->rgt))
                ->andWhere('c.site_id = ?', CMS\Bazalt::getSiteId());

        if ($onlyPublished) {
            $q->andWhere('p.is_published = ?', 1);
        }
        $res = $q->fetch('stdClass');
        if (!$res) {
            $res = new \stdClass();
            $res->min = 1;
            $res->max = 1;
        }
        $res->min = floor($res->min / 100) * 100;
        $res->max = ceil($res->max / 100) * 100;
        if (!$res->min) {
            $res->min = 1;
        }
        return $res;
    }

    public function getProducts($onlyPublished = true)
    {
        $q = ORM::select('Components\Shop\Model\Product p','p.*')
            ->leftJoin('Components\Shop\Model\Category c', array('id', 'p.category_id'))
            ->where('c.lft >= ? AND c.rgt <= ?', array($this->lft, $this->rgt))
            ->andWhere('c.shop_id = ?', (int)$this->shop_id);

        if ($onlyPublished) {
            $q->andWhere('p.is_published = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public function getUrl($filter = null, $value = null, $removeFilter = false, $withPriceMask = false)
    {
        $params = array(
            'shopId' => $this->shop_id,
            'category' => $this,
            'filter' => ''
        );
        if ($filter != null) {
            if ($filter === true) {
                $params['filter'] = ComEcommerce::currentFilter()->toString(array(), $removeFilter, $withPriceMask);
            } else {
                $params['filter'] = ComEcommerce::currentFilter()->toString(array($filter => $value), $removeFilter, $withPriceMask);
            }
        }
        if (empty($params['filter'])) {
            return Routing\Route::urlFor('Shop.Category', $params);
        }
        return Route::urlFor('Shop.CategoryFilter', $params);
    }

    public static function getByAlias($alias, $category = null, $componentId = null)
    {
        $siteId = CMS\Bazalt::getSiteId();
        $q = ORM::select('Components\Ecommerce\Model\Category r', 'r.*')
                ->innerJoin('Components\Ecommerce\Model\CategoryLocale l', array('id', 'r.id'))
                ->where('r.site_id = ?', $siteId);

        $q->orWhere('l.alias LIKE ?', $alias);

        if ($category != null) {
            if(is_numeric($category)) {
                $q->andWhere('r.group_id = ?', (int)$category);
            } elseif ($category instanceof Components\Ecommerce\Model\Category) {
                $q->andWhere('r.group_id = ?', $category->group_id);
                $q->andWhere('r.lft > ?', $category->lft);
                $q->andWhere('r.rgt < ?', $category->rgt);
            }
        }

        //$q->endWhereGroup();
        return $q->limit(1)->fetch();
    }

    /**
     * Return category by path parts
     */
   /* public static function getByPath(array $parts, Category $root = null)
    {
        if (!is_array($parts) || count($parts) == 0) {
            return null;
        }

        $shop = \Components\Shop\Component::currentShop();
        $langId = CMS\Language::getCurrentLanguage()->id;

        $nextElement = $root;
        foreach ($parts as $i => $part) {
            $qByAlias = ORM::select('Components\Shop\Model\Category c', 'c.*')
                            ->andWhere('c.shop_id = ?', $shop->id)
                            ->andWhere('c.alias = ?', $part)
                            ->andWhere('c.is_published = ?', 1);

            if ($nextElement) {
                $qByAlias->andWhere('c.depth = ?', $nextElement->depth + 1)
                         ->andWhere('c.lft >= ? AND c.rgt <= ?', array($nextElement->lft, $nextElement->rgt))
                         ->andWhere('c.shop_id = ?', $nextElement->shop_id);
            }

            $nextElement = $qByAlias->fetch();
            if (!$nextElement) {
                return null;
            }
        }
        return $nextElement;
    }
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
                ->andWhere('is_published = ?', 1);

            if ($nextElement) {
                $qByAlias->andWhere('depth = ?', $nextElement->depth + 1)
                    ->andWhere('lft >= ? AND rgt <= ?', array($nextElement->lft, $nextElement->rgt))
                    ->andWhere('shop_id = ?', $nextElement->shop_id);
            }

            $nextElement = $qByAlias->fetch();
            if (!$nextElement) {
                return null;
            }
        }
        return $nextElement;
    }
}

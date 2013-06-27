<?php

namespace Components\Shop\Model;

use Framework\CMS as CMS,
    Framework\System\Routing as Routing,
    Framework\System\ORM\ORM,
    Framework\Core\Helper\Url;

class Product extends Base\Product
{
    public static function create($shop = null)
    {
        $product = new Product();
        if ($shop || ($shop = \Components\Shop\Component::currentShop())) {
            $product->shop_id = $shop->id;
        }
        $product->is_published = 1;
        $product->price = 0;
        return $product;
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['is_published'] = $res['is_published'] == '1';
        $res['url'] = $this->url();

        $res['images'] = [];
        $images = $this->Images->get();
        foreach ($images as $image) {
            $res['images'][] = $image->toArray();
        }
        return $res;
    }

    public function tagsSave($tags)
    {
        $tagsNew = array();
        $tagsOld = array();

        foreach ($this->Tags->get() as $tagObj) {
            $tagsOld[$tagObj->id] = $tagObj;
        }

        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if ($tagName != '') {
                $tag = Components\Tags\Model\Tag::getByName($tagName);
                if (!$tag) {
                    $tag = Components\Tags\Model\Tag::create($tagName);
                }

                if (!$this->Tags->has($tag)) {
                    $this->Tags->add($tag);
                    $tag->count++;
                    $tag->save();

                }
                $tagsNew[$tag->id] = $tag;
                $tag = null;
            }
        }

        foreach ($tagsOld as $id => $tag) {
            if (!isset($tagsNew[$id])) {
                $this->Tags->remove($tag);
                $tag->count--;
                if ($tag->count == 0) {
                    $tag->hit = 0;
                }
                $tag->save();
            }
        }
    }

    public static function getByIdAndSiteId($id, $siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Components\Shop\Model\Product p')
            ->where('p.id = ?', (int)$id)
            ->andWhere('p.site_id = ?', (int)$siteId);

        return $q->fetch();
    }

    public static function getByIdAndUserId($id, $userId = null)
    {
        if (!$userId) {
            $userId = CMS\user::getUser()->id;
        }
        $q = ORM::select('Components\Shop\Model\Product p')
            ->where('p.id = ?', (int)$id)
            ->andWhere('p.user_id = ?', (int)$userId);

        return $q->fetch();
    }

    public static function getUserList($siteId = null)
    {
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
        }

        $user = CMS\user::getUser();

        $q = ORM::select('Components\Shop\Model\Product p')
            ->where('p.user_id = ?', (int)$user->id)
            ->andWhere('p.site_id = ?', (int)$siteId);

        return new CMS\ORM\Collection($q);
    }

    public static function getPrice($minmax, $brandId = null, $siteId = null)
    {
        $q = ORM::select('Components\Shop\Model\Product p');

        if (isset($brandId)) {
            $q->Where('p.brand_id = ?', (int)$brandId);
        }
        if (!$siteId) {
            $siteId = CMS\Bazalt::getSiteId();
            $q->andWhere('p.site_id = ?', (int)$siteId);
        }
        if ($minmax) {
            $q->orderBy('p.price ASC LIMIT 0,1');
        } else {
            $q->orderBy('p.price DESC LIMIT 0,1');
        }
        return $q->fetch();
    }

    public static function getCollection($category = null, $onlyPublished = false)
    {
        $q = ORM::select('Components\Shop\Model\Product p')
            ->andWhere('p.shop_id = ?', \Components\Shop\Component::currentShop()->id)
            ->groupBy('p.id')
            ->orderBy('p.price');

        if ($category && $category->depth > 0) {
            $childsQuery = ORM::select('Components\Shop\Model\Category c', 'id')
                ->where('c.lft BETWEEN ? AND ?', array($category->lft, $category->rgt))
                ->andWhere('c.shop_id = ?', $category->shop_id);

            $q->andWhereIn('p.category_id', $childsQuery);
        }
        if ($onlyPublished) {
            $q->andWhere('p.is_published = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public static function getHitsCollection($onlyPublished = false)
    {
        $currentLanguage = CMS\Language::getCurrentLanguage();
        $defaultLanguage = CMS\Language::getDefaultLanguage();

        $q = ORM::select('Components\Shop\Model\Product p', 'p.*, pl.title AS title, im.image as image')
            ->leftJoin('Components\Shop\Model\ProductImage im', array('product_id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductLocale pl', array('id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductField f', array('product_id', 'p.id'))
            ->andWhere('p.site_id = ?', CMS\Bazalt::getSiteId())
            ->andWhere('pl.lang_id = ?', $currentLanguage->id)
            ->andWhere('p.hit = ?', 1)
            ->groupBy('p.id')
            ->orderBy('IF(p.price = 0, 999999999, p.price)');

        if ($onlyPublished) {
            $q->andWhere('p.publish = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public static function getLatestCollection($onlyPublished = false)
    {
        $currentLanguage = CMS\Language::getCurrentLanguage();
        $defaultLanguage = CMS\Language::getDefaultLanguage();

        $q = ORM::select('Components\Shop\Model\Product p', 'p.*, pl.title AS title, im.image as image')
            ->leftJoin('Components\Shop\Model\ProductImage im', array('product_id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductLocale pl', array('id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductField f', array('product_id', 'p.id'))
            ->andWhere('p.site_id = ?', CMS\Bazalt::getSiteId())
            ->andWhere('pl.lang_id = ?', $currentLanguage->id)
            ->andWhere('p.is_latest = ? OR UNIX_TIMESTAMP(p.created_at) > ?', array(1, time() - 7 * 24 * 60 * 60))
            ->groupBy('p.id')
            ->orderBy('p.created_at DESC');

        if ($onlyPublished) {
            $q->andWhere('p.publish = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public static function getDiscountsCollection($onlyPublished = false)
    {
        $currentLanguage = CMS\Language::getCurrentLanguage();
        $defaultLanguage = CMS\Language::getDefaultLanguage();

        $q = ORM::select('Components\Shop\Model\Product p', 'p.*, pl.title AS title, im.image as image')
            ->leftJoin('Components\Shop\Model\ProductImage im', array('product_id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductLocale pl', array('id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductField f', array('product_id', 'p.id'))
            ->andWhere('p.site_id = ?', CMS\Bazalt::getSiteId())
            ->andWhere('pl.lang_id = ?', $currentLanguage->id)
            ->andWhere('p.is_discount = ?', 1)
            ->groupBy('p.id')
            ->orderBy('p.created_at DESC');

        if ($onlyPublished) {
            $q->andWhere('p.publish = ?', 1);
        }
        return new CMS\ORM\Collection($q);
    }

    public static function getProductsCollection($category, $onlyPublished = false, $filter = null)
    {
        $currentLanguage = CMS\Language::getCurrentLanguage();

        $qIn = ORM::select('Components\Shop\Model\Category c', 'c.id')
            ->where('c.lft BETWEEN ? AND ?', array($category->lft, $category->rgt));

        $q = ORM::select('Components\Shop\Model\Product p', 'p.*, pl.title AS title, im.image as image')
            ->leftJoin('Components\Shop\Model\ProductImage im', array('product_id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductLocale pl', array('id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductsCategories pc', array('product_id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductField f', array('product_id', 'p.id'))
            ->andWhere('p.site_id = ?', CMS\Bazalt::getSiteId())
        //->andWhere('pc.category_id = ?', $category->id)
            ->andWhere('pl.lang_id = ?', $currentLanguage->id)
            ->andWhereIn('pc.category_id', $qIn)
            ->groupBy('p.id')
            ->orderBy('IF(p.price = 0, 999999999, p.price)');

        if ($onlyPublished) {
            $q->andWhere('p.publish = ?', 1);
        }
        $filter = new ComShop_Filter($filter);
        $q = $filter->addToQuery($q);
        ComShop::currentFilter($filter);
        return new CMS\ORM\Collection($q);
    }

    public static function getCompanyProductsCollection($companyId, $category = null, $onlyPublished = false, $filter = null)
    {
        $q = ORM::select('Components\Shop\Model\Product p', 'p.*, im.image as image')
            ->leftJoin('Components\Shop\Model\ProductImage im', array('product_id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductsCategories pc', array('product_id', 'p.id'))
            ->leftJoin('Components\Shop\Model\ProductField f', array('product_id', 'p.id'))
            //->innerJoin('Components\Enterprise\Model\Company c', array('site_id', 'p.site_id'))
            ->andWhere('p.company_id = ?', $companyId)
        //->andWhere('pc.category_id = ?', $category->id)
            ->groupBy('p.id')
            ->orderBy('IF(p.price = 0, 999999999, p.price)');

        if ($category) {
            $qIn = ORM::select('Components\Shop\Model\Category c', 'c.id')
                ->where('c.lft BETWEEN ? AND ?', array($category->lft, $category->rgt));

            $q->andWhereIn('pc.category_id', $qIn);
        }

        if ($onlyPublished) {
            $q->andWhere('p.publish = ?', 1);
        }
        $filter = new ComShop_Filter($filter);
        $q = $filter->addToQuery($q);
        ComShop::currentFilter($filter);

        return new CMS\ORM\Collection($q);
    }

    public function saveFields($fields = array())
    {
        ORM::delete('Components\Shop\Model\ProductField')
            ->where('product_id = ?', $this->id)
            ->exec();

        $ids = array();
        foreach ($fields as $item) {
            $field = $item['field'];
            $value = $item['value'];

            $val = new Components\Shop\Model\ProductField();
            $val->product_id = $this->id;
            $val->field_id = $field->id;
            if (empty($value)) {
                $value = ' ';
            }
            $val->value = $value;
            $val->save();
        }
    }

    public function getFields($onlyPublished = false)
    {
        $productFields = array();
        $q = ORM::select('Components\Shop\Model\Field f', 'f.*')
            ->leftJoin('Components\Shop\Model\ProductTypesFields ptf', array('field_id', 'f.id'))
            ->where('ptf.product_type_id = ?', $this->type_id)
            ->orderBy('f.order, f.id');

        $fields = $q->fetchAll();

        foreach ($fields as $field) {
            $productFields[$field->id] = $field;
        }
        $q = ORM::select('Components\Shop\Model\Field f', 'f.*, pf.value AS `value`')
            ->leftJoin('Components\Shop\Model\ProductField pf', array('field_id', 'f.id'))
            ->where('pf.product_id = ?', $this->id)
            ->orderBy('f.order, f.id');

        $fields = $q->fetchAll();

        foreach ($fields as $field) {
            if (!isset($productFields[$field->id])) {
                $productFields[$field->id] = $field;
            }
            $productFields[$field->id]->is_custom = true;
            $value = ($field->isBool()) ? $field->value : trim($field->value);

            if ($field->isMultifield()) {
                $value = $productFields[$field->id]->value;
                if (!is_array($value)) {
                    $value = array();
                }
                $value [] = trim($field->value);
            }
            $productFields[$field->id]->value = $value;
        }
        return $productFields;
    }

    public function getMainPrice($param = '%0.2f %s')
    {
        $account = Components\Pay\Model\AccountType::getDefaultAccountType();
        /*$ref = Components\Shop\Model\ProductsPrices::getByParams(array(
            'product_id' => $this->id,
            'account_id' => $account->id
        ));*/
        //if($ref) {
        return sprintf($param, $this->price, $account->currency);
        //}
        return 0;
    }

    public function getAlias()
    {
        $this->alias = Url::cleanUrl($this->title);
        return $this->alias;
    }

    public function url()
    {
        return Routing\Route::urlFor('Shop.Product', [
            'productId' => $this->id,
            'shopId' => $this->shop_id,
            'product' => $this->getAlias()
        ]);
    }

    public function getReadableFields($onlyPublished = false)
    {
        $fields = $this->getFields($onlyPublished);
        /*$q = ORM::select('Components\Shop\Model\Field f', 'f.*, fl.*, pf.value AS value')
                ->innerJoin('Components\Shop\Model\ProductField pf', array('field_id', 'f.id'))
                ->innerJoin('Components\Shop\Model\FieldLocale fl', array('id', 'f.id'))
                ->where('pf.product_id = ?', $this->id)
                //->andWhere('f.is_published = ?', 1)
                ->andWhere('fl.lang_id = ?', CMS\Language::getCurrentLanguage()->id);

        $fields = $q->fetchAll();*/

        $currentLanguage = CMS\Language::getCurrentLanguage();
        $defaultLanguage = CMS\Language::getDefaultLanguage();

        foreach ($fields as &$field) {
            if ($field->type == Components\Shop\Model\Field::FIELD_TYPE_BOOLSET) {
                $data = $field->data;
                $value = $field->value;
                $val = array();
                foreach ($data as $k => $v) {
                    $title = null;
                    if (!in_array($k, $value)) {
                        continue;
                    }
                    if (isset($data[$k][$currentLanguage->alias])) {
                        $title = $data[$k][$currentLanguage->alias];
                    } else if (isset($data[$k][$defaultLanguage->alias])) {
                        $title = $data[$k][$defaultLanguage->alias];
                    } else {
                        continue;
                    }
                    if (!empty($title)) {
                        $val [] = $title;
                    }
                }
                $field->value = $val;
            } else if ($field->type == Components\Shop\Model\Field::FIELD_TYPE_SET) {
                $data = $field->data;
                $value = $field->value;
                $val = $field->value;
                if (isset($data[$value][$currentLanguage->alias])) {
                    $val = $data[$value][$currentLanguage->alias];
                } else if (isset($data[$value][$defaultLanguage->alias])) {
                    $val = $data[$value][$defaultLanguage->alias];
                }
                $field->value = $val;
            }
        }
        return $fields;
    }

    public function isNew()
    {
        if (!$this->created_at) {
            return false;
        }
        return ((strToTime($this->created_at) + 7 * 24 * 60 * 60) > time()) || $this->is_latest;
    }

    public static function getByCode($code)
    {
        $q = Components\Shop\Model\Product::select()
            ->where('code = ?', $code)
            ->limit(1);
        return $q->fetch();
    }

    public static function deleteByIds($ids, $siteId = null)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        if ($siteId == null) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::delete('Components\Shop\Model\Product a')
            ->whereIn('a.id', $ids)
            ->andWhere('a.site_id = ?', $siteId);

        return $q->exec();
    }


    public function inCurrentWishList($type = WishList::TYPE_WISH)
    {
        if(self::$_curWishList === null) {
            self::$_curWishList = array();
            $user = CMS\User::get();
            //@todo check is guest
            $user = CMS\Model\User::getById(1);
            $listItems = WishList::getForUser($user, $type);
            foreach($listItems as $listItem) {
                self::$_curWishList []= $listItem->type .'_' . $listItem->product_id;
            }
//            print_r(self::$_curWishList);exit($type .'_' . $this->id);
        }
        return in_array($type .'_' . $this->id, self::$_curWishList);
    }
}
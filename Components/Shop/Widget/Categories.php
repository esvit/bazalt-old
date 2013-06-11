<?php

namespace Components\Shop\Widget;

use Framework\CMS as CMS,
    Components\Shop\Component,
    Components\Shop\Model as Model;

class Categories extends CMS\Widget
{
    public function fetch()
    {
        $pCategory = Component::currentCategory();
        /*$cacheKey = __CLASS__ . 'fetch' . $this->widgetConfig->id . serialize($this->options) . CMS_User::getUser()->getRolesKey();
        if ($pCategory) {
            $cacheKey .= $pCategory->id;
        }
        $result = Cache::Singleton()->getCache($cacheKey);

        if (!$result) {*/
            $this->view()->assign('productCategory', $pCategory);
            $category = Model\Category::getShopRootCategory();
            if (!$category) {
                $result = parent::fetch();
            } else {
                $this->view()->assign('categories', $category->PublicElements->get());
                $result = parent::fetch();
            }
        /*    Cache::Singleton()->setCache($cacheKey, $result, false, array(ORM_BaseRecord::getTableName('ComEcommerce_Model_Category')));
        }*/

        return $result;
    }
}

<?php

namespace Components\Shop\Widget;

use Framework\CMS as CMS,
    Components\Shop\Component,
    Components\Shop\Model as Model;

class Brands extends CMS\Widget
{
    public function fetch()
    {
        $pCategory = Component::currentCategory();
        if (!$pCategory) {
            return parent::fetch();
        }
        /*$cacheKey = __CLASS__ . 'fetch' . serialize($this->options) . CMS_User::getUser()->getRolesKey() . $pCategory->id;
        $result = Cache::Singleton()->getCache($cacheKey);

        if (!$result) {*/
            //$this->view->assign('currentFilter', ComEcommerce::currentFilter());

            $this->view->assign('category', $pCategory);
            $this->view->assign('brands', $pCategory->getCategoryBrands(true));
            
            $result = parent::fetch();

        /*    Cache::Singleton()->setCache($cacheKey, $result, false, array(ORM_BaseRecord::getTableName('ComEcommerce_Model_Category')));
        }*/

        return $result;
    }
}
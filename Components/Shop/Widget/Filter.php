<?php

class ComEcommerce_Widget_Filter extends CMS_Widget_Component
{
    public function fetch()
    {
        $route = CMS_Mapper::getDispatchedRoute();

        if ($route->Rule->Name != 'ComEcommerce.ProductsCategoryFilter' &&
            $route->Rule->Name != 'ComEcommerce.ProductsCategory') {
            return;
        }

        $pCategory = ComEcommerce::productCategory();
        $cacheKey = __CLASS__ . 'fetch' . serialize($this->options) . CMS_User::getUser()->getRolesKey() . $pCategory->id;
        $result = Cache::Singleton()->getCache($cacheKey);

        if (!$result && $pCategory) {
            $this->view->assign('currentFilter', ComEcommerce::currentFilter());

            $this->view->assign('category', $pCategory);
            $types = $pCategory->getProductTypes(true);
            $this->view->assign('productTypes', $types);
            $this->view->assign('prices', $pCategory->getMinMaxPrice());

            if (count($types) == 1) {
                $type = $types[0];
                if ($type) {
                    $this->view->assign('fields', $type->getFields(true, true));
                }
            } else {
                return parent::fetch();
            }
            
            
            $result = parent::fetch();

            //Cache::Singleton()->setCache($cacheKey, $result, false, array(ORM_BaseRecord::getTableName('ComEcommerce_Model_Category')));
        }

        return $result;
    }
}
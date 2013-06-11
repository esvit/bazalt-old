<?php

class ComEcommerce_Widget_Products extends CMS_Widget_Component
{
    public function fetch()
    {
        $category = $this->getCategory();
        $collection = ComEcommerce_Model_Product::getCollection($category, true);
        if (!$category) {
            $collection->orderBy('RAND()');
        }

        $this->view->assign('products', $collection->fetchAll());
        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        $this->view->assign('config', $this->options);
        $this->view->assign('category', $this->getCategory());

        $category = ComEcommerce_Model_Category::getSiteRootCategory();
        $this->view->assign('tree', $category);
        return $this->view->fetch('widgets/settings/products');
    }

    public function getCategory()
    {
        if (isset($this->options['category_id'])) {
            $category = ComEcommerce_Model_Category::getByIdAndCompanyId((int)$this->options['category_id']);
            return $category;
        }
        return null;
    }
}
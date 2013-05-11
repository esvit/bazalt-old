<?php

class ComEcommerce_Menu_Category extends CMS_Menu_ComponentItem
{
    public function getItemType()
    {
        return __('Product categories', ComEcommerce::getName());
    }

    public function getItems()
    {
        $config = $this->element->config;
        if ($this->items != null) {
            return $this->items;
        }
        $items = array();
        if (isset($config['subcategories']) && $config['subcategories'] && ($category = $this->getCategory())) {
            $subcategories = $category->PublicElements->get(1);
            foreach ($subcategories as $categoryItem) {
                $menuitem = new ComEcommerce_Menu_Category($this->component(), $categoryItem);
                $items []= $menuitem;
            }
        }
        $this->items = $items;
        return $items;
    }

    public function getSettingsForm()
    {
        if ($this->element) {
            $this->view->assign('menuitem', $this->element);
            $config = $this->element->config;
            $this->view->assign('config', $config);
            $this->view->assign('category', $this->getCategory());
        }
        $root = ComEcommerce_Model_Category::getSiteRootCategory();
        if ($root) {
            $this->view->assign('tree', $root->PublicElements->get(1));
        }
        return $this->view->fetch('menu/category_settings');
    }

    public function getUrl()
    {
        if (!($category = $this->getCategory())) {
            return '#';
        }
        return $category->getUrl();
    }

    public function getCategory()
    {
        if ($this->element instanceof ComEcommerce_Model_Category) {
            return $this->element;
        }
        $config = $this->element->config;

        if (isset($config['category_id'])) {
            $category = ComEcommerce_Model_Category::getByIdAndCompanyId((int)$config['category_id']);
            return $category;
        }
        return null;
    }
}
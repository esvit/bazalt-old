<?php

class ComEcommerce_Menu_Categories extends CMS_Menu_ComponentItem
{
    public function getItemType()
    {
        return __('List of product categories', ComEcommerce::getName());
    }

    public function getItems()
    {
        $config = $this->element->config;
        if ($this->items != null) {
            return $this->items;
        }
        if ($this->element instanceof ComEcommerce_Model_Category) {
            $category = $this->element;
        } else {
            $category = ComEcommerce_Model_Category::getSiteRootCategory();
            $category->Childrens = $category->PublicElements->get();
        }
        $items = array();
        if ($category) {
            foreach ($category->Childrens as $categoryItem) {
                $menuitem = new ComEcommerce_Menu_Categories($this->component(), $categoryItem);
                $items []= $menuitem;
            }
        }
        $this->items = $items;
        return $items;
    }

    public function getSettingsForm()
    {
        return '';
    }

    public function getUrl()
    {
        return CMS_Mapper::urlFor('ComEcommerce.Products');
    }

    public function getCategory()
    {
        return $this->element;
    }
}
<?php

class ComNewsChannel_Menu_NewsCategory extends CMS_Menu_ComponentItem
{
    public function getItemType()
    {
        return __('News category', ComNewsChannel::getName());
    }

    public function getItems()
    {
        $config = $this->element->config;
        if ($this->items != null) {
            return $this->items;
        }
        $items = array();
        if (isset($config['subcategories']) && ($category = $this->getCategory())) {
            $subcategories = $category->Elements->get(1);
            foreach ($subcategories as $galleryItem) {
                $menuitem = new ComNewsChannel_Menu_NewsCategory($this->component(), $galleryItem);
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
        $root = ComNewsChannel_Model_Category::getSiteRootCategory();
        $this->view->assign('tree', $root->Elements->get());
        return $this->view->fetch('menu/newscategory_settings');
    }

    public function getUrl()
    {
        if (!($category = $this->getCategory())) {
            return '#';
        }
        return CMS_Mapper::urlFor('ComNewsChannel.ShowCategory', array('category' => $category->Elements));
    }

    public function getCategory()
    {
        if ($this->element instanceof CMS_Model_Category) {
            return $this->element;
        }
        $config = $this->element->config;

        if (isset($config['category_id'])) {
            $gallery = CMS_Model_Category::getById((int)$config['category_id']);
            return $gallery;
        }
        return null;
    }
}
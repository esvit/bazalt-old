<?php

class ComEcommerce_Widget_Categories extends CMS_Widget_Component
{
    public function fetch()
    {
        $categories = ComEcommerce_Model_ProductTypes::getAll();
        if (!$categories) {
            return parent::fetch();
        }

        $this->view->assign('categories', $categories);
        return parent::fetch();
    }
}
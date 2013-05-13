<?php

class ComNewsChannel_Widget_Categories extends CMS_Widget_Component
{
    public function fetch($vars)
    {
        $root = ComNewsChannel_Model_Category::getSiteRootCategory();
        $this->view->assign('categories', $root->PublicElements->get());

        return parent::fetch();
    }

    public function getConfigPage($config)
    {
        return '';
    }
}
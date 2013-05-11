<?php

class ComEcommerce_Widget_DiscountProducts extends CMS_Widget_Component
{
    public function fetch()
    {
        $collection = ComEcommerce_Model_Product::getDiscountsCollection(true);
        $collection->orderBy('RAND()');

        $this->view->assign('products', $collection->fetchPage());
        return parent::fetch();
    }
}
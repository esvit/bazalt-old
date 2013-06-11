<?php

class ComEcommerce_Widget_Cart extends CMS_Widget_Component
{
    public function fetch()
    {
        $route = CMS_Mapper::getDispatchedRoute();

        if ($route->Rule->Name != 'ComEcommerce.ProductsCategoryFilter' &&
            $route->Rule->Name != 'ComEcommerce.ProductsCategory' &&
            $route->Rule->Name != 'ComEcommerce.Hits' &&
            $route->Rule->Name != 'ComEcommerce.View') {
        //    return;
        }

        $this->component->addWebservice('ComEcommerce_Webservice_Cart');

        $cart = ComEcommerce::getUserCart();
        $this->view->assign('cart', $cart);
        return parent::fetch();
    }
}
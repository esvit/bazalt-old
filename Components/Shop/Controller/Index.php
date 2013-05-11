<?php

namespace Components\Shop\Controller;

use Framework\CMS as CMS,
    Components\Shop\Model as Model,
    Framework\System\Routing\Route;

class Index extends CMS\AbstractController
{
    public function defaultAction()
    {
    }

    public function shopAction($shopId)
    {
        $shop = Model\Shop::getById($shopId);
        \Components\Shop\Component::currentShop($shop);

        $this->view()->display('shop/shop');
    }

    public function categoryAction($shopId, $category)
    {
        $this->view()->assign('category', $category);

        $collection = $category->getProducts(true);
        $this->view()->assign('products', $collection->fetchPage());

        $this->view()->display('shop/category');
    }

    public function productAction($shopId, $product, $productId)
    {
        $product = Model\Product::getById((int)$productId);

        $this->view()->assign('product', $product);

        $this->view()->display('shop/product');
    }
}
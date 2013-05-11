<?php

class ComEcommerce_Controller_Default extends CMS_Component_Controller
{
    public function productViewAction($id, $alias)
    {
        $product = ComEcommerce_Model_Product::getById((int)$id);
        //print_r($product);exit;
        if (!$product) {
            throw new CMS_Exception_PageNotFound();
        }

        $fields = $product->getReadableFields(true);

        Metatags::set('PRODUCT_TITLE', $product->title);

        $categories = $product->PublicCategories->get();

        $this->view->assign('product', $product);
        $this->view->assign('categories', $categories);
        $this->view->assign('fields', $fields);
        if (count($categories) > 0) {
            $this->view->assignGlobal('productCategory', $categories[0]);
            ComEcommerce::productCategory($categories[0]);
        }
        $this->view->display('page.product');
    }

    public function orderAction()
    {
        $cart = ComEcommerce::getUserCart();
        if (!$cart) {
            throw new CMS_Exception_PageNotFound();
        }

        $form = new ComEcommerce_Form_UserOrder();
        if ($form->isPostBack()) {
            $form->value($_POST);
            $order = $cart->saveAsOrder();
            $order->name = $form['name']->value();
            $order->phone = $form['phone']->value();
            $order->address = $form['address']->value();
            $order->comment = $form['body']->value();
            $order->save();

            $user = $cart->User;
            CMS_Bazalt::getComponent('ComEcommerce')->OnOrderProducts(array(
                'sitehost' => CMS_Bazalt::getSiteHost(),
                'user' => $user,
                'usermail' => $user->email,
                'products' => $order->Products->get(),
                'orderName' => $order->name,
                'orderPhone' => $order->phone,
                'orderAddress' => $order->address,
                'orderComment' => $order->comment
            ));

            Url::redirect(CMS_Mapper::urlFor('ComEcommerce.OrderStatus', array('id' => $order->id)));
        }

        $this->view->assign('cart', $cart);
        $this->view->assign('form', $form);
        $this->view->assign('products', $cart->Products->get());
        $this->view->display('page.order');
    }

    public function orderStatusAction($id)
    {
        $cart = ComEcommerce::getUserCart();
        $order = $cart->getOrderById((int)$id);
        if (!$order) {
            throw new CMS_Exception_PageNotFound();
        }

        $this->view->assign('order', $order);
        $this->view->display('page.order_status');
    }

    public function cartAction()
    {
        $cart = ComEcommerce::getUserCart();

        $this->component->addWebservice('ComEcommerce_Webservice_Cart');

        $this->view->assign('cart', $cart);
        if ($cart) {
            $this->view->assign('products', $cart->Products->get());
        }
        $this->view->display('page.cart');
    }

    public function hitsAction()
    {
        $collection = ComEcommerce_Model_Product::getHitsCollection(true);

        Metatags::set('PAGE_TITLE', __('Hits', ComEcommerce::getName()));

        $this->view->assign('products', $collection->getPage());
        $this->view->assign('pager', $collection->getPager('ComEcommerce.Hits'));
        $this->view->display('page.products_hits');
    }

    public function latestAction()
    {
        $collection = ComEcommerce_Model_Product::getLatestCollection(true);

        Metatags::set('PAGE_TITLE', __('Latest', ComEcommerce::getName()));

        $this->view->assign('products', $collection->getPage());
        $this->view->assign('pager', $collection->getPager('ComEcommerce.Latest'));
        $this->view->display('page.latest');
    }

    public function discountsAction()
    {
        $collection = ComEcommerce_Model_Product::getDiscountsCollection(true);

        Metatags::set('PAGE_TITLE', __('Discounts', ComEcommerce::getName()));

        $this->view->assign('products', $collection->getPage());
        $this->view->assign('pager', $collection->getPager('ComEcommerce.Discounts'));
        $this->view->display('shop/page.discounts');
    }

    public function compareAction($id)
    {
        $type = ComEcommerce_Model_ProductTypes::getById($id);
        if (!$type) {
            throw new CMS_Exception_PageNotFound();
        }
        $cart = ComEcommerce::getUserCart();
        $products = ComEcommerce_Model_Compare::getProductsByCartAndTypeId($cart, $product->type_id);

        $this->component->addWebservice('ComEcommerce_Webservice_Cart');

        $productFields = array();
        foreach ($products as $product) {
            $productFields[$product->id] = $product->getReadableFields(true);
        }

        $this->view->assign('cart', $cart);
        $this->view->assign('products', $products);
        $this->view->assign('productFields', $productFields);
        $this->view->assign('fields', $type->getFields(true));
        $this->view->display('page.compare');
    }
}

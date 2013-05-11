<?php

class ComEcommerce_Controller_Admin_Order extends CMS_Component_Controller
{
    public function ordersAction()
    {
        // Зберігає останній візит користувача, щоб показувати нотіфікацію
        CMS_User::getUser()->setting(ComEcommerce::LASTACTIVITY_USER_SETTING . CMS_Bazalt::getSiteId(), time());

        $form = new Html_Form('list');

        $form->addElement(new ComEcommerce_Form_Table_Orders('table'));
        $form['table']->collection(ComEcommerce_Model_Order::getCollection())
                      ->pager('ComEcommerce.Orders');

        $this->view->assign('form', $form);
        $this->view->display('admin/orders');
    }

    public function orderViewAction($id)
    {
        $this->component->OrdersMenu->activate();

        $order = ComEcommerce_Model_Order::getById((int)$id);
        if (!$order) {
            throw new CMS_Exception_PageNotFound();
        }
        $this->view->assign('order', $order);

        $form = new ComEcommerce_Form_OrderStatus();
        $form->dataBind($order);
    
        if ($form->isPostBack() && $form->validate()){
            $form->save();
        }

        $this->view->assign('form', $form);
        $this->view->assign('products', $order->Products->get());
        $this->view->display('admin/order_view');
    }
}

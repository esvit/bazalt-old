<?php

class ComEcommerce_Controller_AdminProductTypes extends CMS_Component_Controller
{
    public function productsTypesAction()
    {
        $this->component->ProductsTypesMenu->activate();
        $this->component->addWebservice('ComEcommerce_Webservice_TreeProductTypes');

        $this->view->assign('root', ComEcommerce_Model_ProductTypes::getRoot());
        $this->view->assign('languages', CMS_Language::getLanguages());
        $this->view->assign('language', CMS_Language::getCurrentLanguage());

        $this->view->assign('fieldTypes', ComEcommerce_Model_Field::getTypes());
        $this->view->display('admin/products-types');
    }
}
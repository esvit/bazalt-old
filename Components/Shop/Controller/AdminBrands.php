<?php

class ComEcommerce_Controller_AdminBrands extends CMS_Component_Controller
{
    public function brandsAction()
    {
        $this->component->BrandsMenu->activate();
        $form = new Html_Form('list');

        $form->addElement(new ComEcommerce_Form_Table_Brands('table'));
        $form['table']->collection(ComEcommerce_Model_Brands::getCollection())
                      ->pager('ComEcommerce.BrandsList');

        $this->view->assign('form', $form->toString());
        $this->view->display('admin/brands');
    }

    public function brandEditAction($id = null)
    {
        $this->component->BrandsMenu->activate();
        if (!empty($id)) {
            $obj = ComEcommerce_Model_Brands::getById((int)$id);
            if (!$obj) {
                throw new CMS_Exception_PageNotFound();
            }
            $this->view->assign('edit_obj', $obj);
        } else {
            $obj = ComEcommerce_Model_Brands::create();
        }

        $form = new ComEcommerce_Form_BrandEdit();
        $form->dataBind($obj);
    
        if ($form->isPostBack() && $form->validate()){
            $form->save();

            Url::redirect($this->component->urlFor('ComEcommerce.BrandEdit', array('id' => $form->DataBindedObject->id)));
        }

        $this->view->assign('form', $form->toString());
        $this->view->display('admin/brand-edit');
    }
}

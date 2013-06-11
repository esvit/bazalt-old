<?php

class ComEcommerce_Controller_Admin extends CMS_Component_Controller
{
    public function settingsAction()
    {
        $form = new ComEcommerce_Form_Settings();

        $this->view->assign('form', $form->toString());
        $this->view->display('admin/settings');
    }
}

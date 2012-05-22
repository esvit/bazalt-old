<?php

class Admin_Controller_Default extends Admin_Controller_Base
{
    public function defaultAction()
    {
        $this->application->addWebservice('Admin_Webservice_Dashboard');

        $this->view->assign('dashboard', Admin_Dashboard::getInstance());

        $this->view->display('index');
    }

    public function settingsAction()
    {
        $form = new Admin_Form_Settings();

        $this->view->assign('form', $form->toString());

        $this->view->display('settings');
    }

    public function mailSettingsAction()
    {
        $this->application->addWebservice('Admin_Webservice_Mail');
        $form = new Admin_Form_Mail();

        $this->view->assign('form', $form->toString());

        $this->view->display('mail_settings');
    }

    public function aboutAction()
    {
        $this->view->display('about');
    }

    public function componentsAction()
    {
        $components = CMS_Model_Component::getActiveComponents();
        $services = CMS_Model_Services::getAll();

        $this->view->assign('components', $components);
        $this->view->assign('services', $services);
        $this->view->display('components');
    }

    public function phpinfoAction()
    {
        ob_start();
        phpinfo();
        $string = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', trim(ob_get_clean()));

        $this->view->assign('phpinfo', $string);
        $this->view->display('phpinfo');
    }

    public function loginAction()
    {
        Assets_JS::addPackage('jQuery');
        $this->application->addScript('login.js');

        define('METATAGS_NO_TITLE', true);

        $user = CMS_User::getUser();
        if (CMS_User::isLogined() && $user->hasRight(null, CMS_Bazalt::ACL_CAN_LOGIN)) {
            Url::redirect('/admin/');
        }

        $form = new Admin_Form_Login();
        if ($form->isPostBack()) {
            $form->value($_POST);
            if ($form->validate()) {
                DataType_Url::redirect($form->backUrl());
            }
        } else {
            $form->backUrl(Session::Singleton()->backUrl);
        }

        CMS_View::setLayout('login.layout');
        $this->view->assign('form', $form);

        $this->view->showPage('login.layout');
        exit;
    }
}

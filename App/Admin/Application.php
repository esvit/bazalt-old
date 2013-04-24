<?php

namespace App\Admin;

use Framework\CMS as CMS;

class Application extends CMS\Application
{
    public function init()
    {
        parent::init();

        $folders = $this->view->folders();
        $folders []= __DIR__ . PATH_SEP . 'views';
        $this->view->folders($folders);

        $this->view->assign('baseUrl', $_SERVER['SCRIPT_NAME']);

        $user = CMS\User::get();
        if (!$user->hasRight(null, CMS\Bazalt::ACL_HAS_ADMIN_PANEL_ACCESS)) {
            $this->view->display('login');
            exit;
        }
        $content = $this->view->fetch('index');
        $this->showPage($content);
    }
}
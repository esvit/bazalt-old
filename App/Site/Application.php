<?php

namespace App\Site;

use Framework\CMS as CMS;
use Framework\System\Routing\Route;
use Framework\System\Session\Session;

define('THEMES_DIR', SITE_DIR . '/themes');

class Application extends CMS\Application
{
    public function init()
    {
        parent::init();

        $folders = $this->view->folders();

        $folders []= __DIR__ . PATH_SEP . 'views';
        $folders []= THEMES_DIR . PATH_SEP . 'default/views';

        $this->view->folders($folders);

        Route::root()->param('controller', 'App\Site\Controller\Index');

        if (!CLI_MODE) {
            $content = $this->dispatch($this->url);

            $this->showPage($content);
        }
    }

    public function showPage(&$content = null)
    {
        parent::showPage($content);

        $user = CMS\User::get();
        $hasWidgetsRights = $user->hasRight(null, CMS\Bazalt::ACL_CAN_ADMIN_WIDGETS);
        $this->view->assign('canManageWidgets', $hasWidgetsRights);

        if ($hasWidgetsRights) {
            $widgets = CMS\Model\Widget::getActiveWidgets();
            $this->view->assign('widgets', $widgets);

            $this->view->assign('widgetsOn', ($_COOKIE['cms-show-manage-widgets'] == 'true'));
           //$this->view->display('admin/panel');
        }
    }
}